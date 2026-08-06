# Kelola Jenis Surat - Panduan Pengguna

## Apa itu Jenis Surat?
Jenis surat adalah daftar master jenis surat keterangan yang dikelola admin desa (misalnya Domisili, Tidak Mampu). Setiap jenis punya deskripsi (opsional) dan daftar persyaratan dokumen (wajib). Data ini menjadi acuan warga saat mengajukan surat.

## Cara Menggunakan

### Melihat daftar jenis surat

1. Masuk sebagai **admin**.
2. Di menu samping, klik **Jenis Surat**.
3. Anda akan melihat tabel daftar jenis surat (atau pesan kosong jika belum ada data).

> 💡 **Tips:** Gunakan kolom pencarian untuk mencari berdasarkan nama, deskripsi, atau persyaratan.

### Menambah jenis surat

1. Buka halaman **Jenis Surat**.
2. Klik **Tambah Jenis Surat**.
3. Isi form:
   - **Nama Surat** (wajib): contoh `Surat Keterangan Domisili`
   - **Deskripsi** (opsional): ringkasan kegunaan surat
   - **Persyaratan Dokumen** (wajib): daftar dokumen, satu baris per item
4. Klik **Simpan**.

### Mengubah jenis surat

1. Di daftar, klik **Ubah** pada baris yang ingin diedit.
2. Perbarui field yang diperlukan.
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
3. Konfirmasi di dialog. Data dihapus selamanya dan tidak bisa dipulihkan.

> ⚠️ **Peringatan:** Hapus permanen hanya tersedia dari arsip. Nama surat yang masih diarsip tidak bisa dipakai ulang sampai data dipulihkan (lalu diganti namanya) atau dihapus permanen.

## FAQ

**Q: Mengapa saya mendapat halaman 403?**
A: Halaman ini hanya untuk role admin. Akun warga tidak dapat mengaksesnya.

**Q: Mengapa nama surat ditolak?**
A: Nama surat wajib diisi dan tidak boleh sama dengan nama yang sudah ada (termasuk yang masih di arsip).

**Q: Apakah deskripsi wajib?**
A: Tidak. Deskripsi boleh dikosongkan. Persyaratan dokumen wajib diisi.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tidak menemukan menu Jenis Surat | Pastikan Anda login sebagai admin, lalu refresh halaman |
| Pesan "Nama surat sudah digunakan" | Gunakan nama berbeda, atau cek arsip lalu pulihkan/hapus permanen |
| Pesan "Persyaratan dokumen wajib diisi" | Isi daftar persyaratan sebelum menyimpan |
| Hasil pencarian kosong | Hapus kata kunci pencarian atau coba kata yang lebih pendek |
