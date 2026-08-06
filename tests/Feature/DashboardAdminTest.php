<?php

use App\Livewire\Dashboard\AdminDashboard;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Livewire\Livewire;

test('guest diarahkan ke login dari dashboard admin', function () {
    $this->get(route('dashboard.admin'))->assertRedirect(route('login'));
});

test('warga tidak dapat mengakses dashboard admin', function () {
    $warga = User::factory()->create(['role' => 'warga']);

    $this->actingAs($warga)
        ->get(route('dashboard.admin'))
        ->assertForbidden();
});

test('admin melihat kartu statistik dan empty state saat tidak ada pengajuan aktif', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard.admin'))
        ->assertOk()
        ->assertSee('Dashboard Admin')
        ->assertSee('Tidak ada pengajuan yang perlu ditangani saat ini.')
        ->assertSee('Menunggu Verifikasi')
        ->assertSee('Sedang Diproses')
        ->assertSee('Siap Diambil')
        ->assertSee('Selesai Bulan Ini');
});

test('kartu diajukan menampilkan sub-label tertunda dan severity warning', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'created_at' => now('Asia/Jakarta')->subDays(5),
        'updated_at' => now('Asia/Jakarta')->subDays(5),
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSee($pengajuan->nomor_pengajuan)
        ->assertSee('tertunda > 3 hari')
        ->assertSeeHtml('data-severity="warning"');
});

test('kartu diajukan urgent jika lebih dari 7 hari', function () {
    $admin = User::factory()->admin()->create();
    PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'created_at' => now('Asia/Jakarta')->subDays(9),
        'updated_at' => now('Asia/Jakarta')->subDays(9),
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSeeHtml('data-test="dashboard-admin-card-diajukan"')
        ->assertSeeHtml('data-severity="urgent"')
        ->assertSee('tertunda > 3 hari');
});

test('kartu siap diambil menampilkan jadwal terlewat sebagai urgent', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->siapDiambil()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'tanggal_pengambilan' => now('Asia/Jakarta')->subDay()->toDateString(),
        'siap_diambil_at' => now('Asia/Jakarta')->subDays(3),
        'jam_kerja_label' => 'Senin–Kamis 08.00–16.00 WIB',
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSee('jadwal terlewat')
        ->assertSeeHtml('data-test="dashboard-admin-card-siap-diambil"')
        ->assertSeeHtml('data-severity="urgent"')
        ->assertSee('Perlu Ditindaklanjuti Segera');
});

test('seksi perlu ditindaklanjuti hilang jika tidak ada item mendesak', function () {
    $admin = User::factory()->admin()->create();
    PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'created_at' => now('Asia/Jakarta')->subDay(),
        'updated_at' => now('Asia/Jakarta')->subDay(),
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertDontSee('Perlu Ditindaklanjuti Segera');
});

test('tombol tangani mengarahkan ke halaman verifikasi untuk status diajukan', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'created_at' => now('Asia/Jakarta')->subDays(8),
        'updated_at' => now('Asia/Jakarta')->subDays(8),
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->call('tangani', $pengajuan->id)
        ->assertRedirect(route('verifikasi.show', $pengajuan));
});

test('kartu selesai bulan ini menghitung berdasarkan qr_digunakan_at', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->selesai()->create([
        'diverifikasi_oleh' => $admin->id,
    ]);
    SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'qr_digunakan_at' => now('Asia/Jakarta'),
        'qr_status' => SuratTerbit::QR_STATUS_INVALID,
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSeeHtml('data-test="dashboard-admin-card-selesai-total">1</div>');
});

test('historis disetujui dihitung pada kartu sedang diproses', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->disetujui()->create([
        'diverifikasi_oleh' => $admin->id,
    ]);
    SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'tanggal_terbit' => now('Asia/Jakarta')->subDays(6)->toDateString(),
    ]);

    Livewire::actingAs($admin)
        ->test(AdminDashboard::class)
        ->assertSeeHtml('data-test="dashboard-admin-card-diproses-total">1</div>')
        ->assertSee('tertunda > 5 hari');
});
