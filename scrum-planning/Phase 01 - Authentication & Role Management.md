# Phase 01 - Authentication & Role Management

**Sprint Goal**: Membangun sistem autentikasi dan manajemen role (Warga/Admin) sebagai fondasi akses ke seluruh modul Sistem Informasi Pelayanan Surat Keterangan.

**Estimated Duration**: 3-4 days

**Depends on**: None (entry point)

**Note:** Ini adalah **entry point** ke seluruh sistem. Modul lain (Jenis Surat, Pengajuan, Verifikasi, Notifikasi, Rekap) bergantung pada modul ini untuk autentikasi dan pembedaan hak akses antara Warga dan Admin/Petugas Desa.

---

## Why This Feature

- Warga dan Admin/Petugas Desa memerlukan tingkat akses yang berbeda ke sistem
- NIK dipakai sebagai identitas unik warga, mencegah duplikasi akun pengajuan
- Tanpa autentikasi berbasis role, halaman verifikasi dan pengelolaan data tidak bisa dibatasi aksesnya
- Proses saat ini sepenuhnya manual (formulir kertas, buku register) tanpa identitas terverifikasi secara digital

---

## User Stories

### US-1.1: Registrasi Akun Warga

**As a** warga desa
**I want** to register for an account with my personal data (NIK, nama, no. telepon, alamat, email, password)
**So that** I can access the letter submission system

**Acceptance Criteria:**
- [ ] Form registrasi: NIK, nama, no. telepon, alamat, email, password (+ konfirmasi password)
- [ ] Validasi NIK 16 digit dan unik (tidak boleh duplikat dengan akun lain)
- [ ] Validasi email unik dan format valid
- [ ] Password di-hash (bcrypt) sebelum disimpan ke basis data
- [ ] Role default untuk akun baru adalah `warga`
- [ ] Setelah registrasi berhasil, warga diarahkan ke halaman login

### US-1.2: Login Berbasis Role

**As a** user (warga atau admin)
**I want** to log in with my email and password
**So that** I can access the dashboard sesuai role saya

**Acceptance Criteria:**
- [ ] Form login: email, password
- [ ] Autentikasi memverifikasi kredensial dan role pengguna
- [ ] Redirect ke Dashboard Warga jika role = `warga`, ke Dashboard Admin jika role = `admin`
- [ ] Pesan error generik untuk kredensial salah (tidak mengungkap field mana yang salah)
- [ ] Fitur logout tersedia di kedua dashboard

### US-1.3: Middleware Proteksi Role

**As a** system administrator
**I want** every route to be protected by role-based middleware
**So that** warga cannot access admin pages and vice versa

**Acceptance Criteria:**
- [ ] Middleware memblokir akses warga ke route admin (Kelola Jenis Surat, Verifikasi, Rekap Pengajuan)
- [ ] Middleware memblokir akses admin ke route khusus warga jika tidak relevan
- [ ] Percobaan akses tanpa izin menampilkan halaman 403 atau redirect ke dashboard sesuai role
- [ ] Guest (belum login) yang mengakses route terproteksi diarahkan ke halaman login

### US-1.4: Manajemen Profil Pengguna

**As a** warga atau admin
**I want** to view and edit my basic profile data
**So that** my contact information stays accurate

**Acceptance Criteria:**
- [ ] Halaman profil menampilkan: nama, no. telepon, alamat, email
- [ ] Form edit profil (NIK dan role tidak dapat diubah sendiri oleh pengguna)
- [ ] Ganti password mewajibkan verifikasi password lama

### US-1.5: Lupa Password (Reset Password)

**As a** warga atau admin yang lupa password
**I want** to reset my password via a verification step
**So that** I can regain access without needing to visit the village office in person

**Acceptance Criteria:**
- [ ] Link "Lupa Password?" tersedia di halaman login
- [ ] Form input email untuk memulai proses reset
- [ ] Sistem mengirim link/token reset password ke email (memanfaatkan fitur bawaan Laravel Breeze)
- [ ] Token reset memiliki masa berlaku terbatas (misal 60 menit)
- [ ] Setelah reset berhasil, pengguna dapat login dengan password baru

**Data Model:**
```
users
  - id (PK, AI)
  - nik (varchar 16, unique)
  - nama (varchar 100)
  - email (varchar 100, unique)
  - password (varchar 255, hashed)
  - no_telepon (varchar 20)
  - alamat (text)
  - role (enum: warga, admin)
  - timestamps
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-1.1 Registrasi Akun Warga | 3 | Must |
| 2 | US-1.2 Login Berbasis Role | 3 | Must |
| 3 | US-1.3 Middleware Proteksi Role | 5 | Must |
| 4 | US-1.4 Manajemen Profil | 2 | Nice-to-have |
| 5 | US-1.5 Lupa Password (Reset Password) | 3 | Should |

**Total Story Points: 16**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| NIK duplikat akibat input manual salah ketik | Unique constraint di level database + pesan error yang jelas |
| Password lemah dipakai warga awam teknologi | Aturan minimum password (misal 8 karakter) + hint pada form |
| Middleware gagal memblokir akses tidak sah | Testing manual untuk setiap kombinasi role x route sebelum rilis |
