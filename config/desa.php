<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas Desa (Kop Surat)
    |--------------------------------------------------------------------------
    |
    | Digunakan pada template PDF surat keterangan (US-7.2).
    | Override via .env bila diperlukan tanpa mengubah kode.
    |
    */

    'nama_desa' => env('DESA_NAMA', 'Desa Wadon'),
    'kecamatan' => env('DESA_KECAMATAN', 'Kecamatan Contoh'),
    'kabupaten' => env('DESA_KABUPATEN', 'Kabupaten Contoh'),
    'provinsi' => env('DESA_PROVINSI', 'Jawa Barat'),
    'alamat_kantor' => env('DESA_ALAMAT', 'Jl. Desa No. 1'),
    'kode_pos' => env('DESA_KODE_POS', '40123'),
    'telepon' => env('DESA_TELEPON', '022-0000000'),

    /*
    |--------------------------------------------------------------------------
    | Penandatangan Surat
    |--------------------------------------------------------------------------
    */

    'penandatangan_nama' => env('DESA_PENANDATANGAN_NAMA', 'Kepala Desa'),
    'penandatangan_jabatan' => env('DESA_PENANDATANGAN_JABATAN', 'Kepala Desa'),

    /*
    |--------------------------------------------------------------------------
    | Kode Administrasi Nomor Surat (US-7.3)
    |--------------------------------------------------------------------------
    |
    | Pola: {kode_klasifikasi}/{urut}/{kode_desa}/{bulan romawi}/{tahun}
    | Contoh: 470/12/DS-WDN/VIII/2026
    | Urutan reset per tahun kalender; terpisah dari nomor_pengajuan.
    |
    */

    'kode_klasifikasi' => env('DESA_KODE_KLASIFIKASI', '470'),
    'kode_desa' => env('DESA_KODE', 'DS-WDN'),

    /*
    |--------------------------------------------------------------------------
    | Jam Kerja Kantor (US-7.5)
    |--------------------------------------------------------------------------
    |
    | Bukan time-picker bebas. Label disimpan ke surat_terbit.jam_kerja_label.
    | Validasi tanggal: Senin–Jumat (bukan Sabtu/Minggu), bukan libur nasional.
    |
    */

    'jam_kerja' => [
        'senin_kamis' => 'Senin–Kamis 08.00–16.00 WIB',
        'jumat' => 'Jumat 08.00–16.30 WIB',
    ],

    /*
    |--------------------------------------------------------------------------
    | Libur Nasional (YYYY-MM-DD, timezone Asia/Jakarta)
    |--------------------------------------------------------------------------
    |
    | Daftar tanggal tutup kantor. Perbarui tiap tahun sesuai SKB bersama.
    | Tanggal di daftar ini ditolak saat admin set tanggal pengambilan.
    |
    */

    'libur_nasional' => [
        '2026-01-01', // Tahun Baru
        '2026-01-16', // Isra Mikraj (perkiraan/tetapkan ulang tiap tahun)
        '2026-03-20', // Hari Raya Nyepi
        '2026-03-21', // Hari Suci Nyepi (cuti bersama — sesuaikan)
        '2026-04-03', // Wafat Isa Almasih (Good Friday)
        '2026-04-05', // Hari Paskah
        '2026-05-01', // Hari Buruh
        '2026-05-14', // Kenaikan Isa Almasih
        '2026-05-27', // Hari Raya Waisak
        '2026-06-01', // Hari Lahir Pancasila
        '2026-06-16', // Idul Adha (perkiraan — sesuaikan SKB)
        '2026-06-17', // Idul Adha cuti bersama (sesuaikan)
        '2026-07-07', // Tahun Baru Islam 1 Muharram (perkiraan)
        '2026-08-17', // Hari Kemerdekaan RI
        '2026-08-25', // Maulid Nabi (perkiraan)
        '2026-12-25', // Natal
    ],

];
