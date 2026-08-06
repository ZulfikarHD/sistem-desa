<?php

use App\Livewire\Pengajuan\FormPengajuanSurat;
use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        'persyaratan_dokumen' => '- Surat pengantar RT/RW',
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
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Surat pengantar RT/RW',
    ]);

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

test('nomor pengajuan increments after four digit sequence overflow', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Surat pengantar RT/RW',
    ]);
    $prefix = 'PJ-'.now()->format('Ymd').'-';

    PengajuanSurat::factory()->create([
        'user_id' => $user->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => $prefix.'9999',
        'tanggal_pengajuan' => now()->toDateString(),
    ]);

    PengajuanSurat::factory()->create([
        'user_id' => $user->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => $prefix.'10000',
        'tanggal_pengajuan' => now()->toDateString(),
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Pengajuan setelah overflow')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedNomor', $prefix.'10001');
});

test('create another resets success state', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Surat pengantar RT/RW',
    ]);

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

test('dokumen upload section appears for jenis surat with KTP and KK persyaratan', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi KK",
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->assertSee('Unggah Dokumen Persyaratan')
        ->assertSee('Fotokopi KTP')
        ->assertSee('Fotokopi Kartu Keluarga');
});

test('dokumen upload section detects kartu keluarga text as KK requirement', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi Kartu Keluarga",
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->assertSee('Fotokopi Kartu Keluarga');
});

test('warga can upload KTP and KK and files are stored on submit', function () {
    Storage::fake();

    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi KK",
    ]);
    $ktpFile = UploadedFile::fake()->image('ktp.jpg');
    $kkFile = UploadedFile::fake()->image('kk.png');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan dengan dokumen lengkap')
        ->set('dokumenKtp', $ktpFile)
        ->set('dokumenKk', $kkFile)
        ->call('submit')
        ->assertHasNoErrors();

    $pengajuan = PengajuanSurat::query()->first();
    expect($pengajuan)->not->toBeNull();

    $this->assertDatabaseHas('dokumen_persyaratan', [
        'pengajuan_id' => $pengajuan->id,
        'jenis_dokumen' => DokumenPersyaratan::JENIS_KTP,
    ]);
    $this->assertDatabaseHas('dokumen_persyaratan', [
        'pengajuan_id' => $pengajuan->id,
        'jenis_dokumen' => DokumenPersyaratan::JENIS_KK,
    ]);

    $ktpRecord = DokumenPersyaratan::query()
        ->where('pengajuan_id', $pengajuan->id)
        ->where('jenis_dokumen', DokumenPersyaratan::JENIS_KTP)
        ->first();

    Storage::assertExists($ktpRecord->file_path);
});

test('upload rejects invalid file format', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);
    $invalidFile = UploadedFile::fake()->create('dokumen.txt', 100, 'text/plain');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan valid')
        ->set('dokumenKtp', $invalidFile)
        ->call('submit')
        ->assertHasErrors(['dokumenKtp']);
});

test('upload rejects file larger than 2MB', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);
    $largeFile = UploadedFile::fake()->create('ktp.jpg', FormPengajuanSurat::MAX_FILE_SIZE_KB + 1, 'image/jpeg');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan valid')
        ->set('dokumenKtp', $largeFile)
        ->call('submit')
        ->assertHasErrors(['dokumenKtp']);
});

test('changing jenis surat resets uploaded dokumen', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisKtp = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Hanya KTP',
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);
    $jenisKk = JenisSurat::factory()->create([
        'nama_surat' => 'Surat Hanya KK',
        'persyaratan_dokumen' => '- Fotokopi KK',
    ]);
    $ktpFile = UploadedFile::fake()->image('ktp.jpg');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisKtp->id)
        ->set('dokumenKtp', $ktpFile)
        ->assertSet('dokumenKtp', fn ($file) => $file !== null)
        ->set('jenis_surat_id', $jenisKk->id)
        ->assertSet('dokumenKtp', null)
        ->assertSet('dokumenKk', null);
});

test('remove dokumen ktp clears preview state', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);
    $ktpFile = UploadedFile::fake()->image('ktp.jpg');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('dokumenKtp', $ktpFile)
        ->call('removeDokumenKtp')
        ->assertSet('dokumenKtp', null);
});

test('submit fails when required KTP is not uploaded', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KTP',
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan tanpa KTP')
        ->call('submit')
        ->assertHasErrors(['dokumenKtp' => 'required']);

    expect(PengajuanSurat::query()->count())->toBe(0);
});

test('submit fails when required KK is not uploaded', function () {
    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => '- Fotokopi KK',
    ]);

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan tanpa KK')
        ->call('submit')
        ->assertHasErrors(['dokumenKk' => 'required']);

    expect(PengajuanSurat::query()->count())->toBe(0);
});

test('submit fails when only one of two required dokumen is uploaded', function () {
    Storage::fake();

    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi KK",
    ]);
    $ktpFile = UploadedFile::fake()->image('ktp.jpg');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan hanya KTP')
        ->set('dokumenKtp', $ktpFile)
        ->call('submit')
        ->assertHasErrors(['dokumenKk' => 'required']);

    expect(PengajuanSurat::query()->count())->toBe(0);
});

test('submit succeeds only when all required dokumen are uploaded', function () {
    Storage::fake();

    $user = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create([
        'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi KK",
    ]);
    $ktpFile = UploadedFile::fake()->image('ktp.jpg');
    $kkFile = UploadedFile::fake()->image('kk.png');

    Livewire::actingAs($user)
        ->test(FormPengajuanSurat::class)
        ->set('jenis_surat_id', $jenisSurat->id)
        ->set('keperluan', 'Keperluan lengkap')
        ->set('dokumenKtp', $ktpFile)
        ->set('dokumenKk', $kkFile)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submittedNomor', fn (?string $nomor) => $nomor !== null);

    $pengajuan = PengajuanSurat::query()->first();
    expect($pengajuan?->status)->toBe(PengajuanSurat::STATUS_DIAJUKAN);
    expect(DokumenPersyaratan::query()->where('pengajuan_id', $pengajuan?->id)->count())->toBe(2);
});
