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

    /** Status awal saat warga mengajukan / menunggu verifikasi. */
    public const STATUS_DIAJUKAN = 'diajukan';

    /** Pasca-disetujui: PDF + nomor + QR digenerate (Phase 07). */
    public const STATUS_DIPROSES = 'diproses';

    /** Verifikasi data OK — belum berarti surat siap. */
    public const STATUS_DISETUJUI = 'disetujui';

    /** Verifikasi gagal — alur berhenti. */
    public const STATUS_DITOLAK = 'ditolak';

    /** Admin set tanggal pengambilan; warga dapat notifikasi. */
    public const STATUS_SIAP_DIAMBIL = 'siap_diambil';

    /** Admin scan QR sukses sekali; QR invalid. */
    public const STATUS_SELESAI = 'selesai';

    /**
     * Nama tabel sesuai data model Phase 03 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'pengajuan_surat';

    /**
     * Label tampilan untuk status pengajuan.
     */
    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_SELESAI => 'Selesai',
            default => $status,
        };
    }

    /**
     * Opsi filter status lengkap (Phase 07), tanpa opsi "semua".
     *
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIAJUKAN => self::statusLabel(self::STATUS_DIAJUKAN),
            self::STATUS_DISETUJUI => self::statusLabel(self::STATUS_DISETUJUI),
            self::STATUS_DIPROSES => self::statusLabel(self::STATUS_DIPROSES),
            self::STATUS_SIAP_DIAMBIL => self::statusLabel(self::STATUS_SIAP_DIAMBIL),
            self::STATUS_SELESAI => self::statusLabel(self::STATUS_SELESAI),
            self::STATUS_DITOLAK => self::statusLabel(self::STATUS_DITOLAK),
        ];
    }

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

    /**
     * Notifikasi in-app terkait pengajuan (Phase 05).
     */
    public function notifikasi(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'pengajuan_id');
    }
}
