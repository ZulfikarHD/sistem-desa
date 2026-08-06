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

test('admin can create jenis surat', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Keterangan Domisili')
        ->set('deskripsi', 'Surat untuk keterangan tempat tinggal')
        ->set('persyaratan_dokumen', "- Fotokopi KTP\n- Fotokopi KK")
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('jenis_surat', [
        'nama_surat' => 'Surat Keterangan Domisili',
        'deskripsi' => 'Surat untuk keterangan tempat tinggal',
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi KK",
    ]);
});

test('admin can create jenis surat without deskripsi', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Tanpa Deskripsi')
        ->set('deskripsi', '')
        ->set('persyaratan_dokumen', '- Fotokopi KTP')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('jenis_surat', [
        'nama_surat' => 'Surat Tanpa Deskripsi',
        'deskripsi' => null,
    ]);
});

test('admin can update jenis surat', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Lama',
        'deskripsi' => 'Deskripsi lama',
        'persyaratan_dokumen' => '- Dokumen lama',
    ]);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('edit', $jenisSurat->id)
        ->set('nama_surat', 'Surat Baru')
        ->set('deskripsi', 'Deskripsi baru')
        ->set('persyaratan_dokumen', '- Dokumen baru')
        ->call('save')
        ->assertHasNoErrors();

    expect($jenisSurat->fresh()->nama_surat)->toBe('Surat Baru')
        ->and($jenisSurat->fresh()->deskripsi)->toBe('Deskripsi baru')
        ->and($jenisSurat->fresh()->persyaratan_dokumen)->toBe('- Dokumen baru');
});

test('nama_surat is required', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', '')
        ->set('persyaratan_dokumen', '- Fotokopi KTP')
        ->call('save')
        ->assertHasErrors(['nama_surat' => 'required']);
});

test('persyaratan_dokumen is required', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Baru')
        ->set('persyaratan_dokumen', '')
        ->call('save')
        ->assertHasErrors(['persyaratan_dokumen' => 'required']);
});

test('nama_surat must be unique on create', function () {
    $user = User::factory()->admin()->create();
    JenisSurat::factory()->create(['nama_surat' => 'Surat Duplikat']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Duplikat')
        ->set('persyaratan_dokumen', '- Fotokopi KTP')
        ->call('save')
        ->assertHasErrors(['nama_surat' => 'unique']);
});

test('nama_surat unique rule ignores current record on edit', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Tetap']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('edit', $jenisSurat->id)
        ->set('deskripsi', 'Deskripsi diperbarui')
        ->call('save')
        ->assertHasNoErrors();

    expect($jenisSurat->fresh()->deskripsi)->toBe('Deskripsi diperbarui');
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

test('admin can force delete soft deleted jenis surat', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Hapus Permanen']);
    $jenisSurat->delete();
    $id = $jenisSurat->id;

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->set('showTrashed', true)
        ->call('confirmForceDelete', $id)
        ->call('forceDelete');

    $this->assertDatabaseMissing('jenis_surat', ['id' => $id]);
});

test('force delete only works on trashed records', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Aktif']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('confirmForceDelete', $jenisSurat->id);
})->throws(ModelNotFoundException::class);
