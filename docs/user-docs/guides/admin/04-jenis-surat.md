# Kelola Jenis Surat - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 4 dari 17 — Atur master data jenis surat sebelum warga bisa mengajukan.

## Apa itu Jenis Surat?

Jenis surat adalah daftar master jenis surat keterangan yang dikelola admin desa (misalnya Domisili, Tidak Mampu). Setiap jenis punya deskripsi (opsional) dan **daftar persyaratan terstruktur** — satu baris per syarat, dengan pilihan bagaimana warga memenuhinya (unggah di aplikasi, bawa ke kantor, atau hanya informasi).

## Cara Menggunakan

### Melihat daftar jenis surat

1. Masuk sebagai **admin**.
2. Di menu samping, klik **Jenis Surat**.
3. Anda akan melihat tabel daftar jenis surat (atau pesan kosong jika belum ada data).

> 💡 **Tips:** Gunakan kolom pencarian untuk mencari berdasarkan nama, deskripsi, atau persyaratan.

### Menambah jenis surat

1. Buka halaman **Jenis Surat**.
2. Klik **Tambah Jenis Surat**.
3. Isi **Nama Surat** (wajib) dan **Deskripsi** (opsional).
4. Di bagian **Persyaratan dokumen**, isi minimal satu baris:
   - **Nama syarat** — contoh: `Fotokopi KTP`
   - **Bagaimana warga memenuhi?**
     - **Unggah di aplikasi** — warga kirim foto/scan lewat HP
     - **Bawa ke kantor desa** — berkas fisik, tanpa tombol unggah
     - **Tidak perlu file** — hanya catatan/informasi
   - Jika pilih unggah: **Wajib** (default) atau **Boleh dikosongkan**
5. Lihat **Pratinjau untuk warga** agar badge cocok dengan yang dilihat warga.
6. (Opsional) Klik **Template KTP + KK + Pengantar RT** untuk mengisi 3 baris umum sekaligus.
7. Klik **Simpan**.

### Mengubah jenis surat

1. Di daftar, klik **Ubah** pada baris yang ingin diedit.
2. Perbarui nama, deskripsi, atau baris persyaratan (tambah / hapus / naik-turun urutan).
3. Klik **Simpan**.

### Mengarsipkan (soft delete)

1. Di daftar aktif, klik **Arsipkan**.
2. Konfirmasi jika diminta.
3. Data hilang dari daftar aktif, tetapi masih bisa dipulihkan dari arsip.

### Memulihkan dari arsip

1. Aktifkan sakelar **Tampilkan arsip**.
2. Klik **Pulihkan** pada baris yang diinginkan.
3. Nonaktifkan sakelar arsip untuk melihat data di daftar aktif lagi.

### Menghapus permanen (hard delete)

1. Aktifkan **Tampilkan arsip**.
2. Klik **Hapus Permanen**.
3. Konfirmasi di dialog. Data dihapus selamanya (beserta baris persyaratannya) dan tidak bisa dipulihkan.

> ⚠️ **Peringatan:** Hapus permanen hanya tersedia dari arsip. Jenis surat yang masih aktif tidak bisa langsung dihapus permanen.

## FAQ

**Q: Mengapa saya mendapat halaman 403?**
A: Halaman ini hanya untuk role admin. Akun warga tidak dapat mengaksesnya.

**Q: Mengapa nama surat ditolak?**
A: Nama surat tidak boleh sama dengan nama yang sudah ada (termasuk yang masih di arsip).

**Q: Apa bedanya Wajib dan Boleh dikosongkan?**
A: **Wajib** harus diunggah sebelum pengajuan dikirim. **Boleh dikosongkan** untuk dokumen opsional / “jika ada”.

**Q: Kapan memakai Bawa ke kantor desa?**
A: Untuk berkas fisik seperti pengantar RT/RW yang tidak perlu diunggah online.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tidak menemukan menu Jenis Surat | Pastikan Anda login sebagai admin, lalu refresh halaman |
| Pesan "Nama surat sudah digunakan" | Gunakan nama berbeda, atau cek arsip lalu pulihkan/hapus permanen |
| Pesan "Nama syarat wajib diisi" / "Minimal satu persyaratan" | Isi nama pada setiap baris dan pastikan ada minimal satu syarat |
| Pilihan Wajib tidak muncul | Pastikan **Cara memenuhi** = Unggah di aplikasi |
