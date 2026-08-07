<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\PengajuanSuratFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /** Surat digenerate + sedang diproses (US-8.4: langsung dari setujui). */
    public const STATUS_DIPROSES = 'diproses';

    /**
     * Status historis Phase 07 — tetap di DB, tidak dipakai alur baru (US-8.4).
     * Tampilan UI memakai label "Diproses" agar warga tidak melihat status perantara.
     */
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
            // Historis disetujui ditampilkan sebagai Diproses (mitigasi risiko Phase 08)
            self::STATUS_DISETUJUI,
            self::STATUS_DIPROSES => 'Diproses',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_SIAP_DIAMBIL => 'Siap Diambil',
            self::STATUS_SELESAI => 'Selesai',
            default => $status,
        };
    }

    /**
     * Warna badge Flux untuk status (UI Notes Phase 08 — konsisten di seluruh sistem).
     */
    public static function statusBadgeColor(string $status): string
    {
        return match ($status) {
            self::STATUS_DIAJUKAN => 'amber',
            self::STATUS_DISETUJUI,
            self::STATUS_DIPROSES => 'blue',
            self::STATUS_SIAP_DIAMBIL => 'green',
            self::STATUS_SELESAI => 'zinc',
            self::STATUS_DITOLAK => 'red',
            default => 'zinc',
        };
    }

    /**
     * Status yang masih aktif (belum selesai / ditolak).
     *
     * @return list<string>
     */
    public static function statusAktif(): array
    {
        return [
            self::STATUS_DIAJUKAN,
            self::STATUS_DISETUJUI,
            self::STATUS_DIPROSES,
            self::STATUS_SIAP_DIAMBIL,
        ];
    }

    /**
     * Status yang dihitung sebagai "sedang diproses" di dashboard (termasuk historis disetujui).
     *
     * @return list<string>
     */
    public static function statusDiprosesDashboard(): array
    {
        return [
            self::STATUS_DIPROSES,
            self::STATUS_DISETUJUI,
        ];
    }

    /**
     * Waktu masuk ke status saat ini (untuk aging / elapsed time dashboard).
     */
    public function waktuMasukStatusSaatIni(): CarbonInterface
    {
        return match ($this->status) {
            self::STATUS_DIAJUKAN => $this->created_at ?? now(),
            self::STATUS_DISETUJUI,
            self::STATUS_DIPROSES => $this->suratTerbit?->tanggal_terbit
                ? Carbon::parse($this->suratTerbit->tanggal_terbit)->timezone('Asia/Jakarta')->startOfDay()
                : ($this->updated_at ?? $this->created_at ?? now()),
            self::STATUS_SIAP_DIAMBIL => $this->suratTerbit?->siap_diambil_at
                ?? $this->suratTerbit?->updated_at
                ?? $this->updated_at
                ?? now(),
            self::STATUS_SELESAI => $this->suratTerbit?->qr_digunakan_at
                ?? $this->updated_at
                ?? now(),
            self::STATUS_DITOLAK => $this->updated_at ?? $this->created_at ?? now(),
            default => $this->created_at ?? now(),
        };
    }

    /**
     * Jumlah hari kalender sejak masuk status saat ini (timezone Asia/Jakarta).
     */
    public function hariDiStatusSaatIni(?CarbonInterface $hariIni = null): int
    {
        $hariIni ??= now('Asia/Jakarta')->startOfDay();
        $masuk = $this->waktuMasukStatusSaatIni()->timezone('Asia/Jakarta')->startOfDay();

        return (int) $masuk->diffInDays($hariIni);
    }

    /**
     * Opsi filter status lengkap (Phase 07), tanpa opsi "semua".
     * Filter "Disetujui (historis)" tetap ada untuk data lama; label tampilan beda dari statusLabel.
     *
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_DIAJUKAN => self::statusLabel(self::STATUS_DIAJUKAN),
            self::STATUS_DISETUJUI => 'Disetujui (historis)',
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

    /**
     * Surat yang digenerate setelah pengajuan disetujui (US-7.2).
     */
    public function suratTerbit(): HasOne
    {
        return $this->hasOne(SuratTerbit::class, 'pengajuan_id');
    }

    /**
     * Status yang mengizinkan warga mengunduh/mencetak PDF surat (US-7.6).
     *
     * @return list<string>
     */
    public static function statusBolehUnduhSurat(): array
    {
        return [
            self::STATUS_SIAP_DIAMBIL,
            self::STATUS_SELESAI,
        ];
    }

    /**
     * Apakah warga boleh mengunduh PDF surat terbit untuk pengajuan ini.
     */
    public function dapatUnduhSurat(): bool
    {
        return in_array($this->status, self::statusBolehUnduhSurat(), true)
            && $this->suratTerbit !== null;
    }
}
