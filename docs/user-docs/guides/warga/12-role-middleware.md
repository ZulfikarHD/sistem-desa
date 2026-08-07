# Proteksi Akses Berdasarkan Role - Panduan Pengguna (Warga)

> **Kelompok Pengguna:** Warga
> **Urutan:** 12 dari 12 — Memahami batasan akses akun warga.

## Apa itu Proteksi Role?

Sistem membatasi halaman sesuai jenis akun Anda. Sebagai **warga**, Anda hanya boleh membuka **Dashboard Warga** dan halaman-halaman untuk warga. Halaman admin tidak bisa diakses.

- **Warga** hanya boleh membuka **Dashboard Warga**
- Halaman **Profil / Pengaturan** bisa dipakai kedua jenis akun
- Halaman **Persyaratan Dokumen** bisa dibuka **tanpa login** (informasi publik)

Jika Anda mencoba membuka halaman admin, sistem menampilkan halaman **403 (Akses Ditolak)**.

## Cara Menggunakan

### Masuk sesuai role warga

1. Buka halaman **Login**.
2. Isi **Email** dan **Password**.
3. Klik **Masuk**.
4. Anda otomatis diarahkan ke **Dashboard Warga**.

### Jika belum login

1. Buka URL yang dilindungi (misalnya `/dashboard`) tanpa login.
2. Sistem mengarahkan Anda ke halaman **Login**.
3. Setelah berhasil masuk, lanjutkan ke menu yang tersedia untuk warga.

### Jika membuka halaman admin (salah role)

1. Login sebagai warga, lalu coba buka URL admin.
2. Sistem menampilkan **403 / Forbidden**.
3. Kembali ke Dashboard Warga lewat menu aplikasi.

> 💡 **Tips:** Jika mendapat 403, kembalilah ke Dashboard Warga dan gunakan menu yang tersedia untuk akun Anda.

## FAQ

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
| Mendapat 403 | Halaman tersebut khusus admin; kembali ke Dashboard Warga |
