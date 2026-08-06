<?php

use App\Livewire\Verifikasi\ScanQrPengambilan;
use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

/**
 * Buat pengajuan siap_diambil + surat_terbit dengan QR valid (prasyarat scan US-7.4).
 *
 * @return array{pengajuan: PengajuanSurat, surat: SuratTerbit, warga: User, admin: User}
 */
function buatSuratSiapDiambil(?string $qrToken = null): array
{
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->siapDiambil()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'qr_token' => $qrToken ?? Str::random(64),
        'qr_status' => SuratTerbit::QR_STATUS_VALID,
        'qr_digunakan_at' => null,
        'qr_digunakan_oleh' => null,
    ]);

    return compact('pengajuan', 'surat', 'warga', 'admin');
}

test('admin can open scan qr pengambilan page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('scan-qr-pengambilan.index'))
        ->assertOk()
        ->assertSee('Scan QR Pengambilan');
});

test('warga cannot open scan qr pengambilan page', function () {
    $warga = User::factory()->create(['role' => 'warga']);

    $this->actingAs($warga)
        ->get(route('scan-qr-pengambilan.index'))
        ->assertForbidden();
});

test('successful first scan invalidates qr marks selesai and notifies warga', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'warga' => $warga, 'admin' => $admin] = buatSuratSiapDiambil();

    Livewire::actingAs($admin)
        ->test(ScanQrPengambilan::class)
        ->set('qrToken', $surat->qr_token)
        ->call('prosesScan')
        ->assertSet('hasilSukses', true)
        ->assertSee('Pengambilan berhasil');

    $surat->refresh();
    $pengajuan->refresh();

    expect($surat->qr_status)->toBe(SuratTerbit::QR_STATUS_INVALID)
        ->and($surat->qr_digunakan_at)->not->toBeNull()
        ->and($surat->qr_digunakan_oleh)->toBe($admin->id)
        ->and($pengajuan->status)->toBe(PengajuanSurat::STATUS_SELESAI);

    $notif = Notifikasi::query()
        ->where('user_id', $warga->id)
        ->where('pengajuan_id', $pengajuan->id)
        ->latest('id')
        ->first();

    expect($notif)->not->toBeNull()
        ->and($notif->pesan)->toContain('selesai');
});

test('rescan of invalid qr is always rejected for any admin', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin1] = buatSuratSiapDiambil();
    $admin2 = User::factory()->admin()->create();

    Livewire::actingAs($admin1)
        ->test(ScanQrPengambilan::class)
        ->set('qrToken', $surat->qr_token)
        ->call('prosesScan')
        ->assertSet('hasilSukses', true);

    Livewire::actingAs($admin2)
        ->test(ScanQrPengambilan::class)
        ->set('qrToken', $surat->qr_token)
        ->call('prosesScan')
        ->assertSet('hasilSukses', false)
        ->assertSee('QR sudah digunakan / tidak valid');

    expect($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_SELESAI)
        ->and($surat->fresh()->qr_digunakan_oleh)->toBe($admin1->id);
});

test('unknown token is rejected', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(ScanQrPengambilan::class)
        ->set('qrToken', Str::random(64))
        ->call('prosesScan')
        ->assertSet('hasilSukses', false)
        ->assertSee('Token QR tidak dikenal');
});

test('scan rejected when pengajuan not siap_diambil', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'diverifikasi_oleh' => $admin->id,
    ]);
    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'qr_status' => SuratTerbit::QR_STATUS_VALID,
    ]);

    Livewire::actingAs($admin)
        ->test(ScanQrPengambilan::class)
        ->set('qrToken', $surat->qr_token)
        ->call('prosesScan')
        ->assertSet('hasilSukses', false)
        ->assertSee('belum siap diambil');

    expect($surat->fresh()->qr_status)->toBe(SuratTerbit::QR_STATUS_VALID)
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_DIPROSES);
});

test('terbitkanUntuk does not regenerate qr token on second call', function () {
    $admin = User::factory()->admin()->create();
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'diverifikasi_oleh' => $admin->id,
    ]);

    $first = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);
    $tokenSebelum = $first->qr_token;

    $second = SuratTerbit::terbitkanUntuk($pengajuan->fresh(['user', 'jenisSurat']), $admin->id);

    expect($second->id)->toBe($first->id)
        ->and($second->qr_token)->toBe($tokenSebelum)
        ->and($second->qr_status)->toBe(SuratTerbit::QR_STATUS_VALID);
});

test('conditional update prevents double success under concurrent scan', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin1] = buatSuratSiapDiambil();
    $admin2 = User::factory()->admin()->create();
    $token = $surat->qr_token;

    $hasil1 = null;
    $hasil2 = null;

    // Simulasikan dua pemenang race: hanya satu conditional update boleh sukses.
    DB::transaction(function () use ($token, $admin1, &$hasil1): void {
        $hasil1 = SuratTerbit::scanUntukPengambilan($token, $admin1->id);
    });

    $hasil2 = SuratTerbit::scanUntukPengambilan($token, $admin2->id);

    expect($hasil1['ok'])->toBeTrue()
        ->and($hasil2['ok'])->toBeFalse()
        ->and($hasil2['message'])->toContain('QR sudah digunakan / tidak valid')
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_SELESAI)
        ->and(SuratTerbit::query()->where('qr_token', $token)->value('qr_status'))
        ->toBe(SuratTerbit::QR_STATUS_INVALID);
});

test('qr has no ttl — valid token remains scannable after time passes while siap_diambil', function () {
    ['pengajuan' => $pengajuan, 'surat' => $surat, 'admin' => $admin] = buatSuratSiapDiambil();

    // Mundurkan created_at jauh ke masa lalu — tetap valid sampai scan sukses.
    $surat->forceFill(['created_at' => now()->subYears(2), 'updated_at' => now()->subYears(2)])->save();

    $hasil = SuratTerbit::scanUntukPengambilan($surat->qr_token, $admin->id);

    expect($hasil['ok'])->toBeTrue()
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_SELESAI);
});
