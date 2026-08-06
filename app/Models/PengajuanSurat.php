<?php

namespace App\Models;

use Database\Factories\PengajuanSuratFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Pengajuan surat keterangan oleh warga (Phase 03).
 *
 * @property int $id
 * @property int $user_id
 * @property int $jenis_surat_id
 * @property string $nomor_pengajuan
 * @property string $keperluan
 * @property string $status
 * @property string|null $catatan_admin
 * @property int|null $diverifikasi_oleh
 * @property Carbon $tanggal_pengajuan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'user_id',
    'jenis_surat_id',
    'nomor_pengajuan',
    'keperluan',
    'status',
    'catatan_admin',
    'diverifikasi_oleh',
    'tanggal_pengajuan',
])]
class PengajuanSurat extends Model
{
    /** @use HasFactory<PengajuanSuratFactory> */
    use HasFactory;

    /** Status awal saat warga mengajukan. */
    public const STATUS_DIAJUKAN = 'diajukan';

    public const STATUS_DIPROSES = 'diproses';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    /**
     * Nama tabel sesuai data model Phase 03 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'pengajuan_surat';

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
        ];
    }

    /**
     * Warga pemohon pengajuan.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Jenis surat yang diajukan.
     */
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    /**
     * Admin yang memverifikasi pengajuan (Phase 04).
     */
    public function diverifikasiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diverifikasi_oleh');
    }

    /**
     * Dokumen persyaratan yang diunggah warga (US-3.2).
     */
    public function dokumenPersyaratan(): HasMany
    {
        return $this->hasMany(DokumenPersyaratan::class, 'pengajuan_id');
    }

    /**
     * Log audit keputusan verifikasi admin (US-4.3).
     */
    public function logVerifikasi(): HasMany
    {
        return $this->hasMany(LogVerifikasi::class, 'pengajuan_id');
    }
}
