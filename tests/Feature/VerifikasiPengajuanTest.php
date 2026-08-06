<?php

use App\Livewire\Verifikasi\DaftarPengajuanVerifikasi;
use App\Livewire\Verifikasi\DetailPengajuanVerifikasi;
use App\Models\DokumenPersyaratan;
use App\Models\JenisSurat;
use App\Models\LogVerifikasi;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected to login from verifikasi pengajuan page', function () {
    $this->get(route('verifikasi.index'))
        ->assertRedirect(route('login'));
});

test('warga cannot visit verifikasi pengajuan admin page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('verifikasi.index'))
        ->assertForbidden();
});

test('admin can visit verifikasi pengajuan list page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('verifikasi.index'))
        ->assertOk()
        ->assertSeeLivewire(DaftarPengajuanVerifikasi::class);
});

test('verifikasi list defaults to diajukan status filter', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    $diajukan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-1001',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-1002',
    ]);

    Livewire::actingAs($admin)
        ->test(DaftarPengajuanVerifikasi::class)
        ->assertSet('statusFilter', PengajuanSurat::STATUS_DIAJUKAN)
        ->assertSee($diajukan->nomor_pengajuan)
        ->assertSee($warga->name)
        ->assertSee($jenisSurat->nama_surat)
        ->assertDontSee('PJ-'.now()->format('Ymd').'-1002');
});

test('verifikasi list can filter by other statuses', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-1100',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    $disetujui = PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-1101',
    ]);

    Livewire::actingAs($admin)
        ->test(DaftarPengajuanVerifikasi::class)
        ->set('statusFilter', PengajuanSurat::STATUS_DISETUJUI)
        ->assertSee($disetujui->nomor_pengajuan)
        ->assertDontSee('PJ-'.now()->format('Ymd').'-1100');
});

test('admin can open detail pengajuan from list', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga', 'name' => 'Budi Warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Domisili E2E']);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Keperluan verifikasi admin',
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-1200',
    ]);

    Livewire::actingAs($admin)
        ->test(DaftarPengajuanVerifikasi::class)
        ->call('openDetail', $pengajuan->id)
        ->assertRedirect(route('verifikasi.show', $pengajuan));
});

test('warga cannot visit verifikasi detail page', function () {
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create(['user_id' => $warga->id]);

    $this->actingAs($warga)
        ->get(route('verifikasi.show', $pengajuan))
        ->assertForbidden();
});

test('admin detail page shows pengajuan data keperluan and action buttons', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga', 'name' => 'Siti Warga']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Usaha']);
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'keperluan' => 'Membuka rekening bank',
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-1300',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->assertSee('Detail Pengajuan')
        ->assertSee($pengajuan->nomor_pengajuan)
        ->assertSee('Siti Warga')
        ->assertSee('Surat Usaha')
        ->assertSee('Membuka rekening bank')
        ->assertSeeHtml('verifikasi-detail-setujui-button')
        ->assertSeeHtml('verifikasi-detail-tolak-button');
});

test('admin detail page previews image dokumen', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $path = 'pengajuan-dokumen/'.$pengajuan->id.'/ktp_test.jpg';
    Storage::disk('local')->put($path, UploadedFile::fake()->image('ktp_test.jpg')->getContent());

    $dokumen = DokumenPersyaratan::factory()->ktp()->create([
        'pengajuan_id' => $pengajuan->id,
        'file_path' => $path,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->assertSeeHtml('verifikasi-detail-dokumen-preview-'.$dokumen->id)
        ->assertSeeHtml(route('verifikasi.dokumen.show', $dokumen));
});

test('admin detail page shows download fallback when file missing', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $dokumen = DokumenPersyaratan::factory()->ktp()->create([
        'pengajuan_id' => $pengajuan->id,
        'file_path' => 'pengajuan-dokumen/'.$pengajuan->id.'/missing.jpg',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->assertSeeHtml('verifikasi-detail-dokumen-fallback-'.$dokumen->id)
        ->assertSeeHtml('verifikasi-detail-dokumen-download-'.$dokumen->id);
});

test('admin can stream dokumen for preview', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $path = 'pengajuan-dokumen/'.$pengajuan->id.'/ktp_preview.jpg';
    Storage::disk('local')->put($path, UploadedFile::fake()->image('ktp_preview.jpg')->getContent());

    $dokumen = DokumenPersyaratan::factory()->ktp()->create([
        'pengajuan_id' => $pengajuan->id,
        'file_path' => $path,
    ]);

    $this->actingAs($admin)
        ->get(route('verifikasi.dokumen.show', $dokumen))
        ->assertOk();
});

test('warga cannot access dokumen preview route', function () {
    Storage::fake('local');

    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
    ]);

    $path = 'pengajuan-dokumen/'.$pengajuan->id.'/ktp_forbidden.jpg';
    Storage::disk('local')->put($path, UploadedFile::fake()->image('ktp_forbidden.jpg')->getContent());

    $dokumen = DokumenPersyaratan::factory()->ktp()->create([
        'pengajuan_id' => $pengajuan->id,
        'file_path' => $path,
    ]);

    $this->actingAs($warga)
        ->get(route('verifikasi.dokumen.show', $dokumen))
        ->assertForbidden();
});

