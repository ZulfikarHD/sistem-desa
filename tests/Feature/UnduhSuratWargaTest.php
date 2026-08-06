<?php

use App\Livewire\Pengajuan\DetailPengajuanWarga;
use App\Livewire\Pengajuan\RiwayatPengajuan;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * Buat pengajuan + surat_terbit PDF untuk skenario unduh warga (US-7.6).
 *
 * @return array{0: User, 1: PengajuanSurat, 2: SuratTerbit}
 */
function buatPengajuanDenganSurat(
    string $status = PengajuanSurat::STATUS_DIPROSES,
    ?string $tanggalPengambilan = null,
    ?string $jamKerjaLabel = null,
): array {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => $status,
        'diverifikasi_oleh' => $admin->id,
    ]);

    $path = 'surat-terbit/'.$pengajuan->id.'/surat.pdf';
    Storage::disk('local')->put($path, '%PDF-1.4 unduh surat test');

    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'file_path' => $path,
        'nomor_surat' => '470/'.$pengajuan->id.'/DS-WDN/VIII/2026',
        'tanggal_pengambilan' => $tanggalPengambilan,
        'jam_kerja_label' => $jamKerjaLabel,
        'diterbitkan_oleh' => $admin->id,
        'qr_token' => str_repeat('a', 64),
        'qr_status' => SuratTerbit::QR_STATUS_VALID,
    ]);

    return [$warga, $pengajuan->fresh(), $surat->fresh()];
}

test('warga can download surat pdf for diproses siap_diambil and selesai', function (string $status) {
    [$warga, $pengajuan, $surat] = buatPengajuanDenganSurat($status);
    $tokenSebelum = $surat->qr_token;

    $response = $this->actingAs($warga)
        ->get(route('pengajuan-surat.unduh-surat', $pengajuan));

    $response->assertSuccessful();
    $response->assertHeader('content-disposition');
    expect($response->headers->get('content-disposition'))->toContain('attachment');

    $surat->refresh();
    expect($surat->qr_token)->toBe($tokenSebelum)
        ->and($surat->qr_status)->toBe(SuratTerbit::QR_STATUS_VALID);
})->with([
    PengajuanSurat::STATUS_DIPROSES,
    PengajuanSurat::STATUS_SIAP_DIAMBIL,
    PengajuanSurat::STATUS_SELESAI,
]);

test('warga can open cetak surat pdf inline without regenerating qr', function () {
    [$warga, $pengajuan, $surat] = buatPengajuanDenganSurat();
    $tokenSebelum = $surat->qr_token;

    $response = $this->actingAs($warga)
        ->get(route('pengajuan-surat.cetak-surat', $pengajuan));

    $response->assertSuccessful();
    $response->assertHeader('content-type', 'application/pdf');

    $surat->refresh();
    expect($surat->qr_token)->toBe($tokenSebelum)
        ->and($surat->qr_status)->toBe(SuratTerbit::QR_STATUS_VALID);
});

test('unduh ulang keeps same file and qr token', function () {
    [$warga, $pengajuan, $surat] = buatPengajuanDenganSurat();
    $tokenSebelum = $surat->qr_token;
    $pathSebelum = $surat->file_path;

    $this->actingAs($warga)->get(route('pengajuan-surat.unduh-surat', $pengajuan))->assertSuccessful();
    $this->actingAs($warga)->get(route('pengajuan-surat.unduh-surat', $pengajuan))->assertSuccessful();

    $surat->refresh();
    expect($surat->qr_token)->toBe($tokenSebelum)
        ->and($surat->file_path)->toBe($pathSebelum);
    Storage::disk('local')->assertExists($pathSebelum);
});

test('guest cannot unduh surat', function () {
    [, $pengajuan] = buatPengajuanDenganSurat();

    $this->get(route('pengajuan-surat.unduh-surat', $pengajuan))
        ->assertRedirect(route('login'));
});

test('other warga cannot unduh surat owned by another', function () {
    [, $pengajuan] = buatPengajuanDenganSurat();
    $other = User::factory()->create(['role' => 'warga']);

    $this->actingAs($other)
        ->get(route('pengajuan-surat.unduh-surat', $pengajuan))
        ->assertForbidden();
});

test('admin cannot unduh via warga route', function () {
    [, $pengajuan] = buatPengajuanDenganSurat();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('pengajuan-surat.unduh-surat', $pengajuan))
        ->assertForbidden();
});

test('unduh forbidden when status not allowed', function (string $status) {
    [$warga, $pengajuan] = buatPengajuanDenganSurat($status);

    $this->actingAs($warga)
        ->get(route('pengajuan-surat.unduh-surat', $pengajuan))
        ->assertForbidden();
})->with([
    PengajuanSurat::STATUS_DIAJUKAN,
    PengajuanSurat::STATUS_DISETUJUI,
    PengajuanSurat::STATUS_DITOLAK,
]);

test('unduh returns 404 when pdf file missing', function () {
    [$warga, $pengajuan, $surat] = buatPengajuanDenganSurat();
    Storage::disk('local')->delete($surat->file_path);

    $this->actingAs($warga)
        ->get(route('pengajuan-surat.unduh-surat', $pengajuan))
        ->assertNotFound();
});

test('riwayat shows unduh button only for allowed statuses', function () {
    [$warga, $pengajuan] = buatPengajuanDenganSurat(PengajuanSurat::STATUS_DIPROSES);

    Livewire::actingAs($warga)
        ->test(RiwayatPengajuan::class)
        ->assertSeeHtml('riwayat-pengajuan-unduh-surat-'.$pengajuan->id);

    $pengajuan->update(['status' => PengajuanSurat::STATUS_DIAJUKAN]);

    Livewire::actingAs($warga)
        ->test(RiwayatPengajuan::class)
        ->assertDontSeeHtml('riwayat-pengajuan-unduh-surat-'.$pengajuan->id);
});

test('detail shows pickup date hours and unduh cetak when siap diambil', function () {
    [$warga, $pengajuan] = buatPengajuanDenganSurat(
        PengajuanSurat::STATUS_SIAP_DIAMBIL,
        '2100-08-11',
        'Senin–Kamis 08.00–16.00 WIB',
    );

    Livewire::actingAs($warga)
        ->test(DetailPengajuanWarga::class, ['pengajuan' => $pengajuan])
        ->assertSeeHtml('detail-pengajuan-warga-tanggal-pengambilan')
        ->assertSeeHtml('detail-pengajuan-warga-jam-kerja')
        ->assertSee('Senin–Kamis 08.00–16.00 WIB')
        ->assertSeeHtml('detail-pengajuan-warga-unduh-surat')
        ->assertSeeHtml('detail-pengajuan-warga-cetak-surat');
});

test('detail does not show unduh when status diajukan', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($warga)
        ->test(DetailPengajuanWarga::class, ['pengajuan' => $pengajuan])
        ->assertDontSeeHtml('detail-pengajuan-warga-unduh-surat')
        ->assertDontSeeHtml('detail-pengajuan-warga-tanggal-pengambilan');
});
