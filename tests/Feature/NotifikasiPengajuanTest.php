<?php

use App\Livewire\Notifikasi\PanelNotifikasi;
use App\Livewire\Verifikasi\DetailPengajuanVerifikasi;
use App\Models\JenisSurat;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\User;
use Livewire\Livewire;

test('admin setujui pengajuan creates single notifikasi diproses for warga', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Domisili']);

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-5001',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->call('setujui');

    $notifikasi = Notifikasi::query()->where('pengajuan_id', $pengajuan->id)->orderBy('id')->get();

    expect($notifikasi)->toHaveCount(1)
        ->and($notifikasi[0]->user_id)->toBe($warga->id)
        ->and($notifikasi[0]->status_baca)->toBe(Notifikasi::STATUS_BELUM)
        ->and($notifikasi[0]->pesan)->toBe(
            'Pengajuan Surat Domisili Anda (#PJ-'.now()->format('Ymd').'-5001) sedang diproses. Surat Anda sedang disiapkan.'
        );
});

test('admin tolak pengajuan creates notifikasi for warga', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Usaha']);

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-5002',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->set('catatanAdmin', 'Dokumen tidak lengkap')
        ->call('tolak');

    $notifikasi = Notifikasi::query()->where('pengajuan_id', $pengajuan->id)->sole();

    expect($notifikasi->user_id)->toBe($warga->id)
        ->and($notifikasi->status_baca)->toBe(Notifikasi::STATUS_BELUM)
        ->and($notifikasi->pesan)->toContain('ditolak');
});

test('admin buka detail diajukan does not create notifikasi', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Nikah']);

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-5003',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan]);

    expect(Notifikasi::query()->where('pengajuan_id', $pengajuan->id)->count())->toBe(0)
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIAJUKAN);
});

test('panel notifikasi shows unread badge count for warga', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    Notifikasi::factory()->count(2)->create([
        'user_id' => $warga->id,
        'pengajuan_id' => $pengajuan->id,
    ]);

    Notifikasi::factory()->dibaca()->create([
        'user_id' => $warga->id,
        'pengajuan_id' => $pengajuan->id,
    ]);

    Livewire::actingAs($warga)
        ->test(PanelNotifikasi::class)
        ->assertSeeHtml('panel-notifikasi-badge')
        ->assertSee('2');
});

test('klik notifikasi marks as read and redirects to detail pengajuan', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $notifikasi = Notifikasi::factory()->create([
        'user_id' => $warga->id,
        'pengajuan_id' => $pengajuan->id,
        'pesan' => 'Pengajuan disetujui.',
    ]);

    Livewire::actingAs($warga)
        ->test(PanelNotifikasi::class)
        ->call('bukaNotifikasi', $notifikasi->id)
        ->assertRedirect(route('pengajuan-surat.show', $pengajuan));

    expect($notifikasi->fresh()->status_baca)->toBe(Notifikasi::STATUS_DIBACA);
});

test('warga cannot open notifikasi belonging to another warga', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $otherWarga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $otherWarga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $notifikasi = Notifikasi::factory()->create([
        'user_id' => $otherWarga->id,
        'pengajuan_id' => $pengajuan->id,
    ]);

    Livewire::actingAs($warga)
        ->test(PanelNotifikasi::class)
        ->call('bukaNotifikasi', $notifikasi->id)
        ->assertStatus(404);
});
