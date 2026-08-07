# Login Berbasis Role - Panduan Pengguna (Warga)

> **Kelompok Pengguna:** Warga
> **Urutan:** 1 dari 12 — Langkah pertama setelah memiliki akun.

## Apa itu Login Berbasis Role?

Setelah punya akun, Anda masuk dengan **email** dan **password**. Sistem mengenali bahwa Anda adalah **warga**, lalu membuka **Dashboard Warga** secara otomatis.

## Cara Menggunakan

### Masuk sebagai warga

1. Buka halaman **Masuk** (`/login`), atau dari beranda klik **Masuk**.
2. Isi form:
   - **Email**: email yang terdaftar saat registrasi
   - **Password**: password akun Anda
3. (Opsional) centang **Ingat saya** agar sesi lebih tahan lama di perangkat ini.
4. Klik **Masuk**.
5. Anda diarahkan ke **Dashboard Warga**.

> 💡 **Tips:** Belum punya akun? Klik tautan **Daftar** di bawah form login. Panduan: [Registrasi Akun Warga](../publik/03-citizen-registration.md).

### Keluar (logout)

1. Dari dashboard, buka menu profil di sidebar (nama/avatar Anda).
2. Klik **Keluar**.
3. Anda kembali ke beranda dan harus masuk lagi untuk membuka dashboard.

### Jika login gagal

- Pastikan email dan password benar.
- Pesan error bersifat umum (tidak menyebutkan apakah email atau password yang salah) demi keamanan.
- Jika lupa password, gunakan tautan **Lupa Password?** di form login. Panduan: [Lupa Password](03-password-reset.md).

## FAQ

**Q: Mengapa saya tidak masuk ke Dashboard Warga?**
A: Pastikan Anda login dengan akun berstatus warga, bukan admin. Dashboard mengikuti **role** akun.

**Q: Bisakah saya mendaftar sebagai admin lewat form registrasi?**
A: Tidak. Form registrasi publik hanya membuat akun **warga**. Akun admin dibuat oleh petugas.

**Q: Saya salah password berkali-kali. Apa yang terjadi?**
A: Sistem membatasi percobaan login. Tunggu sebentar lalu coba lagi.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Pesan kredensial tidak cocok | Periksa email/password; pastikan tidak ada spasi ekstra |
| Setelah login tetap di `/login` | Pastikan password benar; coba clear cache browser |
| Tidak menemukan tombol Keluar | Buka menu profil (avatar/nama) di sidebar |
| Lupa password | Gunakan **Lupa Password?** atau hubungi petugas desa |
