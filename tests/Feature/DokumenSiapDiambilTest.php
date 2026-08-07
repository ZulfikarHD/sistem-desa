<?php

use App\Livewire\Pengajuan\RiwayatPengajuan;
use App\Livewire\SuratDiproses\DetailSuratDiproses;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * Buat pengajuan diproses + surat_terbit (prasyarat UI US-7.5 / US-8.6).
 *
 * @return array{pengajuan: PengajuanSurat, surat: SuratTerbit, warga: User, admin: User}
 */
function buatPengajuanDiprosesDenganSurat(): array
{
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'tanggal_pengambilan' => null,
        'jam_kerja_label' => null,
        'siap_diambil_at' => null,
        'qr_token' => Str::random(64),
        'qr_status' => SuratTerbit::QR_STATUS_VALID,
    ]);

    Storage::disk('local')->put($surat->file_path, '%PDF-1.4 test');

    return compact('pengajuan', 'surat', 'warga', 'admin');
}

/**
 * Hari kerja berikutnya (Senin–Jumat, bukan libur nasional) dari tanggal acuan WIB.
 */
function nextHariKerja(?Carbon $from = null): Carbon
{
    $tanggal = Carbon::parse(($from ?? now('Asia/Jakarta'))->toDateString(), 'Asia/Jakarta')->startOfDay();

    for ($i = 0; $i < 60; $i++) {
        $candidate = $tanggal->copy()->addDays($i);
        $candidate = Carbon::parse($candidate->toDateString(), 'Asia/Jakarta');

        if (SuratTerbit::validasiTanggalPengambilan($candidate)['ok']) {
            return $candidate;
        }
    }

    throw new RuntimeException('Tidak menemukan hari kerja dalam 60 hari.');
}

test('admin melihat panel siap diambil pada detail surat diproses dengan PDF', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();

    $component = Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->assertSee('Siap Diambil')
        ->assertSeeHtml('data-test="surat-diproses-detail-siap-diambil-button"');

    expect($component->instance()->canMarkSiapDiambil())->toBeTrue();
});

test('tombol siap diambil disabled tanpa tanggal lalu aktif setelah tanggal valid', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();
    $hariKerja = nextHariKerja();

    $component = Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan]);

    expect($component->instance()->isTanggalPengambilanSiap())->toBeFalse();

    $component->set('tanggalPengambilan', $hariKerja->toDateString());

    expect($component->instance()->isTanggalPengambilanSiap())->toBeTrue()
        ->and($component->instance()->jamKerjaPreview())->not->toBeNull();
});

test('tandai siap diambil mengubah status menyimpan tanggal jam kerja dan notifikasi warga', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'warga' => $warga, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();
    $hariKerja = nextHariKerja();

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $hariKerja->toDateString())
        ->call('tandaiSiapDiambil')
        ->assertHasNoErrors()
        ->assertRedirect(route('surat-diproses.index'));

    $pengajuan->refresh();
    $surat->refresh();

    expect($pengajuan->status)->toBe(PengajuanSurat::STATUS_SIAP_DIAMBIL)
        ->and($surat->tanggal_pengambilan?->toDateString())->toBe($hariKerja->toDateString())
        ->and($surat->jam_kerja_label)->not->toBeNull()
        ->and($surat->siap_diambil_at)->not->toBeNull();

    Storage::disk('local')->assertExists($surat->file_path);
    expect(Storage::disk('local')->get($surat->file_path))->toStartWith('%PDF');

    $notif = Notifikasi::query()
        ->where('user_id', $warga->id)
        ->where('pengajuan_id', $pengajuan->id)
        ->latest('id')
        ->first();

    expect($notif)->not->toBeNull()
        ->and($notif->pesan)->toContain('sudah siap diambil pada')
        ->and($notif->pesan)->toContain('#'.$pengajuan->nomor_pengajuan);
});

