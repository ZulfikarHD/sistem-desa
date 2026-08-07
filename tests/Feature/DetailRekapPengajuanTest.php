<?php

use App\Livewire\Rekap\DetailRekapPengajuan;
use App\Livewire\Rekap\RekapPengajuan;
use App\Models\LogVerifikasi;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * @return array{pengajuan: PengajuanSurat, surat: SuratTerbit, warga: User, admin: User}
 */
function buatPengajuanDiprosesDenganLog(): array
{
    $admin = User::factory()->admin()->create(['name' => 'Admin Timeline']);
    $warga = User::factory()->create([
        'role' => 'warga',
        'name' => 'Warga Timeline',
        'nik' => '3201010101010001',
    ]);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);

    LogVerifikasi::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'admin_id' => $admin->id,
        'aksi' => LogVerifikasi::AKSI_SETUJUI,
        'keterangan' => null,
        'created_at' => now()->subDays(2),
    ]);

    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'nomor_surat' => '470/99/DS-WDN/VIII/2026',
        'tanggal_pengambilan' => null,
        'jam_kerja_label' => null,
        'siap_diambil_at' => null,
        'qr_token' => Str::random(64),
        'qr_status' => SuratTerbit::QR_STATUS_VALID,
    ]);

    Storage::disk('local')->put($surat->file_path, '%PDF-1.4 timeline test');

    return compact('pengajuan', 'surat', 'warga', 'admin');
}

test('guests are redirected to login from rekap detail', function () {
    $pengajuan = PengajuanSurat::factory()->create();

    $this->get(route('rekap-pengajuan.show', $pengajuan))
        ->assertRedirect(route('login'));
});

test('warga cannot visit rekap detail', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create(['user_id' => $warga->id]);

    $this->actingAs($warga)
        ->get(route('rekap-pengajuan.show', $pengajuan))
        ->assertForbidden();
});

test('admin dapat membuka detail rekap dari daftar dengan ringkasan dan timeline', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin, 'warga' => $warga] = buatPengajuanDiprosesDenganLog();

    $this->actingAs($admin)
        ->get(route('rekap-pengajuan.index'))
        ->assertOk()
        ->assertSeeHtml('data-test="rekap-pengajuan-detail-'.$pengajuan->id.'"');

    Livewire::actingAs($admin)
        ->test(DetailRekapPengajuan::class, ['pengajuan' => $pengajuan])
        ->assertSee('Detail Rekap Pengajuan')
        ->assertSeeHtml('data-test="rekap-detail-ringkasan"')
        ->assertSeeHtml('data-test="rekap-detail-timeline"')
        ->assertSee($warga->name)
        ->assertSee($warga->nik)
        ->assertSee($pengajuan->nomor_pengajuan)
        ->assertSee('470/99/DS-WDN/VIII/2026')
        ->assertSeeHtml('data-test="rekap-timeline-item-dibuat"')
        ->assertSeeHtml('data-test="rekap-timeline-item-disetujui_diproses"')
        ->assertDontSeeHtml('data-test="rekap-timeline-item-siap_diambil"')
        ->assertDontSeeHtml('data-test="rekap-timeline-item-selesai"')
        ->assertSee('Pengajuan diterima oleh sistem')
        ->assertSee('Disetujui oleh Admin Timeline')
        ->assertSee('surat #470/99/DS-WDN/VIII/2026 digenerate')
        ->assertSee('Unduh PDF Surat')
        ->assertSee('Kembali ke Rekap');
});

test('timeline ditolak berhenti di poin tolak tanpa siap diambil atau selesai', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Tolak']);
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->ditolak('Dokumen KTP buram')->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);

    LogVerifikasi::factory()->tolak('Dokumen KTP buram')->create([
        'pengajuan_id' => $pengajuan->id,
        'admin_id' => $admin->id,
        'created_at' => now()->subDay(),
    ]);

    Livewire::actingAs($admin)
        ->test(DetailRekapPengajuan::class, ['pengajuan' => $pengajuan])
        ->assertSeeHtml('data-test="rekap-timeline-item-dibuat"')
        ->assertSeeHtml('data-test="rekap-timeline-item-ditolak"')
        ->assertSee('Ditolak oleh Admin Tolak — Alasan: Dokumen KTP buram')
        ->assertDontSeeHtml('data-test="rekap-timeline-item-disetujui_diproses"')
        ->assertDontSeeHtml('data-test="rekap-timeline-item-siap_diambil"')
        ->assertDontSeeHtml('data-test="rekap-timeline-item-selesai"')
        ->assertDontSee('Unduh PDF Surat');
});

