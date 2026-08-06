# Proteksi Akses Berdasarkan Role - Panduan Pengguna

## Apa itu Proteksi Role?

Sistem membatasi halaman sesuai jenis akun Anda:

- **Warga** hanya boleh membuka **Dashboard Warga**
- **Admin / Petugas Desa** hanya boleh membuka **Dashboard Admin**
- Halaman **Profil / Pengaturan** bisa dipakai kedua jenis akun
- Halaman **Persyaratan Dokumen** (`/persyaratan-dokumen`) **bisa dibuka tanpa login** (informasi publik)

Jika Anda mencoba membuka halaman yang bukan untuk role Anda, sistem menampilkan halaman **403 (Akses Ditolak)**.

## Cara Menggunakan

### Masuk sesuai role

1. Buka halaman **Login**
2. Isi **Email** dan **Password**
3. Klik **Masuk**
4. Anda otomatis diarahkan ke dashboard yang sesuai role akun

### Jika belum login

1. Buka URL yang dilindungi (misalnya `/dashboard` atau `/admin/dashboard`) tanpa login
2. Sistem mengarahkan Anda ke halaman **Login**
3. Setelah berhasil masuk, lanjutkan ke menu yang tersedia untuk role Anda

### Jika membuka halaman yang salah role

1. Login sebagai warga, lalu coba buka URL admin (atau sebaliknya)
2. Sistem menampilkan **403 / Forbidden**
3. Kembali ke dashboard Anda lewat menu aplikasi, atau buka URL dashboard yang benar

> 💡 **Tips:** Jangan bagikan kredensial admin ke warga. Setiap akun punya batas akses sendiri.

## FAQ

**Q: Saya admin, kenapa `/dashboard` (Dashboard Warga) ditolak?**  
A: Benar. Dashboard Warga khusus role warga. Admin memakai **Dashboard Admin** di `/admin/dashboard`.

**Q: Saya warga, apakah bisa membuka Pengaturan Profil?**  
A: Bisa. Halaman profil/pengaturan dipakai bersama oleh warga dan admin.

**Q: Apa bedanya 403 dengan diarahkan ke login?**  
A: Belum login → diarahkan ke login. Sudah login tapi role salah → 403.

**Q: Apakah Persyaratan Dokumen juga dilindungi login?**  
A: Tidak. `/persyaratan-dokumen` bersifat informasi publik dan bisa dibuka tanpa akun.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Selalu ke halaman login | Pastikan sudah login; sesi mungkin habis — login ulang |
| Mendapat 403 di dashboard sendiri | Pastikan role akun benar (warga vs admin); hubungi petugas desa jika salah |
| Tidak bisa buka halaman admin | Hanya akun ber-role admin yang diizinkan |
