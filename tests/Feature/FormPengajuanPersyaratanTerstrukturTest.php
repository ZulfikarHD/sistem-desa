<?php

use App\Livewire\Pengajuan\FormPengajuanSurat;
use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('form shows badges matching admin preview for mixed persyaratan', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => 'placeholder',
    ]);
    $jenisSurat->syncPersyaratan([
        [
            'nama' => 'Fotokopi KTP',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
            'urutan' => 0,
        ],
        [
            'nama' => 'Slip gaji (jika ada)',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => false,
            'urutan' => 1,
        ],
        [
            'nama' => 'Surat pengantar RT/RW',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
            'is_wajib' => true,
            'urutan' => 2,
        ],
        [
            'nama' => 'Datang pada jam kerja',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_INFO,
            'is_wajib' => true,
            'urutan' => 3,
        ],
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->assertSee('Wajib diunggah')
        ->assertSee('Boleh dikosongkan')
        ->assertSee('Bawa ke kantor')
        ->assertSee('Informasi')
        ->assertSee(JenisSuratPersyaratan::bantuanBawaKantor())
        ->assertSee('Catatan informasi — tidak perlu diunggah maupun dibawa.');
});

test('upload slots appear only for cara unggah', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'x']);
    $jenisSurat->syncPersyaratan([
        [
            'nama' => 'Fotokopi KTP',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
            'urutan' => 0,
        ],
        [
            'nama' => 'Surat pengantar RT/RW',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
            'is_wajib' => true,
            'urutan' => 1,
        ],
    ]);

    $unggah = $jenisSurat->persyaratan()->where('cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)->firstOrFail();
    $bawa = $jenisSurat->persyaratan()->where('cara_pemenuhan', JenisSuratPersyaratan::CARA_BAWA_KANTOR)->firstOrFail();

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->assertSeeHtml('data-test="pengajuan-surat-dokumen-input-'.$unggah->id.'"')
        ->assertDontSeeHtml('data-test="pengajuan-surat-dokumen-input-'.$bawa->id.'"');
});

test('optional unggah does not block submit', function () {
    Storage::fake();

    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'x']);
    $jenisSurat->syncPersyaratan([
        [
            'nama' => 'Fotokopi KTP',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
            'urutan' => 0,
        ],
        [
            'nama' => 'NPWP (jika ada)',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => false,
            'urutan' => 1,
        ],
        [
            'nama' => 'Pengantar RT/RW',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
            'is_wajib' => true,
            'urutan' => 2,
        ],
    ]);

    $ktp = $jenisSurat->persyaratan()->where('nama', 'Fotokopi KTP')->firstOrFail();
    $ktpFile = UploadedFile::fake()->image('ktp.jpg');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Tanpa NPWP opsional')
        ->set('dokumenFiles.'.$ktp->id, $ktpFile)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedNomor', fn (?string $nomor) => $nomor !== null);

    $pengajuan = PengajuanSurat::query()->first();
    expect(DokumenPersyaratan::query()->where('pengajuan_id', $pengajuan?->id)->count())->toBe(1);
});

test('required unggah blocks submit even if optional file is present', function () {
    Storage::fake();

    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'x']);
    $jenisSurat->syncPersyaratan([
        [
            'nama' => 'Fotokopi KTP',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
            'urutan' => 0,
        ],
        [
            'nama' => 'NPWP (jika ada)',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => false,
            'urutan' => 1,
        ],
    ]);

    $ktp = $jenisSurat->persyaratan()->where('nama', 'Fotokopi KTP')->firstOrFail();
    $npwp = $jenisSurat->persyaratan()->where('nama', 'NPWP (jika ada)')->firstOrFail();
    $opsionalFile = UploadedFile::fake()->image('npwp.jpg');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Hanya opsional')
        ->set('dokumenFiles.'.$npwp->id, $opsionalFile)
        ->call('submit')
        ->assertHasErrors(['dokumenFiles.'.$ktp->id => 'required']);

    expect(PengajuanSurat::query()->count())->toBe(0);
});

test('bawa ke kantor alone allows submit without any file', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'x']);
    $jenisSurat->syncPersyaratan([
        [
            'nama' => 'Surat pengantar RT/RW',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
            'is_wajib' => true,
            'urutan' => 0,
        ],
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Hanya bawa kantor')
        ->call('submit')
        ->assertHasNoErrors();

    expect(DokumenPersyaratan::query()->count())->toBe(0);
});

test('ajukan ulang follows current structured unggah rules', function () {
    Storage::fake();

    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'x']);
    $jenisSurat->syncPersyaratan([
        [
            'nama' => 'Fotokopi KTP',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
            'urutan' => 0,
        ],
        [
            'nama' => 'Pengantar RT/RW',
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
            'is_wajib' => true,
            'urutan' => 1,
        ],
    ]);

    $pengajuanLama = PengajuanSurat::factory()->ditolak('Perbaiki KTP')->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Keperluan resubmit terstruktur',
    ]);

    $syarat = $jenisSurat->persyaratan()->where('cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)->firstOrFail();
    $file = UploadedFile::fake()->image('ktp-baru.jpg');

    Livewire::actingAs($warga)
        ->test(FormPengajuanSurat::class, ['pengajuan' => $pengajuanLama])
        ->assertSet('jenis_surat_id', $jenisSurat->id)
        ->assertSee('Wajib diunggah')
        ->assertSee('Bawa ke kantor')
        ->set('dokumenFiles.'.$syarat->id, $file)
        ->call('submit')
        ->assertHasNoErrors();

    $pengajuanBaru = PengajuanSurat::query()->where('id', '!=', $pengajuanLama->id)->first();
    expect($pengajuanBaru)->not->toBeNull();

    $this->assertDatabaseHas('dokumen_persyaratan', [
        'pengajuan_id' => $pengajuanBaru->id,
        'jenis_surat_persyaratan_id' => $syarat->id,
        'jenis_dokumen' => 'Fotokopi KTP',
    ]);
});
