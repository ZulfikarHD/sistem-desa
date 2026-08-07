<?php

use App\Livewire\JenisSurat\PersyaratanDokumen;
use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use App\Models\User;
use Livewire\Livewire;

test('list and detail show structured persyaratan with badges', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Badge Publik US94',
        'deskripsi' => 'Deskripsi badge US-9.4',
        'persyaratan_dokumen' => 'placeholder',
    ]);
    $jenisSurat->syncPersyaratan([
        ['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
        ['nama' => 'NPWP (jika ada)', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => false],
        ['nama' => 'Surat pengantar RT/RW', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR],
        ['nama' => 'Datang pada jam kerja', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_INFO],
    ]);

    $ids = $jenisSurat->fresh()->persyaratan()->pluck('id', 'nama');

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->assertSee('Surat Badge Publik US94')
        ->assertSeeHtml('persyaratan-dokumen-preview-badge-'.$ids['Fotokopi KTP'])
        ->assertSee('Wajib diunggah')
        ->assertSee('Boleh dikosongkan')
        ->assertSee('Bawa ke kantor')
        ->assertSee('Informasi')
        ->call('openDetail', $jenisSurat->id)
        ->assertSet('showDetail', true)
        ->assertSeeHtml('persyaratan-dokumen-detail-badge-'.$ids['Fotokopi KTP'])
        ->assertSeeHtml('persyaratan-dokumen-detail-item-'.$ids['Surat pengantar RT/RW'])
        ->assertSee('Siapkan berkas ini dan bawa saat diminta petugas');
});

test('guest also sees persyaratan badges without login', function () {
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Guest Badge US94',
        'persyaratan_dokumen' => 'placeholder',
    ]);
    $jenisSurat->syncPersyaratan([
        ['nama' => 'Fotokopi KK', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
        ['nama' => 'Pengantar RT', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR],
    ]);

    Livewire::test(PersyaratanDokumen::class)
        ->assertSee('Surat Guest Badge US94')
        ->assertSee('Wajib diunggah')
        ->assertSee('Bawa ke kantor')
        ->assertSee('Daftar/Login untuk Mengajukan');
});

test('search matches structured persyaratan nama', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $target = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Cari Nama Biasa US94',
        'deskripsi' => 'Deskripsi biasa',
        'persyaratan_dokumen' => 'placeholder',
    ]);
    $target->syncPersyaratan([
        ['nama' => 'Slip Gaji UnikUS94', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => false],
    ]);

    $other = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Lain US94',
        'persyaratan_dokumen' => 'placeholder',
    ]);
    $other->syncPersyaratan([
        ['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
    ]);

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->set('search', 'Slip Gaji UnikUS94')
        ->assertSee('Surat Cari Nama Biasa US94')
        ->assertDontSee('Surat Lain US94');
});

test('soft deleted jenis surat remain hidden with structured rows', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $active = JenisSurat::factory()->create(['nama_surat' => 'Surat Aktif Badge US94']);
    $archived = JenisSurat::factory()->create(['nama_surat' => 'Surat Arsip Badge US94']);
    $archived->syncPersyaratan([
        ['nama' => 'Fotokopi KTP', 'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH, 'is_wajib' => true],
    ]);
    $archived->delete();

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->assertSee('Surat Aktif Badge US94')
        ->assertDontSee('Surat Arsip Badge US94');

    expect($active->trashed())->toBeFalse();
});
