<?php

namespace App\Models;

use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonInterface;
use Database\Factories\SuratTerbitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Surat keterangan yang digenerate setelah pengajuan disetujui (US-7.2 / US-7.3 / US-7.4).
 *
 * @property int $id
 * @property int $pengajuan_id
 * @property string $nomor_surat
 * @property string $file_path
 * @property Carbon $tanggal_terbit
 * @property Carbon|null $tanggal_pengambilan
 * @property Carbon|null $siap_diambil_at
 * @property string|null $jam_kerja_label
 * @property string $qr_token
 * @property string $qr_status
 * @property Carbon|null $qr_digunakan_at
 * @property int|null $qr_digunakan_oleh
 * @property int $diterbitkan_oleh
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'pengajuan_id',
    'nomor_surat',
    'file_path',
    'tanggal_terbit',
    'tanggal_pengambilan',
    'siap_diambil_at',
    'jam_kerja_label',
    'qr_token',
    'qr_status',
    'qr_digunakan_at',
    'qr_digunakan_oleh',
    'diterbitkan_oleh',
])]
class SuratTerbit extends Model
{
    /** @use HasFactory<SuratTerbitFactory> */
    use HasFactory;

    public const QR_STATUS_VALID = 'valid';

    public const QR_STATUS_INVALID = 'invalid';

