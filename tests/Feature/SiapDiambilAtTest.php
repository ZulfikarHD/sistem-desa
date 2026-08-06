<?php

use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    Storage::fake('local');
});

test('tandaiSiapDiambil mencatat siap_diambil_at timestamp', function () {
    $admin = User::factory()->admin()->create();
    $warga = User::factory()->create(['role' => 'warga']);
    $pengajuan = PengajuanSurat::factory()->diproses()->create([
        'user_id' => $warga->id,
        'diverifikasi_oleh' => $admin->id,
    ]);
    $surat = SuratTerbit::factory()->create([
        'pengajuan_id' => $pengajuan->id,
        'diterbitkan_oleh' => $admin->id,
        'siap_diambil_at' => null,
        'qr_token' => Str::random(64),
    ]);
    Storage::disk('local')->put($surat->file_path, '%PDF-1.4 test');

    $tanggal = Carbon::parse(now('Asia/Jakarta')->toDateString(), 'Asia/Jakarta');
    for ($i = 0; $i < 60; $i++) {
        $candidate = Carbon::parse($tanggal->copy()->addDays($i)->toDateString(), 'Asia/Jakarta');
        if (SuratTerbit::validasiTanggalPengambilan($candidate)['ok']) {
            $tanggal = $candidate;
            break;
        }
    }

    $hasil = SuratTerbit::tandaiSiapDiambil($pengajuan, $tanggal);

    expect($hasil['ok'])->toBeTrue()
        ->and($surat->fresh()->siap_diambil_at)->not->toBeNull()
        ->and($pengajuan->fresh()->status)->toBe(PengajuanSurat::STATUS_SIAP_DIAMBIL);
});

test('kolom siap_diambil_at nullable pada factory default', function () {
    $surat = SuratTerbit::factory()->create();

    expect($surat->siap_diambil_at)->toBeNull();
});
