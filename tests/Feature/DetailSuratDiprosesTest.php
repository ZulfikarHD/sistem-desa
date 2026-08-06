<?php

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
 * @return array{pengajuan: PengajuanSurat, surat: SuratTerbit, warga: User, admin: User}
 */
function buatDetailDiprosesDenganSurat(): array
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
function nextHariKerjaDetail(?Carbon $from = null): Carbon
{
    $tanggal = Carbon::parse(($from ?? now('Asia/Jakarta'))->toDateString(), 'Asia/Jakarta')->startOfDay();

    for ($i = 0; $i < 60; $i++) {
        $candidate = Carbon::parse($tanggal->copy()->addDays($i)->toDateString(), 'Asia/Jakarta');

        if (SuratTerbit::validasiTanggalPengambilan($candidate)['ok']) {
            return $candidate;
        }
    }

    throw new RuntimeException('Tidak menemukan hari kerja dalam 60 hari.');
}

test('admin melihat form tanggal dan tombol siap diambil pada detail diproses', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatDetailDiprosesDenganSurat();

    $component = Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->assertSee('Siap Diambil')
        ->assertSeeHtml('data-test="surat-diproses-detail-siap-diambil-button"')
        ->assertSeeHtml('min="'.now('Asia/Jakarta')->toDateString().'"');

    expect($component->instance()->canMarkSiapDiambil())->toBeTrue()
        ->and($component->instance()->isTanggalPengambilanSiap())->toBeFalse();
});

test('tombol siap diambil aktif setelah tanggal valid diisi', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatDetailDiprosesDenganSurat();
    $hariKerja = nextHariKerjaDetail();

    $component = Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $hariKerja->toDateString());

    expect($component->instance()->isTanggalPengambilanSiap())->toBeTrue()
        ->and($component->instance()->jamKerjaPreview())->not->toBeNull();
});

test('klik siap diambil mengubah status menyimpan tanggal siap_diambil_at dan notifikasi', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'warga' => $warga, 'admin' => $admin] = buatDetailDiprosesDenganSurat();
    $hariKerja = nextHariKerjaDetail();

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

    $notif = Notifikasi::query()
        ->where('user_id', $warga->id)
        ->where('pengajuan_id', $pengajuan->id)
        ->latest('id')
        ->first();

    expect($notif)->not->toBeNull()
        ->and($notif->pesan)->toContain('sudah siap diambil pada')
        ->and($notif->pesan)->toContain('#'.$pengajuan->nomor_pengajuan);
});

test('tanggal masa lalu ditolak oleh validasi after_or_equal', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatDetailDiprosesDenganSurat();

    $kemarin = now('Asia/Jakarta')->subDay()->toDateString();

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $kemarin)
        ->call('tandaiSiapDiambil')
        ->assertHasErrors('tanggalPengambilan');

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIPROSES);
});

test('sabtu ditolak sebagai tanggal pengambilan', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatDetailDiprosesDenganSurat();

    $sabtu = now('Asia/Jakarta')->next(Carbon::SATURDAY)->toDateString();

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan])
        ->set('tanggalPengambilan', $sabtu)
        ->call('tandaiSiapDiambil')
        ->assertHasErrors('tanggalPengambilan');

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIPROSES);
});

test('form siap diambil tidak tampil jika status siap_diambil', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin] = buatDetailDiprosesDenganSurat();
    $hariKerja = nextHariKerjaDetail();

    $pengajuan->update(['status' => PengajuanSurat::STATUS_SIAP_DIAMBIL]);
    $surat->update([
        'tanggal_pengambilan' => $hariKerja->toDateString(),
        'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',
        'siap_diambil_at' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(DetailSuratDiproses::class, ['pengajuan' => $pengajuan->fresh(['suratTerbit', 'user', 'jenisSurat'])])
        ->assertDontSeeHtml('data-test="surat-diproses-detail-siap-diambil-panel"')
        ->assertSeeHtml('data-test="surat-diproses-detail-status-info"')
        ->assertSee('Informasi Status Terkini');
});

test('admin dapat mengunduh pdf surat dari detail', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatDetailDiprosesDenganSurat();

    $this->actingAs($admin)
        ->get(route('surat-diproses.pdf.download', $pengajuan))
        ->assertSuccessful()
        ->assertHeader('content-disposition');
});

test('warga tidak dapat mengakses detail surat diproses', function () {
    ['pengajuan' => $pengajuan, 'warga' => $warga] = buatDetailDiprosesDenganSurat();

    $this->actingAs($warga)
        ->get(route('surat-diproses.show', $pengajuan))
        ->assertForbidden();
});

test('verifikasi detail tidak lagi menampilkan panel siap diambil (relokasi US-8.6)', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatDetailDiprosesDenganSurat();

    $this->actingAs($admin)
        ->get(route('verifikasi.show', $pengajuan))
        ->assertSuccessful()
        ->assertDontSeeHtml('data-test="verifikasi-detail-siap-diambil-panel"');
});
