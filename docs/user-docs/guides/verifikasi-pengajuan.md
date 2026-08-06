# Verifikasi Pengajuan - Panduan Pengguna

## Apa itu Verifikasi Pengajuan?

Fitur ini membantu **admin/petugas desa** memeriksa pengajuan surat keterangan yang sudah dikirim warga. Petugas dapat melihat daftar pengajuan yang menunggu pemeriksaan, membuka detail lengkap, dan mempratinjau dokumen KTP/KK tanpa mencetak berkas fisik.

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
2. Halaman detail menampilkan:
   - Data warga (nama, NIK)
   - Jenis surat dan tanggal pengajuan
   - Status saat ini
   - **Keperluan** pengajuan
   - Dokumen persyaratan (KTP/KK)

### Memeriksa Dokumen

1. Di bagian **Dokumen Persyaratan**, pratinjau gambar (JPG/PNG) atau PDF ditampilkan langsung di halaman.
2. Jika pratinjau tidak tersedia (file rusak atau format tidak didukung), klik **Unduh Dokumen** untuk membuka berkas di perangkat Anda.

### Tombol Setujui dan Tolak

Di bagian bawah halaman detail terdapat tombol **Setujui** dan **Tolak**. Tombol ini menandakan langkah keputusan verifikasi; proses persetujuan/penolakan lengkap (termasuk catatan admin dan pencatatan log) akan tersedia pada tahap fitur berikutnya.

## FAQ

**Q: Mengapa daftar saya kosong?**
A: Kemungkinan belum ada pengajuan dengan status **Diajukan**, atau filter status sedang diubah ke status lain yang tidak memiliki data.

**Q: Apakah warga bisa melihat halaman verifikasi?**
A: Tidak. Halaman ini khusus admin/petugas desa.

**Q: Dokumen tidak bisa dipratinjau, apa yang harus dilakukan?**
A: Gunakan tombol **Unduh Dokumen** untuk memeriksa berkas secara manual.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Halaman verifikasi tidak muncul di menu | Pastikan Anda login sebagai admin, bukan warga |
| Pratinjau dokumen kosong | Unduh dokumen; file mungkin hilang atau format tidak didukung |
| Pengajuan tidak muncul di daftar default | Cek filter status — ubah ke **Diajukan** |
