<?php

use App\Livewire\Verifikasi\DetailPengajuanVerifikasi;
use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

afterEach(function () {
    Carbon::setTestNow();
});

/**
 * @return array{0: User, 1: User, 2: JenisSurat}
 */
function nomorSuratActors(string $namaSurat = 'Surat Keterangan Domisili'): array
{
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga', 'alamat' => 'Jl. Melati No. 10']);
    $jenisSurat = JenisSurat::factory()->create(['nama_surat' => $namaSurat]);

    return [$admin, $warga, $jenisSurat];
}

function extractUrut(string $nomorSurat): int
{
    preg_match('/^470\/(\d+)\//', $nomorSurat, $matches);

    return (int) ($matches[1] ?? 0);
}

test('nomor surat follows village administration format with roman month', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-07 10:00:00'));

    [$admin, $warga, $jenisSurat] = nomorSuratActors();
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'nomor_pengajuan' => 'PJ-20260807-8801',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->call('setujui');

    $surat = SuratTerbit::query()->where('pengajuan_id', $pengajuan->id)->first();

    expect($surat)->not->toBeNull()
        ->and($surat->nomor_surat)->toMatch('/^470\/\d+\/DS-WDN\/VIII\/2026$/')
        ->and($surat->nomor_surat)->toMatch(SuratTerbit::nomorSuratPattern());
});

test('nomor surat is unique sequential per year and separate from nomor_pengajuan', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-15 09:00:00'));

    [$admin, $warga, $jenisSurat] = nomorSuratActors('Surat Keterangan Usaha');

    $first = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'nomor_pengajuan' => 'PJ-20260315-8801',
    ]);
    $second = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
        'nomor_pengajuan' => 'PJ-20260315-8802',
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $first])
        ->call('setujui');

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $second])
        ->call('setujui');

    $nomor1 = (string) SuratTerbit::query()->where('pengajuan_id', $first->id)->value('nomor_surat');
    $nomor2 = (string) SuratTerbit::query()->where('pengajuan_id', $second->id)->value('nomor_surat');

    expect($nomor1)->not->toBe($nomor2)
        ->and($nomor1)->not->toBe($first->nomor_pengajuan)
        ->and($nomor2)->not->toBe($second->nomor_pengajuan)
        ->and($nomor1)->not->toStartWith('PJ-')
        ->and($nomor2)->not->toStartWith('PJ-')
        ->and(extractUrut($nomor2))->toBe(extractUrut($nomor1) + 1)
        ->and($nomor1)->toMatch('/^470\/\d+\/DS-WDN\/III\/2026$/')
        ->and($nomor2)->toMatch('/^470\/\d+\/DS-WDN\/III\/2026$/');
});

test('nomor surat sequence resets at the start of a new calendar year', function () {
    [$admin, $warga, $jenisSurat] = nomorSuratActors('Surat Keterangan Tidak Mampu');

    Carbon::setTestNow(Carbon::parse('2025-12-20 11:00:00'));

    $pengajuan2025 = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'diverifikasi_oleh' => $admin->id,
        'nomor_pengajuan' => 'PJ-20251220-8801',
    ]);

    $surat2025 = SuratTerbit::terbitkanUntuk(
        $pengajuan2025->fresh(['user', 'jenisSurat']),
        $admin->id,
    );

    expect($surat2025->nomor_surat)->toMatch('/\/XII\/2025$/');
    $urut2025 = extractUrut($surat2025->nomor_surat);

    Carbon::setTestNow(Carbon::parse('2026-01-05 11:00:00'));

    $pengajuan2026 = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'diverifikasi_oleh' => $admin->id,
        'nomor_pengajuan' => 'PJ-20260105-8802',
    ]);

    $surat2026 = SuratTerbit::terbitkanUntuk(
        $pengajuan2026->fresh(['user', 'jenisSurat']),
        $admin->id,
    );

    expect($surat2026->nomor_surat)->toMatch('/^470\/1\/DS-WDN\/I\/2026$/')
        ->and(extractUrut($surat2026->nomor_surat))->toBe(1)
        ->and(extractUrut($surat2026->nomor_surat))->not->toBe($urut2025 + 1);
});

