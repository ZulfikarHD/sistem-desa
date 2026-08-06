<?php

use App\Livewire\Pengajuan\RiwayatPengajuan;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from riwayat pengajuan page', function () {
    $this->get(route('pengajuan-surat.riwayat'))
        ->assertRedirect(route('login'));
});

test('admin cannot visit riwayat pengajuan warga page', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)
        ->get(route('pengajuan-surat.riwayat'))
        ->assertForbidden();
});

test('warga can visit riwayat pengajuan page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('pengajuan-surat.riwayat'))
        ->assertOk()
        ->assertSeeLivewire(RiwayatPengajuan::class);
});

test('riwayat shows only pengajuan belonging to authenticated warga', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $otherWarga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $own = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0100',
    ]);

    PengajuanSurat::factory()->create([
        'user_id' => $otherWarga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0101',
    ]);

    Livewire::actingAs($warga)
        ->test(RiwayatPengajuan::class)
        ->assertSee($own->nomor_pengajuan)
        ->assertDontSee('PJ-'.now()->format('Ymd').'-0101');
});

test('riwayat shows ajukan ulang button only for ditolak status', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $ditolak = PengajuanSurat::factory()->ditolak('Dokumen KTP buram')->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0200',
    ]);

    $diajukan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0201',
    ]);

    Livewire::actingAs($warga)
        ->test(RiwayatPengajuan::class)
        ->assertSee('Ajukan Ulang')
        ->assertSee('Dokumen KTP buram')
        ->assertSeeHtml('riwayat-pengajuan-ajukan-ulang-'.$ditolak->id)
        ->assertDontSeeHtml('riwayat-pengajuan-ajukan-ulang-'.$diajukan->id);
});

test('riwayat can filter by status', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    PengajuanSurat::factory()->ditolak()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0300',
    ]);

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0301',
    ]);

    Livewire::actingAs($warga)
        ->test(RiwayatPengajuan::class)
        ->set('statusFilter', PengajuanSurat::STATUS_DITOLAK)
        ->assertSee('PJ-'.now()->format('Ymd').'-0300')
        ->assertDontSee('PJ-'.now()->format('Ymd').'-0301');
});
