<?php

use App\Livewire\JenisSurat\DataJenisSurat;
use App\Models\JenisSurat;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('guests are redirected to login from jenis surat page', function () {
    $this->get(route('jenis-surat.index'))
        ->assertRedirect(route('login'));
});

test('warga cannot visit jenis surat admin page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('jenis-surat.index'))
        ->assertForbidden();
});

test('admin can visit jenis surat page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('jenis-surat.index'))
        ->assertOk()
        ->assertSeeLivewire(DataJenisSurat::class);
});

test('admin can search jenis surat by nama', function () {
    $user = User::factory()->admin()->create();
    JenisSurat::factory()->create(['nama_surat' => 'Surat Domisili Khusus']);
    JenisSurat::factory()->create(['nama_surat' => 'Surat Usaha Lain']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->set('search', 'Domisili')
        ->assertSee('Surat Domisili Khusus')
        ->assertDontSee('Surat Usaha Lain');
});

test('empty search shows empty state when no data', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->assertSee('Belum ada data jenis surat');
});

test('admin can soft delete jenis surat', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Diarsipkan']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('softDelete', $jenisSurat->id)
        ->assertDontSee('Surat Diarsipkan');

    expect($jenisSurat->fresh()->trashed())->toBeTrue();
});

test('admin can restore soft deleted jenis surat', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Dipulihkan']);
    $jenisSurat->delete();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->set('showTrashed', true)
        ->call('restore', $jenisSurat->id);

    expect($jenisSurat->fresh()->trashed())->toBeFalse();
});

test('admin can force delete soft deleted jenis surat dan cascade persyaratan', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Hapus Permanen']);
    $id = $jenisSurat->id;
    $persyaratanId = $jenisSurat->persyaratan()->first()->id;

    $jenisSurat->delete();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->set('showTrashed', true)
        ->call('confirmForceDelete', $id)
        ->call('forceDelete');

    $this->assertDatabaseMissing('jenis_surat', ['id' => $id]);
    $this->assertDatabaseMissing('jenis_surat_persyaratan', ['id' => $persyaratanId]);
});

test('force delete only works on trashed records', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Aktif']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('confirmForceDelete', $jenisSurat->id);
})->throws(ModelNotFoundException::class);

test('daftar menampilkan tautan ke halaman tambah dan ubah', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Link']);

    $this->actingAs($user)
        ->get(route('jenis-surat.index'))
        ->assertOk()
        ->assertSee(route('jenis-surat.create'), false)
        ->assertSee(route('jenis-surat.edit', $jenisSurat), false);
});
