<?php

namespace Database\Factories;

use App\Models\LogVerifikasi;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogVerifikasi>
 */
class LogVerifikasiFactory extends Factory
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
            'admin_id' => User::factory()->admin(),
            'aksi' => LogVerifikasi::AKSI_SETUJUI,
            'keterangan' => null,
            'created_at' => now(),
        ];
    }

    public function setujui(?string $keterangan = null): static
    {
        return $this->state(fn () => [
            'aksi' => LogVerifikasi::AKSI_SETUJUI,
            'keterangan' => $keterangan,
        ]);
    }

    public function tolak(?string $keterangan = null): static
    {
        return $this->state(fn () => [
            'aksi' => LogVerifikasi::AKSI_TOLAK,
            'keterangan' => $keterangan ?? fake()->sentence(),
        ]);
    }
}
