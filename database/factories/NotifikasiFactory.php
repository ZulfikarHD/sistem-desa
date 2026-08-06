<?php

namespace Database\Factories;

use App\Models\Notifikasi;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notifikasi>
 */
class NotifikasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pengajuan_id' => PengajuanSurat::factory(),
            'pesan' => fake()->sentence(),
            'status_baca' => Notifikasi::STATUS_BELUM,
            'created_at' => now(),
        ];
    }

    public function dibaca(): static
    {
        return $this->state(fn () => [
            'status_baca' => Notifikasi::STATUS_DIBACA,
        ]);
    }
}
