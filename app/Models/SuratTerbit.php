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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Surat keterangan yang digenerate setelah pengajuan disetujui (US-7.2).
 *
 * @property int $id
 * @property int $pengajuan_id
 * @property string $nomor_surat
 * @property string $file_path
 * @property Carbon $tanggal_terbit
 * @property Carbon|null $tanggal_pengambilan
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
     * Generate nomor surat resmi berurutan per tahun.
     * Format: {kode}/{urut}/DS-WDN/{bulan romawi}/{tahun}
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

        // Kunci nomor per tahun agar urutan unik saat approve bersamaan.
        return Cache::lock('surat-terbit-nomor-'.$tahun, 10)->block(5, function () use ($pengajuan, $adminId, $tanggalTerbit) {
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
