<?php

use App\Livewire\Pengajuan\FormPengajuanSurat;
use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('warga cannot resubmit another wargas ditolak pengajuan', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $otherWarga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->ditolak()->create([
        'user_id' => $otherWarga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $this->actingAs($warga)
        ->get(route('pengajuan-surat.resubmit', $pengajuan))
        ->assertForbidden();
});

test('warga cannot resubmit pengajuan that is not ditolak', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $this->actingAs($warga)
        ->get(route('pengajuan-surat.resubmit', $pengajuan))
        ->assertNotFound();
});

test('resubmit form is prefilled from ditolak pengajuan and shows catatan admin', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Keterangan Usaha',
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);

    $pengajuan = PengajuanSurat::factory()->ditolak('Unggah ulang KTP yang lebih jelas')->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Keperluan perpanjangan izin usaha',
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0400',
    ]);

    Livewire::actingAs($warga)
        ->test(FormPengajuanSurat::class, ['pengajuan' => $pengajuan])
        ->assertSet('jenis_surat_id', $jenisSurat->id)
        ->assertSet('keperluan', 'Keperluan perpanjangan izin usaha')
        ->assertSet('catatanAdminReferensi', 'Unggah ulang KTP yang lebih jelas')
        ->assertSet('nomorPengajuanSebelumnya', 'PJ-'.now()->format('Ymd').'-0400')
        ->assertSee('Catatan Admin dari Pengajuan Sebelumnya')
        ->assertSee('Unggah ulang KTP yang lebih jelas');
});

test('resubmit creates new pengajuan record with new nomor and status diajukan', function () {
    Storage::fake();

    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);

    $pengajuanLama = PengajuanSurat::factory()->ditolak('Perbaiki dokumen')->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Keperluan lama',
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-0500',
    ]);

    $ktpFile = UploadedFile::fake()->image('ktp-baru.jpg');

    Livewire::actingAs($warga)
        ->test(FormPengajuanSurat::class, ['pengajuan' => $pengajuanLama])
        ->set('dokumenKtp', $ktpFile)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedNomor', fn (?string $nomor) => $nomor !== null && $nomor !== 'PJ-'.now()->format('Ymd').'-0500');

    expect(PengajuanSurat::query()->count())->toBe(2);

    $pengajuanBaru = PengajuanSurat::query()
        ->where('id', '!=', $pengajuanLama->id)
        ->first();

    expect($pengajuanBaru)->not->toBeNull();
    expect($pengajuanBaru->status)->toBe(PengajuanSurat::STATUS_DIAJUKAN);
    expect($pengajuanBaru->nomor_pengajuan)->not->toBe($pengajuanLama->nomor_pengajuan);
    expect($pengajuanBaru->keperluan)->toBe('Keperluan lama');

    $pengajuanLama->refresh();
    expect($pengajuanLama->status)->toBe(PengajuanSurat::STATUS_DITOLAK);

    $this->assertDatabaseHas('dokumen_persyaratan', [
        'pengajuan_id' => $pengajuanBaru->id,
        'jenis_dokumen' => DokumenPersyaratan::JENIS_KTP,
    ]);
});

test('resubmit still requires mandatory dokumen upload', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);

    $pengajuan = PengajuanSurat::factory()->ditolak()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    Livewire::actingAs($warga)
        ->test(FormPengajuanSurat::class, ['pengajuan' => $pengajuan])
        ->call('submit')
        ->assertHasErrors(['dokumenKtp' => 'required']);

    expect(PengajuanSurat::query()->count())->toBe(1);
});
