# Verifikasi Pengajuan - Panduan Pengguna

## Apa itu Verifikasi Pengajuan?

Fitur ini membantu **admin/petugas desa** memeriksa pengajuan surat keterangan yang sudah dikirim warga. Petugas dapat melihat daftar pengajuan yang menunggu pemeriksaan, membuka detail lengkap, mempratinjau dokumen KTP/KK, lalu **menyetujui** atau **menolak** pengajuan dengan pencatatan log audit.

## Cara Menggunakan

### Melihat Daftar Pengajuan Menunggu Verifikasi

1. Masuk ke sistem sebagai **admin/petugas desa**.
2. Di menu samping, klik **Verifikasi Pengajuan**.
3. Halaman menampilkan pengajuan dengan status **Diajukan** secara default.
4. Tabel menampilkan:
   - **Nomor Pengajuan**
   - **Nama Warga**
   - **Jenis Surat**
   - **Tanggal Pengajuan**
5. Gunakan **Filter Status** jika ingin melihat pengajuan dengan status lain (Diproses, Disetujui, Ditolak).

> 💡 **Tips:** Fokuskan pemeriksaan pada filter **Diajukan** — itulah antrean yang perlu ditindaklanjuti terlebih dulu.

### Membuka Detail Pengajuan

1. Dari daftar verifikasi, **klik baris** pengajuan yang ingin diperiksa.
2. Status pengajuan otomatis berubah dari **Diajukan** menjadi **Diproses** saat Anda membuka detail untuk pertama kali.
3. Halaman detail menampilkan:
   - Data warga (nama, NIK)
   - Jenis surat dan tanggal pengajuan
   - Status saat ini
   - **Keperluan** pengajuan
   - Dokumen persyaratan (KTP/KK)

### Memeriksa Dokumen

1. Di bagian **Dokumen Persyaratan**, pratinjau gambar (JPG/PNG) atau PDF ditampilkan langsung di halaman.
2. Jika pratinjau tidak tersedia (file rusak atau format tidak didukung), klik **Unduh Dokumen** untuk membuka berkas di perangkat Anda.

### Menyetujui Pengajuan

1. Setelah memeriksa dokumen, klik tombol **Setujui** di bagian bawah halaman detail.
2. Konfirmasi dialog yang muncul.
3. Pengajuan berstatus **Disetujui** dan hilang dari daftar filter **Diajukan**.
4. Anda diarahkan kembali ke daftar verifikasi.

### Menolak Pengajuan

1. Klik tombol **Tolak** di bagian bawah halaman detail.
2. Isi **Alasan Penolakan** pada formulir yang muncul (wajib diisi).
3. Klik **Tolak Pengajuan** untuk mengonfirmasi.
4. Pengajuan berstatus **Ditolak** dan hilang dari daftar filter **Diajukan**.
5. Warga dapat melihat alasan penolakan saat fitur riwayat pengajuan tersedia.

> 💡 **Tips:** Tulis alasan penolakan yang jelas dan spesifik agar warga dapat memperbaiki dokumen atau mengajukan ulang.

## FAQ

**Q: Mengapa daftar saya kosong?**
A: Kemungkinan belum ada pengajuan dengan status **Diajukan**, atau filter status sedang diubah ke status lain yang tidak memiliki data.

**Q: Apakah warga bisa melihat halaman verifikasi?**
A: Tidak. Halaman ini khusus admin/petugas desa.

**Q: Dokumen tidak bisa dipratinjau, apa yang harus dilakukan?**
A: Gunakan tombol **Unduh Dokumen** untuk memeriksa berkas secara manual.

**Q: Mengapa status berubah menjadi Diproses saat saya buka detail?**
A: Ini menandakan pengajuan sedang diperiksa petugas. Status selanjutnya adalah Disetujui atau Ditolak setelah Anda mengambil keputusan.

**Q: Apakah saya bisa menolak tanpa alasan?**
A: Tidak. Alasan penolakan wajib diisi agar warga memahami keputusan Anda.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Halaman verifikasi tidak muncul di menu | Pastikan Anda login sebagai admin, bukan warga |
| Pratinjau dokumen kosong | Unduh dokumen; file mungkin hilang atau format tidak didukung |
| Pengajuan tidak muncul di daftar default | Cek filter status — ubah ke **Diajukan** |
| Tombol Setujui/Tolak tidak muncul | Pengajuan mungkin sudah disetujui/ditolak; cek filter status |
| Form penolakan menolak submit | Pastikan alasan penolakan diisi minimal 5 karakter |
