<?php

namespace App\Models;

use Database\Factories\DokumenPersyaratanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Dokumen persyaratan yang diunggah warga pada pengajuan surat (US-3.2 / US-9.3).
 *
 * @property int $id
 * @property int $pengajuan_id
 * @property int|null $jenis_surat_persyaratan_id
 * @property string $jenis_dokumen
 * @property string $file_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'pengajuan_id',
    'jenis_surat_persyaratan_id',
    'jenis_dokumen',
    'file_path',
])]
class DokumenPersyaratan extends Model
{
    /** @use HasFactory<DokumenPersyaratanFactory> */
    use HasFactory;

    /** Nilai historis/seeder untuk dokumen KTP (bukan lagi satu-satunya sumber aturan slot). */
    public const JENIS_KTP = 'KTP';

    /** Nilai historis/seeder untuk dokumen KK. */
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

    /**
     * Baris syarat terstruktur yang menjadi sumber slot unggah (US-9.3).
     */
    public function jenisSuratPersyaratan(): BelongsTo
    {
        return $this->belongsTo(JenisSuratPersyaratan::class, 'jenis_surat_persyaratan_id');
    }

    /**
     * Label tampilan: nama syarat terstruktur, fallback ke jenis_dokumen.
     */
    public function labelDokumen(): string
    {
        $namaSyarat = $this->jenisSuratPersyaratan?->nama;

        if (is_string($namaSyarat) && trim($namaSyarat) !== '') {
            return trim($namaSyarat);
        }

        return $this->jenis_dokumen;
    }
}