    /**
     * Nama tabel sesuai data model Phase 07 (bukan pluralisasi default).
     *
     * @var string
     */
    protected $table = 'surat_terbit';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'qr_status' => self::QR_STATUS_VALID,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'tanggal_pengambilan' => 'date',
            'siap_diambil_at' => 'datetime',
            'qr_digunakan_at' => 'datetime',
        ];
    }

    /**
     * Pengajuan asal surat ini.
     */
    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class, 'pengajuan_id');
    }

    /**
     * Admin yang menerbitkan surat.
     */
    public function diterbitkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterbitkan_oleh');
    }

    /**
     * Admin yang memakai QR saat pengambilan (US-7.4).
     */
    public function qrDigunakanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'qr_digunakan_oleh');
    }

    /**
     * Generate nomor surat resmi berurutan per tahun (US-7.3).
     * Format: {kode_klasifikasi}/{urut}/{kode_desa}/{bulan romawi}/{tahun}
     * Contoh: 470/12/DS-WDN/VIII/2026
     *
     * Harus dipanggil di dalam transaksi DB agar lockForUpdate efektif.
     * Terpisah dari nomor_pengajuan (PJ-YYYYMMDD-####).
     */
    public static function generateNomorSurat(?CarbonInterface $tanggal = null): string
    {
        $tanggal ??= now();
        $tahun = $tanggal->format('Y');
        $bulanRomawi = self::bulanRomawi((int) $tanggal->format('n'));
        $kodeKlasifikasi = (string) config('desa.kode_klasifikasi', '470');
        $kodeDesa = (string) config('desa.kode_desa', 'DS-WDN');
        $suffix = '/'.$kodeDesa.'/'.$bulanRomawi.'/'.$tahun;

        $maxUrut = static::query()
            ->whereYear('tanggal_terbit', $tahun)
            ->lockForUpdate()
            ->pluck('nomor_surat')
            ->map(function (string $nomor) use ($kodeKlasifikasi): int {
                // Ambil segmen urut: 470/{urut}/DS-WDN/...
                if (! preg_match('/^'.preg_quote($kodeKlasifikasi, '/').'\/(\d+)\//', $nomor, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        $urut = $maxUrut + 1;

        return $kodeKlasifikasi.'/'.$urut.$suffix;
    }

    /**
     * Pola regex nomor surat resmi sesuai config desa (untuk validasi/tes).
     */
    public static function nomorSuratPattern(?CarbonInterface $tanggal = null): string
    {
        $tanggal ??= now();
        $tahun = $tanggal->format('Y');
        $bulanRomawi = preg_quote(self::bulanRomawi((int) $tanggal->format('n')), '/');
        $kodeKlasifikasi = preg_quote((string) config('desa.kode_klasifikasi', '470'), '/');
        $kodeDesa = preg_quote((string) config('desa.kode_desa', 'DS-WDN'), '/');

        return '/^'.$kodeKlasifikasi.'\/\d+\/'.$kodeDesa.'\/'.$bulanRomawi.'\/'.$tahun.'$/';
    }

    /**
     * Buat token QR opaque unik (bukan NIK plain text).
     */
    public static function generateQrToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::query()->where('qr_token', $token)->exists());

        return $token;
    }

    /**
     * Terbitkan surat PDF untuk pengajuan yang baru masuk diproses (US-7.2).
     * Idempotent: jika sudah ada baris surat_terbit, kembalikan yang ada tanpa regenerasi.
     */
    public static function terbitkanUntuk(PengajuanSurat $pengajuan, int $adminId): self
    {
        $existing = static::query()->where('pengajuan_id', $pengajuan->id)->first();

        if ($existing !== null) {
            return $existing;
        }

        $pengajuan->loadMissing(['user', 'jenisSurat']);

        $tanggalTerbit = now();
        $tahun = $tanggalTerbit->format('Y');

        // Cache lock + transaksi DB: urutan nomor unik saat approve bersamaan (US-7.3).
        return Cache::lock('surat-terbit-nomor-'.$tahun, 10)->block(5, function () use ($pengajuan, $adminId, $tanggalTerbit) {
            return DB::transaction(function () use ($pengajuan, $adminId, $tanggalTerbit) {
                $existing = static::query()->where('pengajuan_id', $pengajuan->id)->first();

                if ($existing !== null) {
                    return $existing;
                }

                $nomorSurat = static::generateNomorSurat($tanggalTerbit);
                $qrToken = static::generateQrToken();
                $filePath = 'surat-terbit/'.$pengajuan->id.'/surat.pdf';

                $pdfBinary = static::renderPdfBinary($pengajuan, $nomorSurat, $qrToken, $tanggalTerbit);

                Storage::disk('local')->put($filePath, $pdfBinary);

                return static::query()->create([
                    'pengajuan_id' => $pengajuan->id,
                    'nomor_surat' => $nomorSurat,
                    'file_path' => $filePath,
                    'tanggal_terbit' => $tanggalTerbit->toDateString(),
                    'tanggal_pengambilan' => null,
                    'jam_kerja_label' => null,
                    'qr_token' => $qrToken,
                    'qr_status' => self::QR_STATUS_VALID,
                    'qr_digunakan_at' => null,
                    'qr_digunakan_oleh' => null,
                    'diterbitkan_oleh' => $adminId,
                ]);
            });
        });
    }

    /**
     * Path kanonik PDF di disk lokal untuk pengajuan ini.
     */
    public function canonicalFilePath(): string
    {
        return 'surat-terbit/'.$this->pengajuan_id.'/surat.pdf';
    }

    /**
     * Pastikan file PDF ada di disk (hybrid US-7.6).
     * Jika hilang: regenerate sekali dari data frozen (nomor_surat, qr_token, tanggal_terbit)
     * tanpa membuat token QR baru, lalu simpan ulang ke path kanonik.
     *
     * @return string|null file_path siap diserve, atau null jika pengajuan hilang
     */
    public function pastikanFilePdf(): ?string
    {
        if ($this->file_path !== '' && Storage::disk('local')->exists($this->file_path)) {
            return $this->file_path;
        }

        $canonicalPath = $this->canonicalFilePath();

        if (Storage::disk('local')->exists($canonicalPath)) {
            if ($this->file_path !== $canonicalPath) {
                $this->update(['file_path' => $canonicalPath]);
            }

            return $canonicalPath;
        }

        return Cache::lock('surat-terbit-pdf-'.$this->pengajuan_id, 10)->block(5, function () use ($canonicalPath): ?string {
            $this->refresh();

            if ($this->file_path !== '' && Storage::disk('local')->exists($this->file_path)) {
                return $this->file_path;
            }

            if (Storage::disk('local')->exists($canonicalPath)) {
                if ($this->file_path !== $canonicalPath) {
                    $this->update(['file_path' => $canonicalPath]);
                }

                return $canonicalPath;
            }

            $this->loadMissing(['pengajuan.user', 'pengajuan.jenisSurat']);

            $pengajuan = $this->pengajuan;

            if ($pengajuan === null) {
                return null;
            }

            // Pakai qr_token & nomor_surat yang sudah tersimpan — jangan mint token baru.
            $pdfBinary = static::renderPdfBinary(
                $pengajuan,
                $this->nomor_surat,
                $this->qr_token,
                $this->tanggal_terbit,
            );

            Storage::disk('local')->put($canonicalPath, $pdfBinary);

            $this->update(['file_path' => $canonicalPath]);

            return $canonicalPath;
        });
    }

    /**
     * Render PDF ke string binary sesuai template jenis surat.
     */
    public static function renderPdfBinary(
        PengajuanSurat $pengajuan,
        string $nomorSurat,
        string $qrToken,
        CarbonInterface $tanggalTerbit,
    ): string {
        $template = static::resolveTemplateView($pengajuan->jenisSurat?->nama_surat);
        $qrDataUri = static::qrCodeDataUri($qrToken);

        return Pdf::loadView($template, [
            'pengajuan' => $pengajuan,
            'pemohon' => $pengajuan->user,
            'jenisSurat' => $pengajuan->jenisSurat,
            'nomorSurat' => $nomorSurat,
            'tanggalTerbit' => $tanggalTerbit,
            'qrDataUri' => $qrDataUri,
            'desa' => config('desa'),
        ])
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * Pilih view Blade PDF berdasarkan nama jenis surat.
     */
    public static function resolveTemplateView(?string $namaSurat): string
    {
        $nama = Str::lower($namaSurat ?? '');

        $map = [
            'domisili' => 'pdf.surat.keterangan-domisili',
            'tidak mampu' => 'pdf.surat.keterangan-tidak-mampu',
            'usaha' => 'pdf.surat.keterangan-usaha',
            'kelahiran' => 'pdf.surat.keterangan-kelahiran',
            'kematian' => 'pdf.surat.keterangan-kematian',
        ];

        foreach ($map as $keyword => $view) {
            if (Str::contains($nama, $keyword)) {
                return $view;
            }
        }

        return 'pdf.surat.default';
    }

    /**
     * Generate gambar QR sebagai data URI PNG (Bacon QR Code + GD).
     */
    public static function qrCodeDataUri(string $token): string
    {
        $writer = new Writer(new GDLibRenderer(180));
        $png = $writer->writeString($token);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    /**
     * Label jam kerja kantor untuk tanggal pengambilan (US-7.5).
     * Null jika Sabtu/Minggu atau libur nasional (kantor tutup).
     */
    public static function jamKerjaLabelUntuk(CarbonInterface $tanggal): ?string
    {
        $hari = (int) $tanggal->dayOfWeekIso; // 1=Senin ... 7=Minggu

        if ($hari >= 6) {
            return null;
        }

        if (self::isLiburNasional($tanggal)) {
            return null;
        }

        if ($hari === 5) {
            return (string) config('desa.jam_kerja.jumat', 'Jumat 08.00–16.30 WIB');
        }

        return (string) config('desa.jam_kerja.senin_kamis', 'Senin–Kamis 08.00–16.00 WIB');
    }

    /**
     * Apakah tanggal termasuk libur nasional (config desa.libur_nasional).
     */
    public static function isLiburNasional(CarbonInterface $tanggal): bool
    {
        $list = config('desa.libur_nasional', []);

        if (! is_array($list)) {
            return false;
        }

        return in_array($tanggal->format('Y-m-d'), $list, true);
    }

    /**
     * Validasi tanggal pengambilan: hari kerja + bukan libur + tidak di masa lalu (WIB).
     *
     * @return array{ok: bool, message: string, jam_kerja_label: string|null}
     */
    public static function validasiTanggalPengambilan(CarbonInterface $tanggal): array
    {
        $today = now('Asia/Jakarta')->startOfDay();
        $tanggal = $tanggal->copy()->timezone('Asia/Jakarta')->startOfDay();

        if ($tanggal->lt($today)) {
            return [
                'ok' => false,
                'message' => 'Tanggal pengambilan tidak boleh di masa lalu.',
                'jam_kerja_label' => null,
            ];
        }

        $hari = (int) $tanggal->dayOfWeekIso;

        if ($hari >= 6) {
            return [
                'ok' => false,
                'message' => 'Kantor tutup pada Sabtu–Minggu. Pilih hari kerja Senin–Jumat.',
                'jam_kerja_label' => null,
            ];
        }

        if (self::isLiburNasional($tanggal)) {
            return [
                'ok' => false,
                'message' => 'Tanggal tersebut adalah libur nasional. Kantor tutup — pilih tanggal lain.',
                'jam_kerja_label' => null,
            ];
        }

        $label = self::jamKerjaLabelUntuk($tanggal);

        return [
            'ok' => true,
            'message' => 'Tanggal pengambilan valid.',
            'jam_kerja_label' => $label,
        ];
    }

    /**
     * Tandai dokumen siap diambil: diproses → siap_diambil + simpan tanggal & jam kerja (US-7.5 / US-8.6).
     *
     * @return array{ok: bool, message: string}
     */
    public static function tandaiSiapDiambil(PengajuanSurat $pengajuan, CarbonInterface $tanggalPengambilan): array
    {
        $validasi = self::validasiTanggalPengambilan($tanggalPengambilan);

        if (! $validasi['ok']) {
            return [
                'ok' => false,
                'message' => $validasi['message'],
            ];
        }

        return DB::transaction(function () use ($pengajuan, $tanggalPengambilan, $validasi): array {
            $pengajuanLocked = PengajuanSurat::query()
                ->whereKey($pengajuan->id)
                ->lockForUpdate()
                ->first();

            if ($pengajuanLocked === null) {
                return [
                    'ok' => false,
                    'message' => 'Pengajuan tidak ditemukan.',
                ];
            }

            if ($pengajuanLocked->status !== PengajuanSurat::STATUS_DIPROSES) {
                return [
                    'ok' => false,
                    'message' => 'Hanya pengajuan berstatus diproses yang dapat ditandai siap diambil.',
                ];
            }

            $surat = static::query()
                ->where('pengajuan_id', $pengajuanLocked->id)
                ->lockForUpdate()
                ->first();

            if ($surat === null) {
                return [
                    'ok' => false,
                    'message' => 'PDF surat belum tersedia. Tidak dapat menandai siap diambil.',
                ];
            }

            $tanggal = $tanggalPengambilan->copy()->timezone('Asia/Jakarta')->startOfDay();
            $siapDiambilAt = now();

            $surat->update([
                'tanggal_pengambilan' => $tanggal->toDateString(),
                'jam_kerja_label' => $validasi['jam_kerja_label'],
                'siap_diambil_at' => $siapDiambilAt,
            ]);

            $pengajuanLocked->update([
                'status' => PengajuanSurat::STATUS_SIAP_DIAMBIL,
            ]);

            $pengajuanLocked->loadMissing('jenisSurat');
            $namaSurat = $pengajuanLocked->jenisSurat?->nama_surat ?? 'Surat';
            $tanggalLabel = $tanggal->translatedFormat('d M Y');
            $jamLabel = $validasi['jam_kerja_label'] ?? '';

            // Pesan mengikuti AC US-8.6.
            Notifikasi::query()->create([
                'user_id' => $pengajuanLocked->user_id,
                'pengajuan_id' => $pengajuanLocked->id,
                'pesan' => "Surat {$namaSurat} Anda (#{$pengajuanLocked->nomor_pengajuan}) sudah siap diambil pada {$tanggalLabel} ({$jamLabel}).",
                'status_baca' => Notifikasi::STATUS_BELUM,
                'created_at' => now(),
            ]);

            return [
                'ok' => true,
                'message' => 'Dokumen ditandai siap diambil. Warga telah diberi notifikasi.',
            ];
        });
    }

    /**
     * Scan QR pengambilan sekali pakai (US-7.4).
     * Sukses hanya jika pengajuan siap_diambil dan qr_status=valid;
     * update kondisional WHERE qr_status=valid agar race dua admin aman.
     *
     * @return array{ok: bool, message: string}
     */
    public static function scanUntukPengambilan(string $token, int $adminId): array
    {
        $token = trim($token);

        if ($token === '') {
            return [
                'ok' => false,
                'message' => 'Token QR wajib diisi.',
            ];
        }

        return DB::transaction(function () use ($token, $adminId): array {
            $surat = static::query()
                ->where('qr_token', $token)
                ->lockForUpdate()
                ->first();

            if ($surat === null) {
                return [
                    'ok' => false,
                    'message' => 'Token QR tidak dikenal.',
                ];
            }

            if ($surat->qr_status === self::QR_STATUS_INVALID) {
                return [
                    'ok' => false,
                    'message' => 'QR sudah digunakan / tidak valid.',
                ];
            }

            $pengajuan = PengajuanSurat::query()
                ->whereKey($surat->pengajuan_id)
                ->lockForUpdate()
                ->first();

            if ($pengajuan === null) {
                return [
                    'ok' => false,
                    'message' => 'Token QR tidak dikenal.',
                ];
            }

            if ($pengajuan->status !== PengajuanSurat::STATUS_SIAP_DIAMBIL) {
                return [
                    'ok' => false,
                    'message' => 'Pengajuan belum siap diambil. QR ditolak.',
                ];
            }

            // Enforcement server-side: hanya baris yang masih valid yang boleh diubah.
            $affected = static::query()
                ->whereKey($surat->id)
                ->where('qr_status', self::QR_STATUS_VALID)
                ->update([
                    'qr_status' => self::QR_STATUS_INVALID,
                    'qr_digunakan_at' => now(),
                    'qr_digunakan_oleh' => $adminId,
                ]);

            if ($affected === 0) {
                return [
                    'ok' => false,
                    'message' => 'QR sudah digunakan / tidak valid.',
                ];
            }

            $pengajuan->update([
                'status' => PengajuanSurat::STATUS_SELESAI,
            ]);

            $pengajuan->loadMissing('jenisSurat');
            $namaSurat = $pengajuan->jenisSurat?->nama_surat ?? 'Surat';

            Notifikasi::query()->create([
                'user_id' => $pengajuan->user_id,
                'pengajuan_id' => $pengajuan->id,
                'pesan' => "Pengajuan {$namaSurat} ({$pengajuan->nomor_pengajuan}) selesai.",
                'status_baca' => Notifikasi::STATUS_BELUM,
                'created_at' => now(),
            ]);

            return [
                'ok' => true,
                'message' => 'Pengambilan berhasil dicatat. QR sekarang tidak valid.',
            ];
        });
    }

    /**
     * Konversi nomor bulan ke angka Romawi.
     */
    public static function bulanRomawi(int $bulan): string
    {
        return match ($bulan) {
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
            default => 'I',
        };
    }
}
