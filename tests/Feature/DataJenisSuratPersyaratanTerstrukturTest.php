<?php

use App\Livewire\JenisSurat\FormJenisSurat;
use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use App\Models\User;
use Livewire\Livewire;

test('admin dapat menambah menghapus dan mengurutkan baris persyaratan', function () {
    $user = User::factory()->admin()->create();

    $component = Livewire::actingAs($user)
        ->test(FormJenisSurat::class)
        ->assertSet('persyaratanRows.0.cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)
        ->assertSet('persyaratanRows.0.is_wajib', 1)
        ->call('addPersyaratanRow');

    expect($component->get('persyaratanRows'))->toHaveCount(2);

    $component
        ->set('persyaratanRows.0.nama', 'Pertama')
        ->set('persyaratanRows.1.nama', 'Kedua')
        ->call('movePersyaratanRowDown', 0)
        ->assertSet('persyaratanRows.0.nama', 'Kedua')
        ->assertSet('persyaratanRows.1.nama', 'Pertama')
        ->call('movePersyaratanRowUp', 1)
        ->assertSet('persyaratanRows.0.nama', 'Pertama')
        ->call('removePersyaratanRow', 1);

    expect($component->get('persyaratanRows'))->toHaveCount(1);
});

test('pilihan wajib hanya relevan untuk cara unggah', function () {
    $user = User::factory()->admin()->create();

    Livewire::actingAs($user)
        ->test(FormJenisSurat::class)
        ->set('persyaratanRows.0.cara_pemenuhan', JenisSuratPersyaratan::CARA_BAWA_KANTOR)
        ->assertSet('persyaratanRows.0.is_wajib', 1)
        ->set('persyaratanRows.0.cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)
        ->set('persyaratanRows.0.is_wajib', 0)
        ->set('nama_surat', 'Surat Opsional')
        ->set('persyaratanRows.0.nama', 'Bukti pendukung')
        ->call('save')
        ->assertHasNoErrors();

    $jenisSurat = JenisSurat::query()->where('nama_surat', 'Surat Opsional')->first();

    expect($jenisSurat->persyaratan->first()->is_wajib)->toBeFalse()
        ->and($jenisSurat->persyaratan->first()->cara_pemenuhan)->toBe(JenisSuratPersyaratan::CARA_UNGGAH);
});

test('template cepat domisili mengisi tiga baris default', function () {
    $user = User::factory()->admin()->create();

    $component = Livewire::actingAs($user)
        ->test(FormJenisSurat::class)
        ->call('applyDomisiliTemplate')
        ->assertSet('persyaratanRows.0.nama', 'Fotokopi KTP')
        ->assertSet('persyaratanRows.0.cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)
        ->assertSet('persyaratanRows.0.is_wajib', 1)
        ->assertSet('persyaratanRows.1.nama', 'Fotokopi Kartu Keluarga (KK)')
        ->assertSet('persyaratanRows.1.cara_pemenuhan', JenisSuratPersyaratan::CARA_UNGGAH)
        ->assertSet('persyaratanRows.2.nama', 'Surat pengantar RT/RW')
        ->assertSet('persyaratanRows.2.cara_pemenuhan', JenisSuratPersyaratan::CARA_BAWA_KANTOR);

    expect($component->get('persyaratanRows'))->toHaveCount(3);
});

test('pratinjau badge label mengikuti cara dan is_wajib', function () {
    $unggahWajib = new JenisSuratPersyaratan([
        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
        'is_wajib' => true,
    ]);
    $unggahOpsional = new JenisSuratPersyaratan([
        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
        'is_wajib' => false,
    ]);
    $bawa = new JenisSuratPersyaratan([
        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
        'is_wajib' => true,
    ]);
    $info = new JenisSuratPersyaratan([
        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_INFO,
        'is_wajib' => true,
    ]);

    expect($unggahWajib->badgeLabel())->toBe('Wajib diunggah')
        ->and($unggahOpsional->badgeLabel())->toBe('Boleh dikosongkan')
        ->and($bawa->badgeLabel())->toBe('Bawa ke kantor')
        ->and($info->badgeLabel())->toBe('Informasi');
});
