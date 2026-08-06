<?php

namespace Database\Factories;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PengajuanSurat>
 */
class PengajuanSuratFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggal = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(),
            'jenis_surat_id' => JenisSurat::factory(),
            'nomor_pengajuan' => 'PJ-'.now()->format('Ymd').'-'.Str::padLeft((string) fake()->unique()->numberBetween(1, 9999), 4, '0'),
            'keperluan' => fake()->sentence(),
            'status' => PengajuanSurat::STATUS_DIAJUKAN,
            'catatan_admin' => null,
            'diverifikasi_oleh' => null,
            'tanggal_pengajuan' => $tanggal->format('Y-m-d'),
        ];
    }
}