test('timeline selesai menampilkan semua poin termasuk siap diambil dan qr scan', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin] = buatPengajuanDiprosesDenganLog();

    $adminScan = User::factory()->admin()->create(['name' => 'Admin Scan QR']);

    $pengajuan->update(['status' => PengajuanSurat::STATUS_SELESAI]);
    $surat->update([
        'tanggal_pengambilan' => now('Asia/Jakarta')->toDateString(),
        'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',
        'siap_diambil_at' => now()->subDay(),
        'qr_status' => SuratTerbit::QR_STATUS_INVALID,
        'qr_digunakan_at' => now()->subHour(),
        'qr_digunakan_oleh' => $adminScan->id,
    ]);

    $pengajuan->refresh();
    $pengajuan->load([
        'logVerifikasi.admin',
        'suratTerbit.diterbitkanOleh',
        'suratTerbit.qrDigunakanOleh',
        'diverifikasiOleh',
    ]);

    $component = Livewire::actingAs($admin)
        ->test(DetailRekapPengajuan::class, ['pengajuan' => $pengajuan]);

    $component
        ->assertSeeHtml('data-test="rekap-timeline-item-dibuat"')
        ->assertSeeHtml('data-test="rekap-timeline-item-disetujui_diproses"')
        ->assertSeeHtml('data-test="rekap-timeline-item-siap_diambil"')
        ->assertSeeHtml('data-test="rekap-timeline-item-selesai"')
        ->assertSee('Dokumen siap diambil oleh Admin Timeline')
        ->assertSee('Dokumen telah diambil — QR dipindai, dicatat oleh Admin Scan QR')
        ->assertSee('WIB');

    $items = $component->instance()->timelineItems();
    expect($items)->toHaveCount(4)
        ->and(collect($items)->pluck('key')->all())->toBe([
            'dibuat',
            'disetujui_diproses',
            'siap_diambil',
            'selesai',
        ]);
});

test('timeline memakai fallback updated_at jika siap_diambil_at null pada status siap_diambil', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin] = buatPengajuanDiprosesDenganLog();

    $pengajuan->update(['status' => PengajuanSurat::STATUS_SIAP_DIAMBIL]);
    $surat->update([
        'tanggal_pengambilan' => now('Asia/Jakarta')->toDateString(),
        'jam_kerja_label' => 'Jumat 08.00–16.30 WIB',
        'siap_diambil_at' => null,
    ]);

    $pengajuan->refresh()->load([
        'logVerifikasi.admin',
        'suratTerbit.diterbitkanOleh',
        'diverifikasiOleh',
    ]);

    $component = Livewire::actingAs($admin)
        ->test(DetailRekapPengajuan::class, ['pengajuan' => $pengajuan]);

    $component
        ->assertSeeHtml('data-test="rekap-timeline-item-siap_diambil"')
        ->assertSee('waktu estimasi');

    $siap = collect($component->instance()->timelineItems())->firstWhere('key', 'siap_diambil');
    expect($siap)->not->toBeNull()
        ->and($siap['estimasi'])->toBeTrue();
});

test('tombol unduh pdf tetap tampil dan regenerate jika file hilang', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin] = buatPengajuanDiprosesDenganLog();
    $tokenSebelum = $surat->qr_token;

    Storage::disk('local')->delete($surat->file_path);

    Livewire::actingAs($admin)
        ->test(DetailRekapPengajuan::class, ['pengajuan' => $pengajuan])
        ->assertSee('Unduh PDF Surat');

    $surat->refresh();
    expect($surat->qr_token)->toBe($tokenSebelum);
    Storage::disk('local')->assertExists($surat->file_path);
});

test('tombol unduh pdf tidak tampil jika surat_terbit belum ada', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailRekapPengajuan::class, ['pengajuan' => $pengajuan])
        ->assertDontSee('Unduh PDF Surat');
});

test('rekap list menampilkan tombol lihat detail per baris', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->create();

    Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->assertSeeHtml('data-test="rekap-pengajuan-detail-'.$pengajuan->id.'"')
        ->assertSee('Lihat Detail');
});
