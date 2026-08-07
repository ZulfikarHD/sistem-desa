# Dokumentasi Pengguna

Panduan untuk pengguna akhir **Sistem Informasi Pelayanan Surat Keterangan Desa**.

---

## Struktur Folder

```
docs/user-docs/
├── README.md                          ← Indeks ini
├── guides/
│   ├── publik/                        ← Panduan untuk Publik / Tamu (3 panduan)
│   ├── warga/                         ← Panduan untuk Warga (12 panduan)
│   └── admin/                         ← Panduan untuk Admin / Petugas Desa (17 panduan)
└── diagrams/
    ├── usecase/                        ← Use case diagram per kelompok aktor
    └── activity/                       ← Activity diagram per proses (1 proses = 1 file)
```

---

## Diagram Sistem

### Use Case Diagram

| File | Deskripsi |
|------|-----------|
| [uc-overview.md](diagrams/usecase/uc-overview.md) | Gambaran umum semua aktor dan use case |
| [uc-publik.md](diagrams/usecase/uc-publik.md) | Use case khusus Publik / Tamu |
| [uc-warga.md](diagrams/usecase/uc-warga.md) | Use case khusus Warga |
| [uc-admin.md](diagrams/usecase/uc-admin.md) | Use case khusus Admin / Petugas Desa |

### Activity Diagram

| Kode | File | Proses | Aktor |
|------|------|--------|-------|
| AD-01 | [ad-01-registrasi-akun-warga.md](diagrams/activity/ad-01-registrasi-akun-warga.md) | Registrasi Akun Warga | Publik / Tamu |
| AD-02 | [ad-02-login-redirect-dashboard.md](diagrams/activity/ad-02-login-redirect-dashboard.md) | Login dan Redirect Dashboard | Warga / Admin |
| AD-03 | [ad-03-reset-password.md](diagrams/activity/ad-03-reset-password.md) | Reset Password | Warga / Admin |
| AD-04 | [ad-04-pengajuan-surat.md](diagrams/activity/ad-04-pengajuan-surat.md) | Pengajuan Surat Keterangan | Warga |
| AD-05 | [ad-05-verifikasi-pengajuan.md](diagrams/activity/ad-05-verifikasi-pengajuan.md) | Verifikasi Pengajuan oleh Admin | Admin |
| AD-06 | [ad-06-proses-surat-jadwal-pengambilan.md](diagrams/activity/ad-06-proses-surat-jadwal-pengambilan.md) | Proses Surat & Penetapan Jadwal | Admin |
| AD-07 | [ad-07-scan-qr-pengambilan.md](diagrams/activity/ad-07-scan-qr-pengambilan.md) | Pengambilan Surat dengan Scan QR | Admin |
| AD-08 | [ad-08-unduh-cetak-surat.md](diagrams/activity/ad-08-unduh-cetak-surat.md) | Unduh / Cetak Surat | Warga |
| AD-09 | [ad-09-ajukan-ulang.md](diagrams/activity/ad-09-ajukan-ulang.md) | Ajukan Ulang Setelah Ditolak | Warga |
| AD-10 | [ad-10-kelola-jenis-surat.md](diagrams/activity/ad-10-kelola-jenis-surat.md) | Kelola Master Jenis Surat | Admin |
| AD-11 | [ad-11-rekap-ekspor-csv.md](diagrams/activity/ad-11-rekap-ekspor-csv.md) | Rekap Pengajuan & Ekspor CSV | Admin |
| AD-12 | [ad-12-alur-status-pengajuan.md](diagrams/activity/ad-12-alur-status-pengajuan.md) | Alur Transisi Status Pengajuan | Sistem |
| AD-13 | [ad-13-detail-rekap-timeline.md](diagrams/activity/ad-13-detail-rekap-timeline.md) | Melihat Detail Rekap & Timeline Proses | Admin |

---

## Panduan Pengguna — Publik / Tamu

> Untuk pengunjung yang **belum memiliki akun**. Baca urutan ini dari atas ke bawah.

| # | Panduan | Deskripsi |
|---|---------|-----------|
| 1 | [Beranda, Masuk, dan Daftar](guides/publik/01-public-pages.md) | Mengenal halaman utama dan cara masuk atau membuat akun |
| 2 | [Akses Publik Persyaratan Dokumen](guides/publik/02-persyaratan-dokumen-publik.md) | Melihat persyaratan jenis surat sebelum mendaftar |
| 3 | [Registrasi Akun Warga](guides/publik/03-citizen-registration.md) | Membuat akun baru sebagai warga desa |

---

## Panduan Pengguna — Warga

> Untuk **warga desa** yang sudah memiliki akun. Ikuti urutan ini dari login hingga surat diterima.

