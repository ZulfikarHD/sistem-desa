<?php

namespace App\Models;

use Database\Factories\JenisSuratFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Master data jenis surat keterangan (US-2.1) + persyaratan terstruktur (US-9.1 / US-9.2).
 *
 * @property int $id
 * @property string $nama_surat
 * @property string|null $deskripsi
 * @property string $persyaratan_dokumen
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['nama_surat', 'deskripsi', 'persyaratan_dokumen'])]
class JenisSurat extends Model
{
    /** @use HasFactory<JenisSuratFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel sesuai data model Phase 02 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'jenis_surat';

    /**
     * Pengajuan surat yang menggunakan jenis surat ini.
     */
    public function pengajuanSurat(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'jenis_surat_id');
    }

    /**
     * Baris persyaratan terstruktur, diurutkan.
     */
    public function persyaratan(): HasMany
    {
        return $this->hasMany(JenisSuratPersyaratan::class, 'jenis_surat_id')
            ->orderBy('urutan')
            ->orderBy('id');
    }

    /**
     * Ganti seluruh baris persyaratan lalu sync ringkasan teks.
     *
     * @param  list<array{nama: string, cara_pemenuhan: string, is_wajib?: bool, urutan?: int}>  $rows
     */
    public function syncPersyaratan(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            $this->persyaratan()->delete();

            foreach (array_values($rows) as $index => $row) {
                $cara = $row['cara_pemenuhan'] ?? JenisSuratPersyaratan::CARA_BAWA_KANTOR;
                $isWajib = $cara === JenisSuratPersyaratan::CARA_UNGGAH
                    ? (bool) ($row['is_wajib'] ?? true)
                    : true;

                $this->persyaratan()->create([
                    'nama' => trim((string) ($row['nama'] ?? '')),
                    'cara_pemenuhan' => $cara,
                    'is_wajib' => $isWajib,
                    'urutan' => (int) ($row['urutan'] ?? $index),
                ]);
            }

            $ringkasan = JenisSuratPersyaratan::generateRingkasan(
                $this->persyaratan()->get()
            );

            $this->forceFill([
                'persyaratan_dokumen' => $ringkasan !== ''
                    ? $ringkasan
                    : 'Persyaratan belum diatur — silakan ubah di menu Jenis Surat',
            ])->save();
        });
    }
}
