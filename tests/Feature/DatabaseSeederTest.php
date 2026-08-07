<?php

use App\Models\JenisSurat;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\JenisSuratSeeder;
use Illuminate\Support\Facades\Hash;

test('database seeder membuat akun admin dan warga baku', function () {
    $this->seed(DatabaseSeeder::class);

    $admin = User::query()->where('email', 'admin@desa.test')->first();
    $warga = User::query()->where('email', 'warga@desa.test')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe('admin')
        ->and($admin->nik)->toBe('3201010101000001')
        ->and(Hash::check('password', $admin->password))->toBeTrue();

    expect($warga)->not->toBeNull()
        ->and($warga->role)->toBe('warga')
        ->and($warga->nik)->toBe('3201010101000002')
        ->and(Hash::check('password', $warga->password))->toBeTrue();

    // 1 admin + 1 warga baku + 5 warga factory
    expect(User::query()->count())->toBe(7)
        ->and(User::query()->where('role', 'admin')->count())->toBe(1)
        ->and(User::query()->where('role', 'warga')->count())->toBe(6);
});

test('database seeder mengisi jenis surat keterangan desa beserta persyaratan', function () {
    $this->seed(DatabaseSeeder::class);

    expect(JenisSurat::query()->count())->toBe(14);

    $domisili = JenisSurat::query()->where('nama_surat', 'Surat Keterangan Domisili')->first();
    $sktm = JenisSurat::query()->where('nama_surat', 'Surat Keterangan Tidak Mampu (SKTM)')->first();

    expect($domisili)->not->toBeNull()
        ->and($domisili->persyaratan_dokumen)->toContain('KTP')
        ->and($domisili->persyaratan_dokumen)->toContain('Kartu Keluarga');

    expect($sktm)->not->toBeNull()
        ->and($sktm->persyaratan_dokumen)->toContain('pernyataan tidak mampu');
});

test('jenis surat seeder idempotent saat dijalankan ulang', function () {
    $this->seed(JenisSuratSeeder::class);
    $this->seed(JenisSuratSeeder::class);

    expect(JenisSurat::query()->count())->toBe(14)
        ->and(JenisSurat::query()->where('nama_surat', 'Surat Keterangan Usaha')->count())->toBe(1);
});
