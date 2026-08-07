<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use Illuminate\Database\Seeder;

class JenisSuratSeeder extends Seeder
{
    /**
     * Master data jenis surat keterangan desa (Indonesia) beserta persyaratan umum.
     * Persyaratan memakai kata kunci KTP / Kartu Keluarga agar slot unggah form pengajuan terdeteksi.
     */
    public function run(): void
    {
        foreach ($this->data() as $item) {
            JenisSurat::query()->updateOrCreate(
                ['nama_surat' => $item['nama_surat']],
                [
                    'deskripsi' => $item['deskripsi'],
                    'persyaratan_dokumen' => $item['persyaratan_dokumen'],
                ],
            );
        }
    }

    /**
     * @return list<array{nama_surat: string, deskripsi: string, persyaratan_dokumen: string}>
     */
    private function data(): array
    {
        return [
            [
                'nama_surat' => 'Surat Keterangan Domisili',
                'deskripsi' => 'Menerangkan bahwa pemohon bertempat tinggal di wilayah desa. Umumnya dipakai untuk administrasi kependudukan, sekolah, kerja, atau izin usaha.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Tidak Mampu (SKTM)',
                'deskripsi' => 'Menerangkan kondisi ekonomi keluarga yang kurang mampu. Digunakan untuk beasiswa, KIP Kuliah, bantuan sosial, atau keringanan biaya.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Surat pernyataan tidak mampu bermaterai',
                    '- Bukti pendukung (slip gaji/rekening listrik atau kartu bantuan sosial, jika ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Usaha',
                'deskripsi' => 'Menerangkan bahwa pemohon memiliki usaha di wilayah desa. Digunakan untuk kredit UMKM, NPWP, izin usaha mikro, atau tender.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Foto tempat/kegiatan usaha',
                    '- NPWP atau izin usaha (jika ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Penghasilan',
                'deskripsi' => 'Menerangkan besaran penghasilan pemohon atau orang tua. Digunakan untuk beasiswa, KPR, PPDB, atau administrasi perusahaan.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Slip gaji / surat keterangan penghasilan dari tempat kerja (jika ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Kelahiran',
                'deskripsi' => 'Menerangkan kelahiran bayi sebagai dasar pengurusan akta kelahiran di Dinas Dukcapil.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP orang tua',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Surat keterangan lahir dari dokter/bidan/rumah sakit',
                    '- Fotokopi KTP 2 orang saksi',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Kematian',
                'deskripsi' => 'Menerangkan bahwa seseorang telah meninggal dunia. Digunakan untuk akta kematian, klaim asuransi, warisan, atau pembaruan data kependudukan.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pelapor',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Surat keterangan kematian dari dokter/rumah sakit (jika ada)',
                    '- Fotokopi KTP almarhum/almarhumah (jika ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Belum Menikah',
                'deskripsi' => 'Menerangkan status belum menikah/lajang. Digunakan untuk syarat menikah di KUA, melamar kerja, atau administrasi lainnya.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Fotokopi akta cerai (khusus duda/janda, jika relevan)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Pengantar SKCK',
                'deskripsi' => 'Surat pengantar dari desa untuk mengurus Surat Keterangan Catatan Kepolisian (SKCK) di polsek/polres.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Pas foto terbaru (sesuai ketentuan polsek/polres)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Ahli Waris',
                'deskripsi' => 'Menerangkan ahli waris yang sah dari almarhum/almarhumah. Digunakan untuk pencairan dana, pembagian warisan, atau balik nama aset.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP seluruh ahli waris',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Fotokopi surat keterangan kematian / akta kematian',
                    '- Surat pernyataan ahli waris bermaterai',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Kehilangan',
                'deskripsi' => 'Menerangkan kehilangan dokumen atau barang penting sebagai syarat penggantian dokumen (KTP, ijazah, STNK, dll.).',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Surat pernyataan kehilangan bermaterai',
                    '- Fotokopi dokumen yang hilang (jika masih ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Tanah',
                'deskripsi' => 'Menerangkan status kepemilikan atau riwayat tanah di wilayah desa. Digunakan untuk jual beli, sertifikat, warisan, atau balik nama.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Bukti kepemilikan tanah (sertifikat / Letter C / girik / SPPT)',
                    '- Surat jual beli atau keterangan riwayat tanah (jika ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Pengantar Nikah',
                'deskripsi' => 'Surat pengantar dari desa untuk pendaftaran pernikahan di KUA (formulir N1–N7 sesuai ketentuan setempat).',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP calon pengantin',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Fotokopi akta kelahiran / ijazah',
                    '- Pas foto calon pengantin',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Domisili Usaha',
                'deskripsi' => 'Menerangkan alamat tempat usaha atau badan usaha di wilayah desa. Digunakan untuk legalitas usaha, perpajakan, atau izin usaha.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemilik/penanggung jawab',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Foto lokasi usaha',
                    '- Bukti sewa/kontrak atau kepemilikan tempat usaha (jika ada)',
                ]),
            ],
            [
                'nama_surat' => 'Surat Keterangan Beda Identitas',
                'deskripsi' => 'Menerangkan perbedaan data identitas pada dokumen resmi (nama, tanggal lahir, dll.) agar dapat diluruskan di instansi terkait.',
                'persyaratan_dokumen' => implode("\n", [
                    '- Fotokopi KTP pemohon',
                    '- Fotokopi Kartu Keluarga (KK)',
                    '- Surat pengantar RT/RW',
                    '- Fotokopi dokumen yang memuat perbedaan data (ijazah, sertifikat, polis, dll.)',
                    '- Surat pernyataan beda identitas bermaterai',
                ]),
            ],
        ];
    }
}
