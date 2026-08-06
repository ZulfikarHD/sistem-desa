# Dokumen Siap Diambil - Panduan Pengguna

## Apa itu Dokumen Siap Diambil?

Setelah surat PDF dibuat (status **Diproses**), petugas desa menetapkan **tanggal pengambilan** sesuai jam kerja kantor. Status berubah menjadi **Siap Diambil**, dan warga mendapat notifikasi kapan datang mengambil surat (beserta QR di PDF).

## Cara Menggunakan

### Admin — Menandai Dokumen Siap Diambil

1. Buka **Verifikasi Pengajuan**.
2. Ubah filter status ke **Diproses**, lalu buka detail pengajuan.
3. Pada panel **Dokumen Siap Diambil**, pilih **Tanggal Pengambilan**.
4. Sistem menampilkan **Jam Kerja** otomatis:
   - Senin–Kamis: 08.00–16.00 WIB
   - Jumat: 08.00–16.30 WIB
   - Sabtu–Minggu & libur nasional: tutup (tidak bisa dipilih)
5. Setelah tanggal valid, tombol **Dokumen Siap Diambil** aktif. Klik tombol tersebut.
6. Warga otomatis menerima notifikasi berisi jenis surat, status siap diambil, tanggal, dan jam kerja.

> 💡 **Tips:** Filter daftar verifikasi default-nya **Diajukan**. Ganti ke **Diproses** untuk menemukan surat yang sudah punya PDF.

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
| Panel Dokumen Siap Diambil tidak muncul | Pastikan status **Diproses** dan PDF sudah digenerate (setelah setujui). |
| Tombol tetap nonaktif | Pilih tanggal hari kerja yang valid (bukan masa lalu / weekend / libur). |
| Tidak ada notifikasi | Cek ikon lonceng; pastikan admin sudah menekan **Dokumen Siap Diambil**. |
