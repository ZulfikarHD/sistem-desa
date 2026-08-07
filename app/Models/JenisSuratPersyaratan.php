<?php

namespace App\Models;

use Database\Factories\JenisSuratPersyaratanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Baris persyaratan terstruktur untuk satu jenis surat (US-9.1 / US-9.2).
 *
 * @property int $id
 * @property int $jenis_surat_id
 * @property string $nama
 * @property string $cara_pemenuhan
 * @property bool $is_wajib
 * @property int $urutan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['jenis_surat_id', 'nama', 'cara_pemenuhan', 'is_wajib', 'urutan'])]
class JenisSuratPersyaratan extends Model
{
    /** @use HasFactory<JenisSuratPersyaratanFactory> */
    use HasFactory;

    public const CARA_UNGGAH = 'unggah';

    public const CARA_BAWA_KANTOR = 'bawa_kantor';

    public const CARA_INFO = 'info';

    /**
     * Nama tabel sesuai data model Phase 09 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'jenis_surat_persyaratan';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'cara_pemenuhan' => self::CARA_UNGGAH,
        'is_wajib' => true,
        'urutan' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_wajib' => 'boolean',
            'urutan' => 'integer',
        ];
    }

    /**
     * Jenis surat induk.
     */
    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class, 'jenis_surat_id');
    }

    /**
     * Opsi cara memenuhi untuk form admin (nilai => label UI).
     *
     * @return array<string, string>
     */
    public static function caraPemenuhanOptions(): array
    {
        return [
            self::CARA_UNGGAH => 'Unggah di aplikasi',
            self::CARA_BAWA_KANTOR => 'Bawa ke kantor desa',
            self::CARA_INFO => 'Tidak perlu file',
        ];
    }

    /**
     * Teks bantuan di bawah pilihan cara memenuhi.
     *
     * @return array<string, string>
     */
    public static function caraPemenuhanHelpers(): array
    {
        return [
            self::CARA_UNGGAH => 'Warga kirim foto/scan lewat HP di form pengajuan.',
            self::CARA_BAWA_KANTOR => 'Warga bawa berkas fisik; tidak ada tombol unggah.',
            self::CARA_INFO => 'Hanya catatan/informasi; tidak perlu diunggah maupun dibawa.',
        ];
    }

    /**
     * Label badge yang dilihat warga / pratinjau admin.
     */
    public function badgeLabel(): string
    {
        return match ($this->cara_pemenuhan) {
            self::CARA_UNGGAH => $this->is_wajib ? 'Wajib diunggah' : 'Boleh dikosongkan',
            self::CARA_BAWA_KANTOR => 'Bawa ke kantor',
            self::CARA_INFO => 'Informasi',
            default => 'Informasi',
        };
    }

    /**
     * Warna Flux badge selaras pratinjau admin & form warga.
     */
    public function badgeColor(): string
    {
        return match ($this->cara_pemenuhan) {
            self::CARA_UNGGAH => $this->is_wajib ? 'red' : 'amber',
            self::CARA_BAWA_KANTOR => 'blue',
            default => 'zinc',
        };
    }

    /**
     * Teks bantuan singkat di form warga untuk cara bawa ke kantor.
     */
    public static function bantuanBawaKantor(): string
    {
        return 'Siapkan berkas ini dan bawa saat diminta petugas / saat pengambilan.';
    }

    /**
     * Parse teks persyaratan lama menjadi baris terstruktur (sekali jalan / seeder).
     *
     * Aturan migrasi (ADR-026):
     * - mengandung KTP / KK / Kartu Keluarga → unggah (wajib, kecuali frasa opsional)
     * - mengandung "jika ada" / "(opsional)" / "jika relevan" pada baris unggah → is_wajib = false
     * - baris lain → bawa_kantor (default konservatif)
     * - teks kosong → satu baris info fallback
     *
     * @return list<array{nama: string, cara_pemenuhan: string, is_wajib: bool, urutan: int}>
     */
    public static function parseFromFreeText(?string $teks): array
    {
        $lines = preg_split("/\r\n|\n|\r/", (string) $teks) ?: [];
        $rows = [];
        $urutan = 0;

        foreach ($lines as $line) {
            $nama = trim(preg_replace('/^[\s\-\*\d\.\)\(]+/u', '', $line) ?? '');

            if ($nama === '') {
                continue;
            }

            $lower = mb_strtolower($nama);
            $isOpsional = self::teksMenandaiOpsional($lower);
            $isUnggah = self::teksMenandaiUnggah($lower);

            if ($isUnggah) {
                $rows[] = [
                    'nama' => $nama,
                    'cara_pemenuhan' => self::CARA_UNGGAH,
                    'is_wajib' => ! $isOpsional,
                    'urutan' => $urutan++,
                ];

                continue;
            }

            // Opsional tapi bukan KTP/KK: default bawa kantor (bukan memaksa unggah).
            $rows[] = [
                'nama' => $nama,
                'cara_pemenuhan' => self::CARA_BAWA_KANTOR,
                'is_wajib' => true,
                'urutan' => $urutan++,
            ];
        }

        if ($rows === []) {
            return [[
                'nama' => 'Persyaratan belum diatur — silakan ubah di menu Jenis Surat',
                'cara_pemenuhan' => self::CARA_INFO,
                'is_wajib' => true,
                'urutan' => 0,
            ]];
        }

        return $rows;
    }

    /**
     * Apakah teks menandai dokumen identitas yang biasanya diunggah.
     */
    public static function teksMenandaiUnggah(string $lowerNama): bool
    {
        if (str_contains($lowerNama, 'kartu keluarga')) {
            return true;
        }

        // Hindari false positive pada frasa generik; cocokkan token KTP/KK.
        return (bool) preg_match('/\bktp\b/u', $lowerNama)
            || (bool) preg_match('/\bkk\b/u', $lowerNama);
    }

    /**
     * Apakah teks menandai syarat opsional / jika ada.
     */
    public static function teksMenandaiOpsional(string $lowerNama): bool
    {
        return str_contains($lowerNama, 'jika ada')
            || str_contains($lowerNama, '(opsional)')
            || str_contains($lowerNama, 'opsional')
            || str_contains($lowerNama, 'jika relevan');
    }

    /**
     * Bangun ringkasan teks dari daftar baris (untuk kolom persyaratan_dokumen).
     *
     * @param  iterable<int, array{nama?: string}|self>  $rows
     */
    public static function generateRingkasan(iterable $rows): string
    {
        $lines = [];

        foreach ($rows as $row) {
            $nama = is_array($row) ? trim((string) ($row['nama'] ?? '')) : trim((string) $row->nama);

            if ($nama === '') {
                continue;
            }

            $lines[] = '- '.$nama;
        }

        return implode("\n", $lines);
    }
}
