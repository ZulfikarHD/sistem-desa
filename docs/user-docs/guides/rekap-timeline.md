# Detail Timeline Rekap Pengajuan - Panduan Pengguna

## Apa itu Detail Timeline Rekap?
Halaman untuk petugas/admin melihat **riwayat proses lengkap** satu pengajuan surat — mirip pelacakan paket — mulai dari diajukan sampai selesai (atau ditolak).

## Cara Menggunakan

### Membuka detail dari Rekap

1. Masuk sebagai **admin/petugas desa**
2. Buka menu **Rekap Pengajuan**
3. Pada baris pengajuan yang ingin diperiksa, klik **Lihat Detail**
4. Anda akan melihat:
   - **Ringkasan Pengajuan** (nama, NIK, jenis surat, nomor pengajuan, nomor surat resmi jika ada, status)
   - **Timeline Proses** (hanya tahap yang sudah terjadi)

### Membaca Timeline Proses

Setiap titik timeline menampilkan:

- Ikon berwarna sesuai jenis aksi
- Keterangan aksi (misalnya disetujui, ditolak, siap diambil)
- Waktu dalam zona **WIB**
- Nama petugas atau **Sistem**

Urutan umum (jika sudah terjadi):

1. Pengajuan diterima oleh sistem
2. Disetujui & surat digenerate *(atau Ditolak — maka timeline berhenti di sini)*
3. Dokumen siap diambil (tanggal & jam kerja)
4. Dokumen telah diambil (QR dipindai)

> 💡 **Tips:** Tahap yang belum terjadi **tidak ditampilkan** sama sekali (bukan abu-abu). Jika pengajuan ditolak, Anda tidak akan melihat poin siap diambil atau selesai.

### Mengunduh PDF Surat

1. Jika surat sudah digenerate (status diproses / siap diambil / selesai), klik **Unduh PDF Surat**
2. Jika belum ada file surat, tombol unduh tidak muncul

### Kembali ke daftar

Klik **Kembali ke Rekap** untuk kembali ke tabel filter rekap.

## FAQ

**Q: Bisakah warga membuka halaman ini?**  
A: Tidak. Detail timeline hanya untuk admin/petugas desa.

**Q: Mengapa ada catatan “waktu estimasi” pada siap diambil?**  
A: Data lama sebelum pencatatan `siap_diambil_at` memakai perkiraan waktu dari pembaruan terakhir surat.

**Q: Mengapa nomor surat resmi menampilkan “—”?**  
A: Pengajuan belum disetujui / PDF belum digenerate (misalnya masih diajukan atau ditolak).

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tombol Lihat Detail tidak ada | Pastikan Anda di halaman Rekap Pengajuan (bukan daftar verifikasi) |
| Timeline hanya 1 poin | Pengajuan masih berstatus diajukan — belum diverifikasi |
| Unduh PDF tidak muncul | Surat belum digenerate atau file PDF hilang dari penyimpanan |
| Akses ditolak (403) | Login dengan akun admin, bukan warga |
