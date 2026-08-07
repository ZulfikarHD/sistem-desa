# Proteksi Akses Berdasarkan Role - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 15 dari 17 — Memahami batasan akses akun admin.

## Apa itu Proteksi Role?

Sistem membatasi halaman sesuai jenis akun. Sebagai **admin/petugas desa**, Anda hanya boleh membuka **Dashboard Admin** dan halaman-halaman admin. Halaman khusus warga tidak bisa diakses.

- **Admin** hanya boleh membuka **Dashboard Admin** dan semua menu admin
- Halaman **Profil / Pengaturan** bisa dipakai kedua jenis akun
- Halaman **Persyaratan Dokumen** bisa dibuka siapa saja (informasi publik)

Jika Anda mencoba membuka halaman warga, sistem menampilkan halaman **403 (Akses Ditolak)**.

## Cara Menggunakan

### Masuk sesuai role admin

1. Buka halaman **Login**.
2. Isi **Email** dan **Password** akun admin.
3. Klik **Masuk**.
4. Anda otomatis diarahkan ke **Dashboard Admin**.

### Jika belum login

1. Buka URL admin (misalnya `/admin/dashboard`) tanpa login.
2. Sistem mengarahkan Anda ke halaman **Login**.
3. Setelah berhasil masuk, lanjutkan ke menu admin yang tersedia.

### Jika membuka halaman warga (salah role)

1. Login sebagai admin, lalu coba buka URL warga (misalnya `/dashboard`).
2. Sistem menampilkan **403 / Forbidden**.
3. Kembali ke Dashboard Admin lewat menu aplikasi.

> 💡 **Tips:** Jika mendapat 403, kembalilah ke Dashboard Admin dan gunakan menu yang tersedia untuk akun admin.

## FAQ

**Q: Saya admin, kenapa `/dashboard` (Dashboard Warga) ditolak?**
A: Benar. Dashboard Warga khusus role warga. Admin memakai **Dashboard Admin** di `/admin/dashboard`.

**Q: Apa bedanya 403 dengan diarahkan ke login?**
A: Belum login → diarahkan ke login. Sudah login tapi role salah → 403.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Selalu ke halaman login | Pastikan sudah login; sesi mungkin habis — login ulang |
| Mendapat 403 di halaman warga | Halaman tersebut khusus warga; kembali ke Dashboard Admin |
