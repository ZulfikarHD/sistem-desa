# Login Berbasis Role - Panduan Pengguna

## Apa itu Login Berbasis Role?

Setelah punya akun, Anda masuk dengan **email** dan **password**. Sistem mengenali apakah Anda **warga** atau **admin/petugas desa**, lalu membuka dashboard yang sesuai.

## Cara Menggunakan

### Masuk sebagai warga atau admin

1. Buka halaman **Masuk** (`/login`), atau dari beranda klik **Masuk**.
2. Isi form:
   - **Email**: email yang terdaftar
   - **Password**: password akun Anda
3. (Opsional) centang **Ingat saya** agar sesi lebih tahan lama di perangkat ini.
4. Klik **Masuk**.
5. Anda diarahkan ke:
   - **Dashboard Warga** jika akun berstatus warga
   - **Dashboard Admin** jika akun berstatus admin/petugas desa

> 💡 **Tips:** Belum punya akun warga? Klik tautan **Daftar** di bawah form login.

### Keluar (logout)

1. Dari dashboard, buka menu profil di sidebar (nama/avatar Anda).
2. Klik **Keluar**.
3. Anda kembali ke beranda dan harus masuk lagi untuk membuka dashboard.

### Jika login gagal

- Pastikan email dan password benar.
- Pesan error bersifat umum (tidak menyebutkan apakah email atau password yang salah) demi keamanan.
- Jika lupa password, gunakan tautan **Lupa Password?** di form login (jika tersedia).

## FAQ

**Q: Mengapa saya tidak masuk ke dashboard yang sama dengan teman saya?**  
A: Dashboard mengikuti **role** akun. Warga dan admin punya halaman beranda berbeda.

**Q: Bisakah saya mendaftar sebagai admin lewat form registrasi?**  
A: Tidak. Form registrasi publik hanya membuat akun **warga**. Akun admin dibuat oleh petugas.

**Q: Saya salah password berkali-kali. Apa yang terjadi?**  
A: Sistem membatasi percobaan login agar tidak disalahgunakan. Tunggu sebentar lalu coba lagi.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Pesan kredensial tidak cocok | Periksa email/password; pastikan tidak ada spasi ekstra |
| Setelah login tetap di `/login` | Pastikan password benar; coba clear cache browser |
| Tidak menemukan tombol Keluar | Buka menu profil (avatar/nama) di sidebar |
| Lupa password | Gunakan **Lupa Password?** atau hubungi petugas desa |
