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
    | Kode Administrasi Nomor Surat
    |--------------------------------------------------------------------------
    |
    | Pola: {kode_klasifikasi}/{urut}/DS-WDN/{bulan romawi}/{tahun}
    | Sesuai contoh US-7.3; diimplementasikan bersama US-7.2 karena wajib di PDF.
    |
    */

    'kode_klasifikasi' => env('DESA_KODE_KLASIFIKASI', '470'),
    'kode_desa' => env('DESA_KODE', 'DS-WDN'),

];
