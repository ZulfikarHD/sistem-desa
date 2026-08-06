<?php

use App\Livewire\Rekap\RekapPengajuan;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login from rekap pengajuan page', function () {
    $this->get(route('rekap-pengajuan.index'))
        ->assertRedirect(route('login'));
});

test('warga cannot visit rekap pengajuan admin page', function () {
    $user = User::factory()->create(['role' => 'warga']);

    $this->actingAs($user)
        ->get(route('rekap-pengajuan.index'))
        ->assertForbidden();
});

test('admin can visit rekap pengajuan page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('rekap-pengajuan.index'))
        ->assertOk()
        ->assertSeeLivewire(RekapPengajuan::class);
});

test('rekap page shows table columns and summary counts', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Rekap']);
    $warga = User::factory()->create(['role' => 'warga', 'name' => 'Warga Rekap']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => 'Surat Domisili Rekap']);

    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6001',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'tanggal_pengajuan' => now()->toDateString(),
    ]);

    $disetujui = PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6002',
        'diverifikasi_oleh' => $admin->id,
        'tanggal_pengajuan' => now()->toDateString(),
    ]);

    Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->assertSee('Rekap Pengajuan')
        ->assertSee($pengajuan->nomor_pengajuan)
        ->assertSee($disetujui->nomor_pengajuan)
        ->assertSee('Warga Rekap')
        ->assertSee('Surat Domisili Rekap')
        ->assertSee('Admin Rekap')
        ->assertSeeHtml('data-test="rekap-ringkasan-total"')
        ->assertSeeHtml('data-test="rekap-ringkasan-diajukan"')
        ->assertSeeHtml('data-test="rekap-ringkasan-diproses"')
        ->assertSeeHtml('data-test="rekap-ringkasan-disetujui"')
        ->assertSeeHtml('data-test="rekap-ringkasan-ditolak"');
});

test('rekap can filter by status jenis surat and date range', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisA = JenisSurat::factory()->create(['nama_surat' => 'Jenis A Rekap']);
    $jenisB = JenisSurat::factory()->create(['nama_surat' => 'Jenis B Rekap']);

    $match = PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisA->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6100',
        'tanggal_pengajuan' => '2026-07-15',
        'diverifikasi_oleh' => $admin->id,
    ]);

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisB->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6101',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'tanggal_pengajuan' => '2026-07-15',
    ]);

    PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisA->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6102',
        'tanggal_pengajuan' => '2026-06-01',
        'diverifikasi_oleh' => $admin->id,
    ]);

    Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->set('jenisSuratFilter', (string) $jenisA->id)
        ->set('statusFilter', PengajuanSurat::STATUS_DISETUJUI)
        ->set('tanggalDari', '2026-07-01')
        ->set('tanggalSampai', '2026-07-31')
        ->assertSee($match->nomor_pengajuan)
        ->assertDontSee('PJ-'.now()->format('Ymd').'-6101')
        ->assertDontSee('PJ-'.now()->format('Ymd').'-6102');
});

test('ringkasan ignores status filter but respects jenis and date filters', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisA = JenisSurat::factory()->create();
    $jenisB = JenisSurat::factory()->create();

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisA->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6201',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'tanggal_pengajuan' => '2026-08-01',
    ]);

    PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisA->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6202',
        'tanggal_pengajuan' => '2026-08-02',
        'diverifikasi_oleh' => $admin->id,
    ]);

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisB->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6203',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'tanggal_pengajuan' => '2026-08-01',
    ]);

    $component = Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->set('jenisSuratFilter', (string) $jenisA->id)
        ->set('statusFilter', PengajuanSurat::STATUS_DISETUJUI)
        ->set('tanggalDari', '2026-08-01')
        ->set('tanggalSampai', '2026-08-31');

    // Tabel hanya tampilkan disetujui
    $component
        ->assertSee('PJ-'.now()->format('Ymd').'-6202')
        ->assertDontSee('PJ-'.now()->format('Ymd').'-6201')
        ->assertDontSee('PJ-'.now()->format('Ymd').'-6203');

    // Ringkasan: jenis A + tanggal Agustus = 2 (abaikan status filter)
    $ringkasan = $component->viewData('ringkasan');
    expect($ringkasan['total'])->toBe(2)
        ->and($ringkasan['diajukan'])->toBe(1)
        ->and($ringkasan['disetujui'])->toBe(1)
        ->and($ringkasan['diproses'])->toBe(0)
        ->and($ringkasan['ditolak'])->toBe(0);
});

