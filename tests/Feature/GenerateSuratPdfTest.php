<?php

use App\Livewire\Verifikasi\DetailPengajuanVerifikasi;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('approving pengajuan generates surat pdf nomor and qr token', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create([
        'role' => 'warga',
        'nik' => '3201010101010001',
        'alamat' => 'Jl. Melati No. 10',
    ]);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Keterangan Domisili',
    ]);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'keperluan' => 'Melamar pekerjaan',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->call('setujui')
        ->assertRedirect(route('verifikasi.index'));

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe(PengajuanSurat::STATUS_DIPROSES);

    $surat = SuratTerbit::query()->where('pengajuan_id', $pengajuan->id)->first();

    expect($surat)->not->toBeNull()
        ->and($surat->nomor_surat)->toMatch('/^470\/\d+\/DS-WDN\/[IVX]+\/'.now()->format('Y').'$/')
        ->and($surat->qr_status)->toBe(SuratTerbit::QR_STATUS_VALID)
        ->and(strlen($surat->qr_token))->toBe(64)
        ->and($surat->qr_token)->not->toContain($warga->nik)
        ->and($surat->diterbitkan_oleh)->toBe($admin->id)
        ->and($surat->file_path)->toBe('surat-terbit/'.$pengajuan->id.'/surat.pdf');

    Storage::disk('local')->assertExists($surat->file_path);

    $pdf = Storage::disk('local')->get($surat->file_path);
    expect($pdf)->toStartWith('%PDF');
});

test('rejecting pengajuan does not generate surat pdf or qr', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->set('catatanAdmin', 'Dokumen tidak lengkap dan tidak jelas')
        ->call('tolak')
        ->assertRedirect(route('verifikasi.index'));

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DITOLAK);
    expect(SuratTerbit::query()->where('pengajuan_id', $pengajuan->id)->exists())->toBeFalse();
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

test('template resolver picks jenis-specific blade view', function () {
    expect(SuratTerbit::resolveTemplateView('Surat Keterangan Domisili'))
        ->toBe('pdf.surat.keterangan-domisili')
        ->and(SuratTerbit::resolveTemplateView('Surat Keterangan Tidak Mampu'))
        ->toBe('pdf.surat.keterangan-tidak-mampu')
        ->and(SuratTerbit::resolveTemplateView('Surat Keterangan Usaha'))
        ->toBe('pdf.surat.keterangan-usaha')
        ->and(SuratTerbit::resolveTemplateView('Surat Keterangan Kelahiran'))
        ->toBe('pdf.surat.keterangan-kelahiran')
        ->and(SuratTerbit::resolveTemplateView('Surat Keterangan Kematian'))
        ->toBe('pdf.surat.keterangan-kematian')
        ->and(SuratTerbit::resolveTemplateView('Surat Lainnya'))
        ->toBe('pdf.surat.default');
});

test('nomor surat increments sequentially within the same year', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Keterangan Usaha']);

    $first = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-9001',
    ]);
    $second = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-9002',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $first])
        ->call('setujui');

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $second])
        ->call('setujui');

    $nomor1 = SuratTerbit::query()->where('pengajuan_id', $first->id)->value('nomor_surat');
    $nomor2 = SuratTerbit::query()->where('pengajuan_id', $second->id)->value('nomor_surat');

    expect($nomor1)->not->toBe($nomor2);

    preg_match('/^470\/(\d+)\//', (string) $nomor1, $m1);
    preg_match('/^470\/(\d+)\//', (string) $nomor2, $m2);

    expect((int) $m2[1])->toBe(((int) $m1[1]) + 1);
});

test('terbitkanUntuk is idempotent for the same pengajuan', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Keterangan Domisili']);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'diverifikasi_oleh' => $admin->id,
    ]);

    $first = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);
    $second = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);

    expect($second->id)->toBe($first->id);
    expect(SuratTerbit::query()->where('pengajuan_id', $pengajuan->id)->count())->toBe(1);
});
