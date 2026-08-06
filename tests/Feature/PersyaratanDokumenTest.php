<?php

use App\Livewire\JenisSurat\PersyaratanDokumen;
use App\Models\JenisSurat;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('guests are redirected to login from persyaratan dokumen page', function () {
    $this->get(route('persyaratan-dokumen.index'))
        ->assertRedirect(route('login'));
});

test('admin cannot visit persyaratan dokumen warga page', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->get(route('persyaratan-dokumen.index'))
        ->assertForbidden();
});

test('warga can visit persyaratan dokumen page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('persyaratan-dokumen.index'))
        ->assertOk()
        ->assertSeeLivewire(PersyaratanDokumen::class);
});

test('warga sees active jenis surat with deskripsi and persyaratan', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Domisili Warga Test',
        'deskripsi' => 'Keterangan tempat tinggal warga',
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi KK",
    ]);

    $this->actingAs($user)
        ->get(route('persyaratan-dokumen.index'))
        ->assertOk()
        ->assertSee('Surat Domisili Warga Test')
        ->assertSee('Keterangan tempat tinggal warga')
        ->assertSee('Fotokopi KTP');

    expect($jenisSurat->exists)->toBeTrue();
});

test('warga does not see soft deleted jenis surat', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $active = JenisSurat::factory()->create(['nama_surat' => 'Surat Aktif Warga']);
    $archived = JenisSurat::factory()->create(['nama_surat' => 'Surat Arsip Warga']);
    $archived->delete();

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->assertSee('Surat Aktif Warga')
        ->assertDontSee('Surat Arsip Warga');

    expect($active->trashed())->toBeFalse();
});

test('warga can open detail modal for jenis surat', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Detail Warga',
        'deskripsi' => 'Deskripsi lengkap detail',
        'persyaratan_dokumen' => "- KTP asli\n- KK asli",
    ]);

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->call('openDetail', $jenisSurat->id)
        ->assertSet('showDetail', true)
        ->assertSet('selectedId', $jenisSurat->id)
        ->assertSee('Surat Detail Warga')
        ->assertSee('Deskripsi lengkap detail')
        ->assertSee('KTP asli');
});

test('opening detail for missing jenis surat fails', function () {
    $user = User::factory()->create(['role' => 'warga']);

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->call('openDetail', 999999);
})->throws(ModelNotFoundException::class);

test('warga can search jenis surat', function () {
    $user = User::factory()->create(['role' => 'warga']);
    JenisSurat::factory()->create(['nama_surat' => 'Surat Cari Target Warga']);
    JenisSurat::factory()->create(['nama_surat' => 'Surat Cari Lain Warga']);

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->set('search', 'Target')
        ->assertSee('Surat Cari Target Warga')
        ->assertDontSee('Surat Cari Lain Warga');
});

test('empty state is shown when no jenis surat exists', function () {
    $user = User::factory()->create(['role' => 'warga']);

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->assertSee('Belum ada jenis surat');
});

test('jenis surat without deskripsi shows fallback text', function () {
    $user = User::factory()->create(['role' => 'warga']);
    JenisSurat::factory()->create([
        'nama_surat' => 'Surat Tanpa Deskripsi',
        'deskripsi' => null,
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);

    Livewire::actingAs($user)
        ->test(PersyaratanDokumen::class)
        ->assertSee('Surat Tanpa Deskripsi')
        ->assertSee('Tidak ada deskripsi.');
});
