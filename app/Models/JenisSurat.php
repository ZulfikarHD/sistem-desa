<?php

namespace App\Models;

use Database\Factories\JenisSuratFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Master data jenis surat keterangan (US-2.1).
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
}
