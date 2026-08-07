<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Identitas desa untuk kop PDF / nomor surat (satu baris).
 *
 * @property int $id
 * @property string $nama_desa
 * @property string $kecamatan
 * @property string $kabupaten
 * @property string $provinsi
 * @property string $alamat_kantor
 * @property string|null $kode_pos
 * @property string|null $telepon
 * @property string $penandatangan_nama
 * @property string $penandatangan_jabatan
 * @property string $kode_klasifikasi
 * @property string $kode_desa
 */
#[Fillable([
    'nama_desa',
    'kecamatan',
    'kabupaten',
    'provinsi',
    'alamat_kantor',
    'kode_pos',
    'telepon',
    'penandatangan_nama',
    'penandatangan_jabatan',
    'kode_klasifikasi',
    'kode_desa',
])]
class PengaturanDesa extends Model
{
    /**
     * @var string
     */
    protected $table = 'pengaturan_desa';

    /**
     * Ambil baris tunggal; buat dari config/desa.php jika belum ada.
     */
    public static function instance(): self
    {
        $existing = static::query()->first();

        if ($existing !== null) {
            return $existing;
        }

        return static::query()->create(static::defaultsFromConfig());
    }

    /**
     * Nilai default dari config (fallback .env).
     *
     * @return array{
     *     nama_desa: string,
     *     kecamatan: string,
     *     kabupaten: string,
     *     provinsi: string,
     *     alamat_kantor: string,
     *     kode_pos: string|null,
     *     telepon: string|null,
     *     penandatangan_nama: string,
     *     penandatangan_jabatan: string,
     *     kode_klasifikasi: string,
     *     kode_desa: string
     * }
     */
    public static function defaultsFromConfig(): array
    {
        return [
            'nama_desa' => (string) config('desa.nama_desa', 'Desa Wadon'),
            'kecamatan' => (string) config('desa.kecamatan', 'Kecamatan Contoh'),
            'kabupaten' => (string) config('desa.kabupaten', 'Kabupaten Contoh'),
            'provinsi' => (string) config('desa.provinsi', 'Jawa Barat'),
            'alamat_kantor' => (string) config('desa.alamat_kantor', 'Jl. Desa No. 1'),
            'kode_pos' => config('desa.kode_pos'),
            'telepon' => config('desa.telepon'),
            'penandatangan_nama' => (string) config('desa.penandatangan_nama', 'Kepala Desa'),
            'penandatangan_jabatan' => (string) config('desa.penandatangan_jabatan', 'Kepala Desa'),
            'kode_klasifikasi' => (string) config('desa.kode_klasifikasi', '470'),
            'kode_desa' => (string) config('desa.kode_desa', 'DS-WDN'),
        ];
    }

    /**
     * Array untuk template PDF / nomor surat (DB dulu, config untuk jam/libur).
     *
     * @return array<string, mixed>
     */
    public static function untukSurat(): array
    {
        $row = static::instance();

        return array_merge(config('desa', []), [
            'nama_desa' => $row->nama_desa,
            'kecamatan' => $row->kecamatan,
            'kabupaten' => $row->kabupaten,
            'provinsi' => $row->provinsi,
            'alamat_kantor' => $row->alamat_kantor,
            'kode_pos' => $row->kode_pos,
            'telepon' => $row->telepon,
            'penandatangan_nama' => $row->penandatangan_nama,
            'penandatangan_jabatan' => $row->penandatangan_jabatan,
            'kode_klasifikasi' => $row->kode_klasifikasi,
            'kode_desa' => $row->kode_desa,
        ]);
    }
}
