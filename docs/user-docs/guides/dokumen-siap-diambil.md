# Dokumen Siap Diambil - Panduan Pengguna

## Apa itu Dokumen Siap Diambil?

Setelah surat PDF dibuat (status **Diproses**), petugas desa menetapkan **tanggal pengambilan** sesuai jam kerja kantor lewat menu **Surat Diproses**. Status berubah menjadi **Siap Diambil**, dan warga mendapat notifikasi kapan datang mengambil surat (beserta QR di PDF).

> Panduan lengkap menu baru: [Surat Diproses & Siap Diambil](surat-diproses.md).

## Cara Menggunakan

### Admin — Menandai Dokumen Siap Diambil

1. Buka **Surat Diproses** (sidebar, di bawah Daftar Pengajuan Surat).
2. Klik **Lihat Detail** pada surat yang sedang diproses.
3. Pilih **Tanggal Pengambilan** (hari ini atau ke depan; bukan Sabtu/Minggu/libur).
4. Sistem menampilkan **Jam Kerja** otomatis:
   - Senin–Kamis: 08.00–16.00 WIB
   - Jumat: 08.00–16.30 WIB
5. Setelah tanggal valid, klik **Siap Diambil**.
6. Warga menerima notifikasi berisi jenis surat, nomor pengajuan, tanggal, dan jam kerja.

> 💡 **Tips:** Surat yang baru disetujui masuk menu **Surat Diproses**, bukan Daftar Pengajuan Surat.

### Warga — Melihat Tanggal Pengambilan

1. Buka **Status & Riwayat Pengajuan**.
2. Lihat kolom **Status** (Siap Diambil) dan kolom **Pengambilan** (tanggal + jam kerja).
3. Baca notifikasi di ikon lonceng untuk detail yang sama.

### Admin — Menyelesaikan Pengambilan (Scan QR)

Setelah status **Siap Diambil**, petugas scan QR pada surat (lihat panduan Scan QR Pengambilan). Scan pertama sukses mengubah status menjadi **Selesai** dan QR tidak bisa dipakai lagi.

## FAQ

**Q: Bisa pilih jam pengambilan bebas?**  
A: Tidak. Jam mengikuti jam kerja kantor pemerintah yang sudah ditetapkan sistem.

**Q: Kenapa tanggal Sabtu/Minggu tidak bisa?**  
A: Kantor tutup. Pilih hari kerja Senin–Jumat yang bukan libur nasional.

**Q: Apakah unduh PDF membuat QR baru?**  
A: Tidak. QR tetap yang sama; hanya scan pertama yang berhasil.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Panel Siap Diambil tidak muncul | Pastikan status **Diproses** dan buka dari menu **Surat Diproses**. |
| Tombol tetap nonaktif | Pilih tanggal hari kerja yang valid (bukan masa lalu / weekend / libur). |
| Tidak ada notifikasi | Cek ikon lonceng; pastikan admin sudah menekan **Siap Diambil**. |
