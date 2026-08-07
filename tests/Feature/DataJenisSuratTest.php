<?php

use App\Livewire\JenisSurat\DataJenisSurat;
use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
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

test('admin can create jenis surat dengan persyaratan terstruktur', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Keterangan Domisili')
        ->set('deskripsi', 'Surat untuk keterangan tempat tinggal')
        ->set('persyaratanRows', [
            [
                'key' => 'a',
                'nama' => 'Fotokopi KTP',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
            [
                'key' => 'b',
                'nama' => 'Surat pengantar RT/RW',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                'is_wajib' => true,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $jenisSurat = JenisSurat::query()->where('nama_surat', 'Surat Keterangan Domisili')->first();

    expect($jenisSurat)->not->toBeNull()
        ->and($jenisSurat->deskripsi)->toBe('Surat untuk keterangan tempat tinggal')
        ->and($jenisSurat->persyaratan_dokumen)->toContain('Fotokopi KTP')
        ->and($jenisSurat->persyaratan)->toHaveCount(2);

    $this->assertDatabaseHas('jenis_surat_persyaratan', [
        'jenis_surat_id' => $jenisSurat->id,
        'nama' => 'Fotokopi KTP',
        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
        'is_wajib' => true,
    ]);

    $this->assertDatabaseHas('jenis_surat_persyaratan', [
        'jenis_surat_id' => $jenisSurat->id,
        'nama' => 'Surat pengantar RT/RW',
        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
    ]);
});

test('admin can create jenis surat without deskripsi', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Tanpa Deskripsi')
        ->set('deskripsi', '')
        ->set('persyaratanRows', [
            [
                'key' => 'a',
                'nama' => 'Fotokopi KTP',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('jenis_surat', [
        'nama_surat' => 'Surat Tanpa Deskripsi',
        'deskripsi' => null,
    ]);
});

test('admin can update jenis surat dan baris persyaratan', function () {
    $user = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Lama',
        'deskripsi' => 'Deskripsi lama',
    ]);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('edit', $jenisSurat->id)
        ->set('nama_surat', 'Surat Baru')
        ->set('deskripsi', 'Deskripsi baru')
        ->set('persyaratanRows', [
            [
                'key' => 'x',
                'nama' => 'Dokumen baru',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_INFO,
                'is_wajib' => true,
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $fresh = $jenisSurat->fresh();

    expect($fresh->nama_surat)->toBe('Surat Baru')
        ->and($fresh->deskripsi)->toBe('Deskripsi baru')
        ->and($fresh->persyaratan_dokumen)->toContain('Dokumen baru')
        ->and($fresh->persyaratan)->toHaveCount(1)
        ->and($fresh->persyaratan->first()->cara_pemenuhan)->toBe(JenisSuratPersyaratan::CARA_INFO);
});

test('nama_surat is required', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', '')
        ->set('persyaratanRows', [
            [
                'key' => 'a',
                'nama' => 'Fotokopi KTP',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
        ])
        ->call('save')
        ->assertHasErrors(['nama_surat' => 'required']);
});

test('minimal satu baris persyaratan wajib saat simpan', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Baru')
        ->set('persyaratanRows', [])
        ->call('save')
        ->assertHasErrors(['persyaratanRows']);
});

test('nama syarat wajib diisi', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Baru')
        ->set('persyaratanRows', [
            [
                'key' => 'a',
                'nama' => '',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
        ])
        ->call('save')
        ->assertHasErrors(['persyaratanRows.0.nama' => 'required']);
});

test('nama_surat must be unique on create', function () {
    $user = User::factory()->admin()->create();
    JenisSurat::factory()->create(['nama_surat' => 'Surat Duplikat']);

    Livewire::actingAs($user)
        ->test(DataJenisSurat::class)
        ->call('create')
        ->set('nama_surat', 'Surat Duplikat')
        ->set('persyaratanRows', [
            [
                'key' => 'a',
                'nama' => 'Fotokopi KTP',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
        ])
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