test('nomor surat is printed on the PDF template', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-07 10:00:00'));

    [$admin, $warga, $jenisSurat] = nomorSuratActors('Surat Keterangan Domisili');
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'diverifikasi_oleh' => $admin->id,
        'keperluan' => 'Melamar pekerjaan',
    ]);

    $surat = SuratTerbit::terbitkanUntuk(
        $pengajuan->fresh(['user', 'jenisSurat']),
        $admin->id,
    );

    $html = view(SuratTerbit::resolveTemplateView($jenisSurat->nama_surat), [
        'pengajuan' => $pengajuan->fresh(['user', 'jenisSurat']),
        'pemohon' => $warga,
        'jenisSurat' => $jenisSurat,
        'nomorSurat' => $surat->nomor_surat,
        'tanggalTerbit' => now(),
        'qrDataUri' => 'data:image/png;base64,xx',
        'desa' => config('desa'),
    ])->render();

    expect($html)->toContain('Nomor: '.$surat->nomor_surat)
        ->and($html)->toContain($surat->nomor_surat);

    Storage::disk('local')->assertExists($surat->file_path);
    $pdf = Storage::disk('local')->get($surat->file_path);
    expect($pdf)->toStartWith('%PDF')
        ->and($pdf)->toContain('470')
        ->and($pdf)->toContain((string) extractUrut($surat->nomor_surat))
        ->and($pdf)->toContain('2026');
});

test('rejecting pengajuan does not allocate nomor surat', function () {
    [$admin, $warga, $jenisSurat] = nomorSuratActors('Surat Keterangan Kelahiran');
    $pengajuan = PengajuanSurat::factory()->create([
        'user_id' => $warga->id,
        'jenis_surat_id' => $jenisSurat->id,
        'status' => PengajuanSurat::STATUS_DIAJUKAN,
    ]);

    Livewire::actingAs($admin)
        ->test(DetailPengajuanVerifikasi::class, ['pengajuan' => $pengajuan])
        ->set('catatanAdmin', 'Dokumen tidak lengkap untuk penerbitan')
        ->call('tolak');

    expect(SuratTerbit::query()->where('pengajuan_id', $pengajuan->id)->exists())->toBeFalse()
        ->and(SuratTerbit::query()->count())->toBe(0);
});

test('bulanRomawi maps all months correctly for nomor format', function () {
    expect(SuratTerbit::bulanRomawi(1))->toBe('I')
        ->and(SuratTerbit::bulanRomawi(2))->toBe('II')
        ->and(SuratTerbit::bulanRomawi(3))->toBe('III')
        ->and(SuratTerbit::bulanRomawi(4))->toBe('IV')
        ->and(SuratTerbit::bulanRomawi(5))->toBe('V')
        ->and(SuratTerbit::bulanRomawi(6))->toBe('VI')
        ->and(SuratTerbit::bulanRomawi(7))->toBe('VII')
        ->and(SuratTerbit::bulanRomawi(8))->toBe('VIII')
        ->and(SuratTerbit::bulanRomawi(9))->toBe('IX')
        ->and(SuratTerbit::bulanRomawi(10))->toBe('X')
        ->and(SuratTerbit::bulanRomawi(11))->toBe('XI')
        ->and(SuratTerbit::bulanRomawi(12))->toBe('XII');
});

test('generateNomorSurat uses configurable kode klasifikasi and kode desa', function () {
    Carbon::setTestNow(Carbon::parse('2026-11-01 08:00:00'));
    config([
        'desa.kode_klasifikasi' => '331',
        'desa.kode_desa' => 'DS-ABC',
    ]);

    $nomor = null;

    DB::transaction(function () use (&$nomor): void {
        $nomor = SuratTerbit::generateNomorSurat();
    });

    expect($nomor)->toBe('331/1/DS-ABC/XI/2026')
        ->and($nomor)->toMatch(SuratTerbit::nomorSuratPattern());
});
