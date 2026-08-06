<?php

use App\Livewire\Pengajuan\FormPengajuanSurat;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from pengajuan surat page', function () {
    $this->get(route('pengajuan-surat.create'))
        ->assertRedirect(route('login'));
});

test('admin cannot visit pengajuan surat warga page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('pengajuan-surat.create'))
        ->assertForbidden();
});

test('warga can visit pengajuan surat form page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('pengajuan-surat.create'))
        ->assertOk()
        ->assertSeeLivewire(FormPengajuanSurat::class);
});

test('warga can submit pengajuan surat with auto generated nomor and defaults', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Keterangan Domisili',
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Untuk keperluan administrasi bank')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedNomor', fn (?string $nomor) => $nomor !== null && str_starts_with($nomor, 'PJ-'.now()->format('Ymd').'-'));

    $this->assertDatabaseHas('pengajuan_surat', [
        'user_id' => $user->id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Untuk keperluan administrasi bank',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    $pengajuan = PengajuanSurat::query()->first();
    expect($pengajuan?->nomor_pengajuan)->toMatch('/^PJ-\d{8}-\d{4}$/');
    expect($pengajuan?->tanggal_pengajuan?->toDateString())->toBe(now()->toDateString());
});

test('submit fails when jenis surat is not selected', function () {
    $user = User::factory()->create(['role' => 'warga']);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', null)
        ->set('keperluan', 'Keperluan valid')
        ->call('submit')
        ->assertHasErrors(['jenis_surat_id' => 'required']);
});

test('submit fails when keperluan is empty', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', '')
        ->call('submit')
        ->assertHasErrors(['keperluan' => 'required']);
});

test('submit fails when jenis surat is soft deleted', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $jenisSurat->delete();

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan valid')
        ->call('submit')
        ->assertHasErrors(['jenis_surat_id']);
});

test('nomor pengajuan increments sequentially for same day', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    PengajuanSurat::factory()->create([
        'user_id' => $user->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0001',
        'tanggal_pengajuan' => now()->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Pengajuan kedua')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedNomor', 'PJ-'.now()->format('Ymd').'-0002');
});

test('create another resets success state', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan valid')
        ->call('submit')
        ->assertSet('submittedNomor', fn (?string $nomor) => $nomor !== null)
        ->call('createAnother')
        ->assertSet('submittedNomor', null)
        ->assertSet('jenis_surat_id', null)
        ->assertSet('keperluan', '');
});
