<?php

use App\Livewire\Verifikasi\DetailPengajuanVerifikasi;
use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('verifikasi detail separates online uploads and physical checklist', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Checklist US95',
        'persyaratan_dokumen' => 'placeholder',
    ]);
    $jenisSurat->syncPersyaratan([
        ['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
        ['nama' => 'NPWP (jika ada)', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => false],
        ['nama' => 'Surat pengantar RT/RW', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR],
        ['nama' => 'Catatan info', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_INFO],
    ]);

    $syarat = $jenisSurat->fresh()->persyaratan()->get()->keyBy('nama');
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    $path = 'pengajuan-dokumen/'.$pengajuan->id.'/ktp.jpg';
    Storage::disk('local')->put($path, UploadedFile::fake()->image('ktp.jpg')->getContent());

    $dokumen = DokumenPersyaratan::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'jenis_surat_persyaratan_id' => $syarat['Fotokopi KTP']->id,
        'jenis_dokumen' => 'Fotokopi KTP',
        'file_path' => $path,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan->fresh()])
        ->assertSee('Diunggah online')
        ->assertSee('Harus dicek / dibawa ke kantor')
        ->assertSeeHtml('verifikasi-detail-dokumen-preview-'.$dokumen->id)
        ->assertSeeHtml('verifikasi-detail-dokumen-optional-empty-'.$syarat['NPWP (jika ada)']->id)
        ->assertSee('Tidak diunggah — diperbolehkan')
        ->assertSeeHtml('verifikasi-detail-checklist-fisik-item-'.$syarat['Surat pengantar RT/RW']->id)
        ->assertSee('Surat pengantar RT/RW')
        ->assertDontSeeHtml('verifikasi-detail-checklist-fisik-item-'.$syarat['Catatan info']->id)
        ->assertSeeHtml('verifikasi-detail-setujui-button')
        ->assertSeeHtml('verifikasi-detail-tolak-button');
});

test('verifikasi detail keeps approve reject buttons when checklist present', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'placeholder']);
    $jenisSurat->syncPersyaratan([
        ['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
        ['nama' => 'Pengantar RT', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR],
    ]);

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan]);

    expect($component->instance()->canVerify())->toBeTrue();
    $component
        ->assertSeeHtml('verifikasi-detail-setujui-button')
        ->assertSeeHtml('verifikasi-detail-tolak-button')
        ->assertSee('Harus dicek / dibawa ke kantor');
});

test('physical checklist empty state when no bawa kantor syarat', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['persyaratan_dokumen' => 'placeholder']);
    $jenisSurat->syncPersyaratan([
        ['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
    ]);
    $syaratKtp = $jenisSurat->fresh()->persyaratan()->firstOrFail();

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $path = 'pengajuan-dokumen/'.$pengajuan->id.'/ktp.jpg';
    Storage::disk('local')->put($path, UploadedFile::fake()->image('ktp.jpg')->getContent());

    DokumenPersyaratan::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'jenis_surat_persyaratan_id' => $syaratKtp->id,
        'jenis_dokumen' => 'Fotokopi KTP',
        'file_path' => $path,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan->fresh()])
        ->assertSeeHtml('verifikasi-detail-checklist-fisik-empty')
        ->assertSee('Tidak ada syarat yang harus dibawa ke kantor');
});
