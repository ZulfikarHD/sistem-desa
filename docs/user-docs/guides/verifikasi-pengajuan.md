# Verifikasi Pengajuan - Panduan Pengguna

## Apa itu Verifikasi Pengajuan?

Fitur ini membantu **admin/petugas desa** memeriksa pengajuan surat keterangan yang sudah dikirim warga. Petugas dapat melihat daftar pengajuan yang menunggu pemeriksaan, membuka detail lengkap, mempratinjau dokumen KTP/KK, lalu **menyetujui** atau **menolak** pengajuan dengan pencatatan log audit.

## Cara Menggunakan

### Melihat Daftar Pengajuan Menunggu Verifikasi

1. Masuk ke sistem sebagai **admin/petugas desa**.
2. Di menu samping, klik **Verifikasi Pengajuan**.
3. Halaman menampilkan pengajuan dengan status **Diajukan** secara default.
4. Tabel menampilkan nomor, nama warga, jenis surat, dan tanggal pengajuan.
5. Gunakan **Filter Status** untuk status lain (Disetujui, Diproses, Siap Diambil, Selesai, Ditolak).

> 💡 **Tips:** Fokuskan pemeriksaan pada filter **Diajukan** — itulah antrean yang perlu ditindaklanjuti.

### Membuka Detail Pengajuan

1. Dari daftar verifikasi, **klik baris** pengajuan yang ingin diperiksa.
2. Status tetap **Diajukan** saat halaman detail dibuka (tidak berubah otomatis).
3. Halaman detail menampilkan data warga, jenis surat, keperluan, dan dokumen persyaratan.

### Memeriksa Dokumen

1. Di bagian **Dokumen Persyaratan**, pratinjau gambar atau PDF ditampilkan langsung.
2. Jika pratinjau tidak tersedia, klik **Unduh Dokumen**.

### Menyetujui Pengajuan

1. Klik **Setujui**.
2. Sistem mencatat **Disetujui**, lalu otomatis lanjut ke **Diproses** (persiapan surat).
3. Pengajuan hilang dari daftar filter **Diajukan**.
4. Warga menerima notifikasi status.

### Menolak Pengajuan

1. Klik **Tolak**.
2. Isi **Alasan Penolakan** (wajib).
3. Klik **Tolak Pengajuan**.
4. Status menjadi **Ditolak** (tidak masuk Diproses).
5. Warga dapat melihat alasan di riwayat pengajuan.

> 💡 **Tips:** Tulis alasan yang jelas agar warga dapat memperbaiki dokumen atau mengajukan ulang.

## FAQ

**Q: Mengapa daftar saya kosong?**  
A: Belum ada pengajuan **Diajukan**, atau filter status sedang diubah.

**Q: Mengapa status tidak jadi Diproses saat saya buka detail?**  
A: Diproses sekarang berarti surat sedang disiapkan setelah disetujui, bukan “sedang dibaca admin.”

**Q: Setelah setujui, kenapa status akhirnya Diproses?**  
A: Setelah verifikasi OK, sistem otomatis lanjut ke tahap persiapan surat.

**Q: Apakah saya bisa menolak tanpa alasan?**  
A: Tidak. Alasan penolakan wajib diisi.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Halaman verifikasi tidak muncul di menu | Login sebagai admin, bukan warga |
| Pratinjau dokumen kosong | Unduh dokumen; file mungkin hilang |
| Tombol Setujui/Tolak tidak muncul | Status bukan Diajukan lagi |
| Pengajuan tidak muncul di daftar default | Set filter ke **Diajukan** |

Lihat juga: [Migrasi Alur Status](migrasi-alur-status.md).