test('sabtu minggu ditolak sebagai tanggal pengambilan', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();

    $sabtu = now('Asia/Jakarta')->next(Carbon::SATURDAY)->startOfDay();

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $sabtu->toDateString())
        ->call('tandaiSiapDiambil')
        ->assertHasErrors('tanggalPengambilan');

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIPROSES);
});

test('libur nasional ditolak sebagai tanggal pengambilan', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();

    $libur = Carbon::parse('2026-08-17', 'Asia/Jakarta');

    expect(SuratTerbit::isLiburNasional($libur))->toBeTrue();

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $libur->toDateString())
        ->call('tandaiSiapDiambil')
        ->assertHasErrors('tanggalPengambilan');

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIPROSES);
});

test('tanggal masa lalu ditolak', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();

    $kemarin = now('Asia/Jakarta')->subDay()->startOfDay();

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $kemarin->toDateString())
        ->call('tandaiSiapDiambil')
        ->assertHasErrors('tanggalPengambilan');
});

test('tidak bisa tandai siap diambil jika status bukan diproses', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'diverifikasi_oleh' => null,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', nextHariKerja()->toDateString())
        ->call('tandaiSiapDiambil')
        ->assertNoRedirect();

    expect($component->instance()->canMarkSiapDiambil())->toBeFalse()
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIAJUKAN);
});

test('tidak bisa tandai siap diambil jika PDF surat belum ada', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'diverifikasi_oleh' => $admin->id,
    ]);

    expect($pengajuan->suratTerbit)->toBeNull();

    $component = Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan]);

    expect($component->instance()->canMarkSiapDiambil())->toBeFalse();
});

test('jam kerja jumat berbeda dari senin kamis', function () {
    $jumat = Carbon::parse('2026-08-14', 'Asia/Jakarta');
    $senin = Carbon::parse('2026-08-10', 'Asia/Jakarta');

    expect(SuratTerbit::isLiburNasional($jumat))->toBeFalse()
        ->and(SuratTerbit::isLiburNasional($senin))->toBeFalse();

    $labelJumat = SuratTerbit::jamKerjaLabelUntuk($jumat);
    $labelSenin = SuratTerbit::jamKerjaLabelUntuk($senin);

    expect($labelJumat)->toContain('16.30')
        ->and($labelSenin)->toContain('16.00')
        ->and($labelJumat)->not->toBe($labelSenin);
});

test('riwayat warga menampilkan tanggal pengambilan dan jam kerja', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'warga' => $warga] = buatPengajuanDiprosesDenganSurat();
    $hariKerja = nextHariKerja();

    $pengajuan->update(['status' => PengajuanSurat::STATUS_SIAP_DIAMBIL]);
    $surat->update([
        'tanggal_pengambilan' => $hariKerja->toDateString(),
        'jam_kerja_label' => SuratTerbit::jamKerjaLabelUntuk($hariKerja),
        'siap_diambil_at' => now(),
    ]);

    Livewire::actingAs($warga)
        ->test(RiwayatPengajuan::class)
        ->assertSee($pengajuan->nomor_pengajuan)
        ->assertSee('Siap Diambil')
        ->assertSeeHtml('data-test="riwayat-pengajuan-tanggal-ambil-'.$pengajuan->id.'"')
        ->assertSeeHtml('data-test="riwayat-pengajuan-jam-kerja-'.$pengajuan->id.'"');
});

test('scan qr setelah siap diambil menandai selesai (integrasi US-7.4)', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin] = buatPengajuanDiprosesDenganSurat();
    $hariKerja = nextHariKerja();

    $hasil = SuratTerbit::tandaiSiapDiambil($pengajuan, $hariKerja);
    expect($hasil['ok'])->toBeTrue();

    $scan = SuratTerbit::scanUntukPengambilan($surat->qr_token, $admin->id);

    expect($scan['ok'])->toBeTrue()
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_SELESAI)
        ->and($surat->fresh()->qr_status)->toBe(SuratTerbit::QR_STATUS_INVALID);
});
