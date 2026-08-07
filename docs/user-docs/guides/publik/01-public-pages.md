# Beranda, Masuk, dan Daftar - Panduan Pengguna

> **Kelompok Pengguna:** Publik / Tamu
> **Urutan:** 1 dari 3 — Mulai dari sini jika Anda baru mengakses sistem.

## Apa itu halaman beranda?

Halaman beranda (**Pelayanan Surat Desa**) adalah pintu masuk aplikasi. Dari sini warga bisa **Daftar** akun baru atau **Masuk** jika sudah punya akun. Petugas desa juga masuk lewat tombol yang sama, lalu sistem mengarahkan ke dashboard sesuai role.

## Cara Menggunakan

### Dari beranda

1. Buka alamat aplikasi (halaman utama `/`).
2. Baca nama layanan dan ringkasan singkat di layar.
3. Pilih:
   - **Daftar** / **Daftar sebagai Warga** — membuat akun baru
   - **Masuk** / **Sudah punya akun? Masuk** — masuk ke akun yang sudah ada

> 💡 **Tips:** Jika Anda sudah login, beranda menampilkan tombol **Dashboard** untuk kembali ke halaman kerja Anda.

### Masuk (login)

1. Buka **Masuk** (`/login`), atau dari beranda klik **Masuk**.
2. Isi **Email** dan **Password**.
3. (Opsional) centang **Ingat saya**.
4. Klik **Masuk**.

Lihat panduan lengkap: [Login Berbasis Role](../warga/01-role-based-login.md).

### Daftar (registrasi warga)

1. Buka **Daftar** (`/register`).
2. Isi NIK, nama, telepon, alamat, email, dan password.
3. Klik **Daftar**, lalu masuk di halaman login.

Lihat panduan lengkap: [Registrasi Akun Warga](03-citizen-registration.md).

## Akun uji (hanya lingkungan pengembangan)

Jika petugas IT sudah menjalankan seeder, akun contoh berikut tersedia:

| Email | Password | Role |
|-------|----------|------|
| admin@desa.test | password | Admin / petugas |
| warga@desa.test | password | Warga |

> ⚠️ Akun ini **hanya untuk uji coba**, bukan untuk produksi.

## FAQ

**Q: Tombol di beranda dan di form login sama fungsinya?**
A: Ya. Beranda hanya memudahkan akses; proses masuk/daftar tetap sama.

**Q: Saya warga baru, harus mulai dari mana?**
A: Klik **Daftar sebagai Warga**, isi data sesuai KTP, lalu masuk dengan email dan password Anda.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tidak menemukan tombol Daftar | Pastikan Anda belum login; atau buka langsung `/register` |
| Tampilan beranda masih seperti Laravel default | Minta admin menjalankan build frontend (`pnpm build` / `pnpm dev`) |
| Login akun uji gagal | Pastikan database sudah di-seed ulang di lingkungan pengembangan |
