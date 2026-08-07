<?php

namespace Database\Factories;

use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JenisSurat>
 */
class JenisSuratFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_surat' => fake()->unique()->randomElement([
                'Surat Keterangan Domisili',
                'Surat Keterangan Kelahiran',
                'Surat Keterangan Kematian',
                'Surat Keterangan Tidak Mampu',
                'Surat Keterangan Usaha',
            ]).' '.fake()->unique()->numerify('###'),
            'deskripsi' => fake()->sentence(),
            'persyaratan_dokumen' => "- Fotokopi KTP\n- Fotokopi Kartu Keluarga\n- Surat pengantar RT/RW",
        ];
    }

    /**
     * Setelah create, isi baris persyaratan terstruktur dari teks persyaratan_dokumen
     * (sumber aturan unggah form warga sejak US-9.3).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (JenisSurat $jenisSurat): void {
            if ($jenisSurat->persyaratan()->exists()) {
                return;
            }

            $rows = JenisSuratPersyaratan::parseFromFreeText($jenisSurat->persyaratan_dokumen);

            foreach ($rows as $row) {
                $jenisSurat->persyaratan()->create($row);
            }
        });
    }
}
