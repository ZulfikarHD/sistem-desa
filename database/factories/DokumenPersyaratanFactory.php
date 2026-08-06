<?php

namespace Database\Factories;

use App\Models\DokumenPersyaratan;
use App\Models\PengajuanSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DokumenPersyaratan>
 */
class DokumenPersyaratanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pengajuan_id' => PengajuanSurat::factory(),
            'jenis_dokumen' => fake()->randomElement([
                DokumenPersyaratan::JENIS_KTP,
                DokumenPersyaratan::JENIS_KK,
            ]),
            'file_path' => 'pengajuan-dokumen/'.fake()->uuid().'/KTP.jpg',
        ];
    }

    /**
     * Dokumen KTP.
     */
    public function ktp(): static
    {
        return $this->state(fn (): array => [
            'jenis_dokumen' => DokumenPersyaratan::JENIS_KTP,
            'file_path' => 'pengajuan-dokumen/'.fake()->uuid().'/KTP.jpg',
        ]);
    }

    /**
     * Dokumen KK.
     */
    public function kk(): static
    {
        return $this->state(fn (): array => [
            'jenis_dokumen' => DokumenPersyaratan::JENIS_KK,
            'file_path' => 'pengajuan-dokumen/'.fake()->uuid().'/KK.jpg',
        ]);
    }
}
