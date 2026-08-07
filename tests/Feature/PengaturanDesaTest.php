<?php

use App\Livewire\Pengaturan\FormPengaturanDesa;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\PengaturanDesa;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('admin dapat membuka dan menyimpan pengaturan desa', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(FormPengaturanDesa::class)
        ->assertSeeHtml('data-test="pengaturan-desa-heading"')
        ->set('nama_desa', 'Desa Widodaren')
        ->set('kecamatan', 'Kecamatan Gerih')
        ->set('kabupaten', 'Kabupaten Ngawi')
        ->set('provinsi', 'Jawa Timur')
        ->set('alamat_kantor', 'Jl. Raya Widodaren No. 1')
        ->set('kode_pos', '63271')
        ->set('telepon', '0351-123456')
        ->set('penandatangan_nama', 'Budi Santoso')
        ->set('penandatangan_jabatan', 'Kepala Desa')
        ->set('kode_klasifikasi', '470')
        ->set('kode_desa', 'DS-WDN')
        ->call('simpan')
        ->assertHasNoErrors();

    $row = PengaturanDesa::query()->first();
    expect($row)->not->toBeNull()
        ->and($row->nama_desa)->toBe('Desa Widodaren')
        ->and($row->kabupaten)->toBe('Kabupaten Ngawi');
});

test('warga tidak dapat mengakses pengaturan desa', function () {
    $warga = User::factory()->create(['role' => 'warga']);

    $this->actingAs($warga)
        ->get(route('pengaturan-desa.edit'))
        ->assertForbidden();
});

test('pdf bukti pengambilan memakai nama desa dari pengaturan', function () {
    PengaturanDesa::instance()->update(['nama_desa' => 'Desa Uji Kop']);

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga', 'nik' => '3201010101010099']);
    $jenis = JenisSurat::factory()->create(['nama_surat' => 'Surat Keterangan Domisili']);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenis->id,
        'diverifikasi_oleh' => $admin->id,
    ]);

    $surat = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);

    Storage::disk('local')->assertExists($surat->file_path);
    expect(Storage::disk('local')->get($surat->file_path))->toStartWith('%PDF');

    $html = view(SuratTerbit::resolveTemplateView(), [
        'pengajuan' => $pengajuan->fresh(['user', 'jenisSurat']),
        'pemohon' => $warga,
        'jenisSurat' => $jenis,
        'nomorSurat' => $surat->nomor_surat,
        'tanggalTerbit' => $surat->tanggal_terbit,
        'tanggalPengambilan' => null,
        'jamKerjaLabel' => null,
        'qrDataUri' => 'data:image/png;base64,xx',
        'desa' => PengaturanDesa::untukSurat(),
    ])->render();

    expect($html)->toContain('Bukti Pengambilan Berkas')
        ->and($html)->toContain('Desa Uji Kop')
        ->and($html)->not->toContain('menerangkan dengan sesungguhnya');
});

test('validasi gagal jika nama desa kosong', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(FormPengaturanDesa::class)
        ->set('nama_desa', '')
        ->call('simpan')
        ->assertHasErrors(['nama_desa']);
});
