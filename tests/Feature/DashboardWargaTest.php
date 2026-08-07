<?php

use App\Livewire\Dashboard\WargaDashboard;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Livewire\Livewire;

test('guest diarahkan ke login dari dashboard warga', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admin tidak dapat mengakses dashboard warga', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('warga tanpa pengajuan aktif melihat CTA ajukan surat', function () {
    $warga = User::factory()->create(['role' => 'warga']);

    $this->actingAs($warga)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Warga')
        ->assertSee('Ajukan Surat Sekarang')
        ->assertSee('Belum ada pengajuan aktif');
});

test('warga melihat hero status pengajuan aktif dengan penjelasan', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'created_at' => now('Asia/Jakarta')->subDays(2),
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee($pengajuan->nomor_pengajuan)
        ->assertSee('Pengajuan Anda sedang menunggu ditinjau oleh petugas desa.')
        ->assertSee('Sudah 2 hari di status ini')
        ->assertSee('Ajukan Surat Baru');
});

test('warga dengan status diproses melihat tombol unduh surat', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'file_path' => 'surat-terbit/'.$pengajuan->id.'/surat.pdf',
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee('Surat Anda sedang disiapkan oleh petugas')
        ->assertSee('Unduh Surat')
        ->assertSeeHtml('data-test="dashboard-warga-unduh-'.$pengajuan->id.'"');
});

test('warga dengan status siap diambil melihat jadwal pengambilan menonjol', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->siapDiambil()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    $tanggal = now('Asia/Jakarta')->nextWeekday()->toDateString();
    SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'tanggal_pengambilan' => $tanggal,
        'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',
        'siap_diambil_at' => now('Asia/Jakarta'),
        'file_path' => 'surat-terbit/'.$pengajuan->id.'/surat.pdf',
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee('sudah siap diambil')
        ->assertSee('Senin–Kamis 08.00–16.00 WIB')
        ->assertSeeHtml('data-test="dashboard-warga-hero-jadwal-'.$pengajuan->id.'"');
});

test('banner notifikasi muncul jika ada notifikasi belum dibaca', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create(['user_id' => $warga->id]);
    Notifikasi::factory()->create([
        'user_id' => $warga->id,
        'pengajuan_id' => $pengajuan->id,
        'status_baca' => Notifikasi::STATUS_BELUM,
        'pesan' => 'Notifikasi uji dashboard warga',
        'created_at' => now(),
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee('Anda memiliki 1 notifikasi baru')
        ->assertSee('Notifikasi uji dashboard warga')
        ->assertSeeHtml('data-test="dashboard-warga-notif-dot-');
});

test('elapsed time diajukan lebih dari 7 hari memakai warna amber', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'created_at' => now('Asia/Jakarta')->subDays(8),
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee('Sudah 8 hari di status ini')
        ->assertSeeHtml('text-amber-700');
});

test('warga hanya melihat pengajuan miliknya sendiri di hero', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $lain = User::factory()->create(['role' => 'warga']);
    $milik = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);
    $bukan = PengajuanSurat::factory()->create([
        'user_id' => $lain->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'nomor_pengajuan' => 'PJ-20990101-9999',
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee($milik->nomor_pengajuan)
        ->assertDontSee($bukan->nomor_pengajuan);
});

test('hero menampilkan alur status dan judul fokus status surat', function () {
    $warga = User::factory()->create(['role' => 'warga', 'name' => 'Budi Warga']);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'status' => PengajuanSurat::STATUS_DIPROSES,
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee('Status surat Anda')
        ->assertSee('Halo, Budi Warga')
        ->assertSeeHtml('data-test="dashboard-warga-hero-alur-'.$pengajuan->id.'"')
        ->assertSee('Diajukan')
        ->assertSee('Diproses')
        ->assertSee('Siap diambil');
});

test('siap diambil tanpa tanggal pengambilan tidak merender blok jadwal', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->siapDiambil()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'tanggal_pengambilan' => null,
        'jam_kerja_label' => null,
        'siap_diambil_at' => now('Asia/Jakarta'),
        'file_path' => 'surat-terbit/'.$pengajuan->id.'/surat.pdf',
    ]);

    Livewire::actingAs($warga)
        ->test(WargaDashboard::class)
        ->assertSee('sudah siap diambil')
        ->assertDontSeeHtml('data-test="dashboard-warga-hero-jadwal-'.$pengajuan->id.'"');
});
