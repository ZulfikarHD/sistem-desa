# Rekap Pengajuan - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 11 dari 15 — Cara memfilter rekap dan mengekspor laporan CSV.

## Apa itu Rekap Pengajuan?

Halaman untuk petugas/admin desa melihat seluruh pengajuan surat dalam satu tabel, memfilter sesuai kebutuhan, dan mengunduh laporan CSV — pengganti pencatatan buku register manual.

## Cara Menggunakan

### Membuka halaman Rekap

1. Masuk sebagai **admin/petugas desa**.
2. Di menu samping, klik **Rekap Pengajuan**.
3. Anda akan melihat:
   - **Ringkasan** di atas (Total, Diajukan, Disetujui, Diproses, Siap Diambil, Selesai, Ditolak)
   - **Filter** jenis surat, status, dan rentang tanggal
   - **Tabel** pengajuan (nomor, nama warga, jenis surat, tanggal, status, admin verifikator)
   - **Tombol Lihat Detail** per baris untuk melihat timeline proses pengajuan

> 💡 **Tips:** Angka ringkasan mengikuti filter jenis surat dan tanggal, tetapi **tidak** berubah saat Anda memfilter status di tabel — supaya ringkasan tetap berguna sebagai gambaran volume.

### Memfilter data

1. Pilih **Jenis Surat** (atau biarkan "Semua jenis surat").
2. Pilih **Status** (atau "Semua status").
3. Isi **Tanggal Dari** dan/atau **Tanggal Sampai**.
4. Tabel dan ringkasan akan diperbarui otomatis.
5. Klik **Reset Filter** untuk menampilkan semua data lagi.

### Mengekspor CSV

1. Atur filter sesuai laporan yang dibutuhkan.
2. Klik tombol **Export CSV**.
3. File akan terunduh (nama mirip `rekap-pengajuan-YYYYMMDD-HHMMSS.csv`).
4. Buka di Excel atau spreadsheet — encoding UTF-8 sudah disesuaikan agar teks Indonesia tampil rapi.

Isi kolom CSV: Nomor Pengajuan, Nama Warga, Jenis Surat, Tanggal Pengajuan, Status, Admin Verifikator.

## FAQ

**Q: Mengapa warga tidak bisa membuka halaman ini?**
A: Rekap hanya untuk admin/petugas desa. Warga memakai menu Riwayat Pengajuan.

**Q: Apakah export mengambil semua data di database?**
A: Tidak. Export hanya data yang cocok dengan filter yang sedang aktif.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Halaman kosong / tidak ada data | Cek filter; klik **Reset Filter** |
| Pesan "Tanggal sampai harus sama atau setelah tanggal dari" | Perbaiki rentang tanggal |
| CSV aneh di Excel | Pastikan membuka file yang baru diunduh; file memakai UTF-8 dengan BOM |
| Akses ditolak (403) | Login dengan akun admin, bukan warga |
