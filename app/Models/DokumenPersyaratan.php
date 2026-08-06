<?php

namespace App\Models;

use Database\Factories\DokumenPersyaratanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Dokumen persyaratan yang diunggah warga pada pengajuan surat (US-3.2).
 *
 * @property int $id
 * @property int $pengajuan_id
 * @property string $jenis_dokumen
 * @property string $file_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'pengajuan_id',
    'jenis_dokumen',
    'file_path',
])]
class DokumenPersyaratan extends Model
{
    /** @use HasFactory<DokumenPersyaratanFactory> */
    use HasFactory;

    public const JENIS_KTP = 'KTP';

    public const JENIS_KK = 'KK';

    /**
     * Nama tabel sesuai data model Phase 03 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'dokumen_persyaratan';

    /**
     * Pengajuan surat yang memiliki dokumen ini.
     */
    public function pengajuanSurat(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'pengajuan_id');
    }
}
