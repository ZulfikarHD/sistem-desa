<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun uji baku untuk role admin & warga (Phase 01).
     */
    public function run(): void
    {
        // Admin/petugas desa — untuk uji dashboard admin & middleware
        User::factory()->admin()->create([
            'nik' => '3201010101000001',
            'name' => 'Admin Desa',
            'email' => 'admin@desa.test',
            'no_telepon' => '081111111111',
            'alamat' => 'Kantor Desa Contoh, Kecamatan Contoh',
            'password' => 'password',
        ]);

        // Warga utama — untuk uji registrasi/login/dashboard warga
        User::factory()->create([
            'nik' => '3201010101000002',
            'name' => 'Warga Contoh',
            'email' => 'warga@desa.test',
            'no_telepon' => '081222222222',
            'alamat' => 'Jl. Merdeka No. 1, Desa Contoh',
            'password' => 'password',
        ]);

        // Beberapa warga tambahan untuk data uji list/pagination nanti
        User::factory()->count(5)->create();
    }
}
