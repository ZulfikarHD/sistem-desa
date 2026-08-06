<?php

namespace Database\Factories;

use App\Models\PengajuanSurat;
use App\Models\SuratTerbit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SuratTerbit>
 */
class SuratTerbitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tanggal = now();
        $bulanRomawi = SuratTerbit::bulanRomawi((int) $tanggal->format('n'));

        return [
            'pengajuan_id' => PengajuanSurat::factory()->diproses(),
            'nomor_surat' => '470/'.fake()->unique()->numberBetween(1, 9999).'/DS-WDN/'.$bulanRomawi.'/'.$tanggal->format('Y'),
            'file_path' => 'surat-terbit/'.fake()->numberBetween(1, 9999).'/surat.pdf',
            'tanggal_terbit' => $tanggal->toDateString(),
            'tanggal_pengambilan' => null,
            'siap_diambil_at' => null,
            'jam_kerja_label' => null,
            'qr_token' => Str::random(64),
            'qr_status' => SuratTerbit::QR_STATUS_VALID,
            'qr_digunakan_at' => null,
            'qr_digunakan_oleh' => null,
            'diterbitkan_oleh' => User::factory()->admin(),
        ];
    }

    public function invalid(): static
    {
        return $this->state(fn () => [
            'qr_status' => SuratTerbit::QR_STATUS_INVALID,
            'qr_digunakan_at' => now(),
            'qr_digunakan_oleh' => User::factory()->admin(),
        ]);
    }
}