test('dokumen preview returns 404 when file missing', function () {
    Storage::fake('local');

    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->create(['user_id' => $warga->id]);

    $dokumen = DokumenPersyaratan::factory()->ktp()->create([
        'pengajuan_id' => $pengajuan->id,
        'file_path' => 'pengajuan-dokumen/'.$pengajuan->id.'/not-found.jpg',
    ]);

    $this->actingAs($admin)
        ->get(route('verifikasi.dokumen.show', $dokumen))
        ->assertNotFound();
});

test('opening detail keeps diajukan status unchanged', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->assertSet('pengajuan.status', PengajuanSurat::STATUS_DIAJUKAN);

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIAJUKAN);
    expect(Notifikasi::query()->count())->toBe(0);
});

test('reopening detail does not change diproses status', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'diverifikasi_oleh' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->assertSet('pengajuan.status', PengajuanSurat::STATUS_DIPROSES);

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIPROSES);
});

test('admin can approve diajukan pengajuan directly to diproses', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->call('setujui')
        ->assertRedirect(route('verifikasi.index'));

    $pengajuan->refresh();

    expect($pengajuan->status)->toBe(PengajuanSurat::STATUS_DIPROSES)
        ->and($pengajuan->diverifikasi_oleh)->toBe($admin->id)
        ->and($pengajuan->catatan_admin)->toBeNull();

    $this->assertDatabaseHas('log_verifikasi', [
        'pengajuan_id' => $pengajuan->id,
        'admin_id' => $admin->id,
        'aksi' => LogVerifikasi::AKSI_SETUJUI,
        'keterangan' => null,
    ]);

    // Tidak boleh ada jejak status perantara disetujui pada alur baru
    expect(Notifikasi::query()->where('pengajuan_id', $pengajuan->id)->count())->toBe(1);
});

test('admin verifikasi list page shows renamed heading Daftar Pengajuan Surat', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('verifikasi.index'))
        ->assertOk()
        ->assertSee('Daftar Pengajuan Surat')
        ->assertDontSee('Verifikasi Pengajuan', false);
});

test('historical disetujui status displays as Diproses label', function () {
    expect(PengajuanSurat::statusLabel(PengajuanSurat::STATUS_DISETUJUI))->toBe('Diproses')
        ->and(PengajuanSurat::statusLabel(PengajuanSurat::STATUS_DIPROSES))->toBe('Diproses')
        ->and(PengajuanSurat::statusOptions()[PengajuanSurat::STATUS_DISETUJUI])->toBe('Disetujui (historis)');
});

test('admin can reject diajukan pengajuan with required catatan without entering diproses', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    $alasan = 'Dokumen KTP tidak terbaca dengan jelas';

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->set('catatanAdmin', $alasan)
        ->call('tolak')
        ->assertRedirect(route('verifikasi.index'));

    $pengajuan->refresh();

    expect($pengajuan->status)->toBe(PengajuanSurat::STATUS_DITOLAK)
        ->and($pengajuan->diverifikasi_oleh)->toBe($admin->id)
        ->and($pengajuan->catatan_admin)->toBe($alasan);

    $this->assertDatabaseHas('log_verifikasi', [
        'pengajuan_id' => $pengajuan->id,
        'admin_id' => $admin->id,
        'aksi' => LogVerifikasi::AKSI_TOLAK,
        'keterangan' => $alasan,
    ]);
});

test('reject requires catatan admin', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->set('catatanAdmin', '')
        ->call('tolak')
        ->assertHasErrors(['catatanAdmin' => 'required']);

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIAJUKAN);
    $this->assertDatabaseCount('log_verifikasi', 0);
});

test('approved pengajuan disappears from default diajukan list', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-2001',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->call('setujui');

    Livewire::actingAs($admin)
        ->test(DaftarPengajuanVerifikasi::class)
        ->assertDontSee('PJ-'.now()->format('Ymd').'-2001');
});

test('action buttons hidden for already decided pengajuan', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'diverifikasi_oleh' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->assertDontSeeHtml('verifikasi-detail-setujui-button')
        ->assertDontSeeHtml('verifikasi-detail-tolak-button');
});

test('verifikasi list status options include siap_diambil and selesai', function () {
    $admin = User::factory()->admin()->create();

    $component = Livewire::actingAs($admin)
        ->test(DaftarPengajuanVerifikasi::class);

    $options = $component->instance()->statusOptions();

    expect($options)->toHaveKey(PengajuanSurat::STATUS_SIAP_DIAMBIL)
        ->and($options)->toHaveKey(PengajuanSurat::STATUS_SELESAI)
        ->and($options[PengajuanSurat::STATUS_SIAP_DIAMBIL])->toBe('Siap Diambil')
        ->and($options[PengajuanSurat::STATUS_SELESAI])->toBe('Selesai');
});
