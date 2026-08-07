<?php

namespace Database\Factories;

use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisSuratPersyaratan>
 */
class JenisSuratPersyaratanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'jenis_surat_id' => JenisSurat::factory(),
            'nama' => fake()->randomElement([
                'Fotokopi KTP',
                'Fotokopi Kartu Keluarga (KK)',
                'Surat pengantar RT/RW',
            ]),
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
            'urutan' => 0,
        ];
    }

    /**
     * Syarat yang harus diunggah secara wajib.
     */
    public function unggahWajib(string $nama = 'Fotokopi KTP'): static
    {
        return $this->state(fn (): array => [
            'nama' => $nama,
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => true,
        ]);
    }

    /**
     * Syarat unggah yang boleh dikosongkan.
     */
    public function unggahOpsional(string $nama = 'Bukti pendukung (jika ada)'): static
    {
        return $this->state(fn (): array => [
            'nama' => $nama,
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
            'is_wajib' => false,
        ]);
    }

    /**
     * Syarat fisik dibawa ke kantor.
     */
    public function bawaKantor(string $nama = 'Surat pengantar RT/RW'): static
    {
        return $this->state(fn (): array => [
            'nama' => $nama,
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
            'is_wajib' => true,
        ]);
    }

    /**
     * Hanya catatan informasi.
     */
    public function info(string $nama = 'Catatan tambahan'): static
    {
        return $this->state(fn (): array => [
            'nama' => $nama,
            'cara_pemenuhan' => JenisSuratPersyaratan::CARA_INFO,
            'is_wajib' => true,
        ]);
    }
}
