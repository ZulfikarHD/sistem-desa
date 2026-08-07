<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use App\Models\JenisSuratPersyaratan;
use Illuminate\Database\Seeder;

class JenisSuratSeeder extends Seeder
{
    /**
     * Master data jenis surat keterangan desa (Indonesia) beserta persyaratan terstruktur.
     * Ringkasan teks persyaratan_dokumen digenerate dari baris terstruktur (US-9.1 / US-9.2).
     */
    public function run(): void
    {
        foreach ($this->data() as $item) {
            $jenisSurat = JenisSurat::query()->updateOrCreate(
                ['nama_surat' => $item['nama_surat']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'persyaratan_dokumen' => JenisSuratPersyaratan::generateRingkasan($item['persyaratan']),
                ],
            );

            $jenisSurat->syncPersyaratan($item['persyaratan']);
        }
    }

    /**
     * @return list<array{nama_surat: string, deskripsi: string, persyaratan: list<array{nama: string, cara_pemenuhan: string, is_wajib: bool}>}>
     */
    private function data(): array
    {
        return [
            [
                'nama_surat' => 'Surat Keterangan Domisili',
                'deskripsi' => 'Menerangkan bahwa pemohon bertempat tinggal di wilayah desa. Umumnya dipakai untuk administrasi kependudukan, sekolah, kerja, atau izin usaha.',
                'persyaratan' => $this->standarDenganPengantar(),
            ],
            [
                'nama_surat' => 'Surat Keterangan Tidak Mampu (SKTM)',
                'deskripsi' => 'Menerangkan kondisi ekonomi keluarga yang kurang mampu. Digunakan untuk beasiswa, KIP Kuliah, bantuan sosial, atau keringanan biaya.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Surat pernyataan tidak mampu bermaterai',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Bukti pendukung (slip gaji/rekening listrik atau kartu bantuan sosial, jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => false,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Usaha',
                'deskripsi' => 'Menerangkan bahwa pemohon memiliki usaha di wilayah desa. Digunakan untuk kredit UMKM, NPWP, izin usaha mikro, atau tender.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Foto tempat/kegiatan usaha',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'NPWP atau izin usaha (jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => false,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Penghasilan',
                'deskripsi' => 'Menerangkan besaran penghasilan pemohon atau orang tua. Digunakan untuk beasiswa, KPR, PPDB, atau administrasi perusahaan.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Slip gaji / surat keterangan penghasilan dari tempat kerja (jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => false,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Kelahiran',
                'deskripsi' => 'Menerangkan kelahiran bayi sebagai dasar pengurusan akta kelahiran di Dinas Dukcapil.',
                'persyaratan' => [
                    [
                        'nama' => 'Fotokopi KTP orang tua',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi Kartu Keluarga (KK)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pengantar RT/RW',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat keterangan lahir dari dokter/bidan/rumah sakit',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi KTP 2 orang saksi',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Kematian',
                'deskripsi' => 'Menerangkan bahwa seseorang telah meninggal dunia. Digunakan untuk akta kematian, klaim asuransi, warisan, atau pembaruan data kependudukan.',
                'persyaratan' => [
                    [
                        'nama' => 'Fotokopi KTP pelapor',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi Kartu Keluarga (KK)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pengantar RT/RW',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat keterangan kematian dari dokter/rumah sakit (jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi KTP almarhum/almarhumah (jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => false,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Belum Menikah',
                'deskripsi' => 'Menerangkan status belum menikah/lajang. Digunakan untuk syarat menikah di KUA, melamar kerja, atau administrasi lainnya.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Fotokopi akta cerai (khusus duda/janda, jika relevan)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Pengantar SKCK',
                'deskripsi' => 'Surat pengantar dari desa untuk mengurus Surat Keterangan Catatan Kepolisian (SKCK) di polsek/polres.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Pas foto terbaru (sesuai ketentuan polsek/polres)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Ahli Waris',
                'deskripsi' => 'Menerangkan ahli waris yang sah dari almarhum/almarhumah. Digunakan untuk pencairan dana, pembagian warisan, atau balik nama aset.',
                'persyaratan' => [
                    [
                        'nama' => 'Fotokopi KTP seluruh ahli waris',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi Kartu Keluarga (KK)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pengantar RT/RW',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi surat keterangan kematian / akta kematian',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pernyataan ahli waris bermaterai',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Kehilangan',
                'deskripsi' => 'Menerangkan kehilangan dokumen atau barang penting sebagai syarat penggantian dokumen (KTP, ijazah, STNK, dll.).',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Surat pernyataan kehilangan bermaterai',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi dokumen yang hilang (jika masih ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Tanah',
                'deskripsi' => 'Menerangkan status kepemilikan atau riwayat tanah di wilayah desa. Digunakan untuk jual beli, sertifikat, warisan, atau balik nama.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Bukti kepemilikan tanah (sertifikat / Letter C / girik / SPPT)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat jual beli atau keterangan riwayat tanah (jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Pengantar Nikah',
                'deskripsi' => 'Surat pengantar dari desa untuk pendaftaran pernikahan di KUA (formulir N1–N7 sesuai ketentuan setempat).',
                'persyaratan' => [
                    [
                        'nama' => 'Fotokopi KTP calon pengantin',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi Kartu Keluarga (KK)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pengantar RT/RW',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi akta kelahiran / ijazah',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Pas foto calon pengantin',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Domisili Usaha',
                'deskripsi' => 'Menerangkan alamat tempat usaha atau badan usaha di wilayah desa. Digunakan untuk legalitas usaha, perpajakan, atau izin usaha.',
                'persyaratan' => [
                    [
                        'nama' => 'Fotokopi KTP pemilik/penanggung jawab',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Fotokopi Kartu Keluarga (KK)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pengantar RT/RW',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Foto lokasi usaha',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Bukti sewa/kontrak atau kepemilikan tempat usaha (jika ada)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
            [
                'nama_surat' => 'Surat Keterangan Beda Identitas',
                'deskripsi' => 'Menerangkan perbedaan data identitas pada dokumen resmi (nama, tanggal lahir, dll.) agar dapat diluruskan di instansi terkait.',
                'persyaratan' => [
                    ...$this->standarDenganPengantar(),
                    [
                        'nama' => 'Fotokopi dokumen yang memuat perbedaan data (ijazah, sertifikat, polis, dll.)',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                    [
                        'nama' => 'Surat pernyataan beda identitas bermaterai',
                        'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                        'is_wajib' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Pola umum: KTP + KK (unggah wajib) + pengantar RT/RW (bawa kantor).
     *
     * @return list<array{nama: string, cara_pemenuhan: string, is_wajib: bool}>
     */
    private function standarDenganPengantar(): array
    {
        return [
            [
                'nama' => 'Fotokopi KTP pemohon',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
            [
                'nama' => 'Fotokopi Kartu Keluarga (KK)',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_UNGGAH,
                'is_wajib' => true,
            ],
            [
                'nama' => 'Surat pengantar RT/RW',
                'cara_pemenuhan' => JenisSuratPersyaratan::CARA_BAWA_KANTOR,
                'is_wajib' => true,
            ],
        ];
    }
}
