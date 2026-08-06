# Pengajuan Surat Keterangan - Panduan Pengguna

## Apa itu Pengajuan Surat?

Fitur ini memungkinkan warga yang sudah login mengajukan surat keterangan secara online. Anda memilih jenis surat, mengunggah dokumen persyaratan (KTP/KK) jika diperlukan, menjelaskan keperluan, lalu sistem memberikan **nomor pengajuan** untuk melacak status permohonan.

## Cara Menggunakan

### Mengajukan Surat Baru

1. Buka halaman **Pengajuan Surat** dari menu sidebar (setelah masuk sebagai warga).
2. Pilih **Jenis Surat** dari daftar dropdown (data dari master jenis surat desa).
3. Jika muncul area **Unggah Dokumen Persyaratan**, unggah fotokopi **KTP** dan/atau **KK** sesuai kebutuhan (format JPG/PNG/PDF, maks. 2 MB). Periksa pratinjau sebelum melanjutkan.
4. Isi **Keperluan** — jelaskan untuk apa surat tersebut dibutuhkan (misalnya: keperluan administrasi bank).
5. Klik **Kirim Pengajuan**.
6. Catat **nomor pengajuan** yang muncul (format contoh: `PJ-20260806-0001`). Nomor ini penting untuk mengecek status nanti.

> 💡 **Tips:** Sebelum mengajukan, buka **Persyaratan Dokumen** untuk melihat dokumen yang perlu disiapkan. Panduan lengkap unggah dokumen: [Unggah Dokumen Persyaratan](pengajuan-surat-dokumen.md).

### Mengajukan Surat Lain

Setelah pengajuan berhasil, klik **Ajukan Surat Lain** pada layar konfirmasi untuk mengisi formulir baru.

## FAQ

**Q: Apakah admin bisa mengajukan surat dari halaman ini?**
A: Tidak. Halaman ini khusus warga. Admin yang mencoba akses akan mendapat pesan tidak diizinkan (403).

**Q: Apakah saya harus login?**
A: Ya. Pengunjung tanpa akun akan diarahkan ke halaman masuk terlebih dahulu.

**Q: Bagaimana jika daftar jenis surat kosong?**
A: Hubungi admin desa — master data jenis surat belum diisi.

**Q: Apakah wajib mengunggah KTP/KK?**
A: Disarankan mengunggah sesuai persyaratan. Validasi wajib ketat akan diterapkan pada pembaruan berikutnya.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Pesan "Jenis surat wajib dipilih" | Pilih salah satu jenis surat dari dropdown sebelum kirim. |
| Pesan "Keperluan wajib diisi" | Isi kolom keperluan dengan penjelasan singkat. |
| Pesan format/ukuran file tidak valid | Lihat [panduan unggah dokumen](pengajuan-surat-dokumen.md). |
| Jenis surat tidak muncul di dropdown | Jenis surat mungkin diarsipkan admin; hubungi kantor desa. |
| Tombol Kirim tidak aktif | Belum ada jenis surat tersedia — tunggu admin menambahkan data. |