test('invalid date range shows validation error and empty table', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $jenisSurat = JenisSurat::factory()->create();

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6300',
        'tanggal_pengajuan' => '2026-08-01',
    ]);

    Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->set('tanggalDari', '2026-08-31')
        ->set('tanggalSampai', '2026-08-01')
        ->assertHasErrors(['tanggalSampai'])
        ->assertDontSee('PJ-'.now()->format('Ymd').'-6300');
});

test('export csv follows active filters and includes utf8 bom', function () {
    $admin = User::factory()->admin()->create(['name' => 'Admin Export']);
    $warga = User::factory()->create(['role' => 'warga', 'name' => 'Warga Export']);
    $jenisA = JenisSurat::factory()->create(['nama_surat' => 'Jenis Export A']);
    $jenisB = JenisSurat::factory()->create(['nama_surat' => 'Jenis Export B']);

    $included = PengajuanSurat::factory()->disetujui()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisA->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6400',
        'tanggal_pengajuan' => '2026-08-05',
        'diverifikasi_oleh' => $admin->id,
    ]);

    PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisB->id,
        'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-6401',
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'tanggal_pengajuan' => '2026-08-05',
    ]);

    $response = Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->set('jenisSuratFilter', (string) $jenisA->id)
        ->set('statusFilter', PengajuanSurat::STATUS_DISETUJUI)
        ->call('exportCsv')
        ->assertFileDownloaded();

    $content = $response->effects['download']['content'] ?? null;

    // Livewire may base64-encode download content
    if (is_string($content) && ! str_contains($content, 'Nomor Pengajuan')) {
        $decoded = base64_decode($content, true);
        $content = $decoded !== false ? $decoded : $content;
    }

    expect($content)->toBeString()
        ->and(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($content)->toContain('Nomor Pengajuan')
        ->and($content)->toContain('Nama Warga')
        ->and($content)->toContain('Jenis Surat')
        ->and($content)->toContain('Tanggal Pengajuan')
        ->and($content)->toContain('Status')
        ->and($content)->toContain('Admin Verifikator')
        ->and($content)->toContain($included->nomor_pengajuan)
        ->and($content)->toContain('Warga Export')
        ->and($content)->toContain('Jenis Export A')
        ->and($content)->toContain('Admin Export')
        ->and($content)->not->toContain('PJ-'.now()->format('Ymd').'-6401');
});

test('reset filters clears all filter state', function () {
    $admin = User::factory()->admin()->create();
    $jenisSurat = JenisSurat::factory()->create();

    Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->set('jenisSuratFilter', (string) $jenisSurat->id)
        ->set('statusFilter', PengajuanSurat::STATUS_DITOLAK)
        ->set('tanggalDari', '2026-01-01')
        ->set('tanggalSampai', '2026-12-31')
        ->call('resetFilters')
        ->assertSet('jenisSuratFilter', '')
        ->assertSet('statusFilter', '')
        ->assertSet('tanggalDari', '')
        ->assertSet('tanggalSampai', '');
});

test('empty filter result shows empty state', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(RekapPengajuan::class)
        ->set('statusFilter', PengajuanSurat::STATUS_DITOLAK)
        ->set('tanggalDari', '2099-01-01')
        ->set('tanggalSampai', '2099-12-31')
        ->assertSeeHtml('data-test="rekap-pengajuan-empty"');
});