| # | Panduan | Deskripsi |
|---|---------|-----------|
| 1 | [Login Berbasis Role](guides/warga/01-role-based-login.md) | Cara masuk ke Dashboard Warga dan keluar dari sistem |
| 2 | [Manajemen Profil](guides/warga/02-profile-management.md) | Melihat dan mengubah data profil serta ganti password |
| 3 | [Lupa Password](guides/warga/03-password-reset.md) | Mereset password melalui email jika lupa |
| 4 | [Persyaratan Dokumen](guides/warga/04-persyaratan-dokumen.md) | Melihat dokumen yang diperlukan per jenis surat |
| 5 | [Pengajuan Surat](guides/warga/05-pengajuan-surat-form.md) | Mengajukan surat keterangan secara online |
| 6 | [Unggah Dokumen Persyaratan](guides/warga/06-pengajuan-surat-dokumen.md) | Cara mengunggah KTP/KK pada formulir pengajuan |
| 7 | [Validasi Kelengkapan Pengajuan](guides/warga/07-pengajuan-surat-kelengkapan.md) | Memahami dokumen wajib dan pesan error sebelum kirim |
| 8 | [Dashboard Warga](guides/warga/08-dashboard-warga.md) | Status surat aktif di halaman utama (alur + unduh + jadwal) |
| 9 | [Notifikasi & Riwayat Pengajuan](guides/warga/09-notifikasi-pengajuan.md) | Melihat notifikasi perubahan status dan riwayat pengajuan |
| 10 | [Unduh/Cetak Bukti Pengambilan](guides/warga/10-unduh-surat-warga.md) | Mengunduh bukti pengambilan berkas (bukan surat resmi) |
| 11 | [Ajukan Ulang Pengajuan](guides/warga/11-pengajuan-surat-ajukan-ulang.md) | Mengajukan ulang pengajuan yang sebelumnya ditolak |
| 12 | [Proteksi Akses Berdasarkan Role](guides/warga/12-role-middleware.md) | Memahami batasan halaman warga dan arti 403 |

---

## Panduan Pengguna — Admin / Petugas Desa

> Untuk **admin atau petugas desa**. Baca dari atas ke bawah: **setup dulu** (identitas desa + jenis surat), baru operasional harian.

| # | Panduan | Deskripsi |
|---|---------|-----------|
| 1 | [Login Berbasis Role](guides/admin/01-role-based-login.md) | Cara masuk ke Dashboard Admin dan keluar dari sistem |
| 2 | [Dashboard Admin](guides/admin/02-dashboard-admin.md) | Membaca kartu aging dan memantau antrean yang mendesak |
| 3 | [Pengaturan Desa](guides/admin/03-pengaturan-desa.md) | Atur identitas kantor untuk kop bukti pengambilan (wajib sebelum PDF) |
| 4 | [Kelola Jenis Surat](guides/admin/04-jenis-surat.md) | Menambah/ubah jenis surat beserta baris persyaratan terstruktur |
| 5 | [Verifikasi / Daftar Pengajuan](guides/admin/05-verifikasi-pengajuan.md) | Memeriksa, menyetujui, atau menolak pengajuan warga |
| 6 | [Daftar Pengajuan & Alur Setujui](guides/admin/06-daftar-pengajuan-dan-alur-setujui.md) | Alur setujui langsung diproses dan perubahan nama menu |
| 7 | [Generate Bukti Pengambilan PDF](guides/admin/07-generate-surat-pdf.md) | PDF bukti pengambilan otomatis saat admin menyetujui |
| 8 | [Nomor Surat Resmi](guides/admin/08-nomor-surat-resmi.md) | Memahami format dan arti nomor surat resmi otomatis |
| 9 | [Surat Diproses & Siap Diambil](guides/admin/09-surat-diproses.md) | Menu Surat Diproses dan cara menandai surat siap diambil |
| 10 | [Dokumen Siap Diambil](guides/admin/10-dokumen-siap-diambil.md) | Menetapkan tanggal pengambilan dan notifikasi ke warga |
| 11 | [Scan QR Pengambilan](guides/admin/11-qr-sekali-pakai.md) | Memindai QR code sekali pakai saat warga mengambil surat |
| 12 | [Rekap Pengajuan](guides/admin/12-rekap-pengajuan.md) | Memfilter rekap pengajuan dan mengekspor laporan CSV |
| 13 | [Detail Rekap & Timeline Proses](guides/admin/13-rekap-timeline.md) | Membaca riwayat proses kronologis tiap pengajuan (US-8.7) |
| 14 | [Migrasi Alur Status](guides/admin/14-migrasi-alur-status.md) | Referensi arti tiap status dan alur perubahan status surat |
| 15 | [Proteksi Akses Berdasarkan Role](guides/admin/15-role-middleware.md) | Memahami batasan halaman admin dan arti 403 |
| 16 | [Manajemen Profil](guides/admin/16-profile-management.md) | Melihat dan mengubah data profil serta ganti password |
| 17 | [Lupa Password](guides/admin/17-password-reset.md) | Mereset password melalui email jika lupa |
