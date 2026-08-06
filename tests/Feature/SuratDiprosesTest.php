<?php

use App\Livewire\SuratDiproses\DaftarSuratDiproses;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * @return array{pengajuan: PengajuanSurat, surat: SuratTerbit, warga: User, admin: User}
 */
function buatPengajuanDiprosesUntukDaftar(): array
{
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'qr_token' => Str::random(64),
    ]);
    Storage::disk('local')->put($surat->file_path, '%PDF-1.4 test');

    return compact('pengajuan', 'surat', 'warga', 'admin');
}

test('admin dapat membuka halaman daftar surat diproses', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('surat-diproses.index'))
        ->assertSuccessful()
        ->assertSee('Surat Diproses')
        ->assertSeeHtml('data-test="surat-diproses-heading"');
});

test('warga tidak dapat mengakses halaman surat diproses', function () {
    $warga = User::factory()->create(['role' => 'warga']);

    $this->actingAs($warga)
        ->get(route('surat-diproses.index'))
        ->assertForbidden();
});

test('guest diarahkan ke login saat membuka surat diproses', function () {
    $this->get(route('surat-diproses.index'))
        ->assertRedirect(route('login'));
});

test('daftar hanya menampilkan pengajuan berstatus diproses', function () {
    ['pengajuan' => $diproses, 'admin' => $admin] = buatPengajuanDiprosesUntukDaftar();

    $diajukan = PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);
    $siap = PengajuanSurat::factory()->create([
        'status' => PengajuanSurat::STATUS_SIAP_DIAMBIL,
        'diverifikasi_oleh' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(DaftarSuratDiproses::class)
        ->assertSee($diproses->nomor_pengajuan)
        ->assertDontSee($diajukan->nomor_pengajuan)
        ->assertDontSee($siap->nomor_pengajuan)
        ->assertSeeHtml('data-test="surat-diproses-nomor-surat-'.$diproses->id.'"');
});

test('state kosong ramah ketika tidak ada surat diproses', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(DaftarSuratDiproses::class)
        ->assertSee('Tidak ada surat yang sedang diproses saat ini.')
        ->assertSeeHtml('data-test="surat-diproses-empty"');
});

test('tombol lihat detail mengarahkan ke halaman detail', function () {
    ['pengajuan' => $pengajuan, 'admin' => $admin] = buatPengajuanDiprosesUntukDaftar();

    Livewire::actingAs($admin)
        ->test(DaftarSuratDiproses::class)
        ->call('openDetail', $pengajuan->id)
        ->assertRedirect(route('surat-diproses.show', $pengajuan));
});

test('sidebar admin menampilkan menu surat diproses di bawah daftar pengajuan', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('surat-diproses.index'))
        ->assertSuccessful()
        ->assertSeeHtml('data-test="sidebar-surat-diproses"')
        ->assertSee('Surat Diproses');
});
