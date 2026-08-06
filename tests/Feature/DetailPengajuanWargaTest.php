<?php

use App\Livewire\Pengajuan\DetailPengajuanWarga;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from detail pengajuan page', function () {
    $pengajuan = PengajuanSurat::factory()->create();

    $this->get(route('pengajuan-surat.show', $pengajuan))
        ->assertRedirect(route('login'));
});

test('admin cannot visit detail pengajuan warga page', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->create();

    $this->actingAs($admin)
        ->get(route('pengajuan-surat.show', $pengajuan))
        ->assertForbidden();
});

test('warga cannot view detail pengajuan owned by another warga', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $otherWarga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $otherWarga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $this->actingAs($warga)
        ->get(route('pengajuan-surat.show', $pengajuan))
        ->assertForbidden();
});

test('warga can view detail of own pengajuan with full info', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Keterangan']);

    $pengajuan = PengajuanSurat::factory()->ditolak('Dokumen buram')->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6001',
        'keperluan' => 'Keperluan detail test',
    ]);

    Livewire::actingAs($warga)
        ->test(DetailPengajuanWarga::class, ['pengajuan' => $pengajuan])
        ->assertSee('PJ-'.now()->format('Ymd').'-6001')
        ->assertSee('Surat Keterangan')
        ->assertSee('Keperluan detail test')
        ->assertSee('Dokumen buram')
        ->assertSeeHtml('detail-pengajuan-warga-ajukan-ulang');
});
