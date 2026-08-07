# Use Case Diagram — Gambaran Umum Sistem

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | UC-Overview |
| **Nama** | Gambaran Umum Sistem |
| **Aktor** | Publik / Tamu, Warga, Admin / Petugas Desa |
| **Cakupan** | Seluruh fungsionalitas sistem |

## Deskripsi

Diagram ini menampilkan gambaran menyeluruh dari seluruh use case dalam Sistem Informasi Pelayanan Surat Keterangan Desa. Tiga aktor utama berinteraksi dengan sistem: Publik/Tamu (pengunjung tanpa akun), Warga (pengguna terdaftar), dan Admin/Petugas Desa (operator).

## Aktor

| Aktor | Deskripsi | Mewarisi dari |
|-------|-----------|---------------|
| **Publik / Tamu** | Pengunjung tanpa akun | — |
| **Warga** | Warga desa yang terdaftar | Semua akses Publik/Tamu |
| **Admin / Petugas Desa** | Operator desa | — |

## Diagram Use Case

```mermaid
graph LR
    Tamu(["👤 Publik / Tamu"])
    Warga(["👤 Warga"])
    Admin(["👤 Admin / Petugas Desa"])

    subgraph SISTEM["Sistem Informasi Pelayanan Surat Keterangan Desa"]

        subgraph PUB["Akses Publik"]
            UC01["UC-01\nMelihat Beranda"]
            UC02["UC-02\nMelihat Persyaratan\n(tanpa login)"]
            UC03["UC-03\nMendaftar Akun Warga"]
        end

        subgraph AUTH["Autentikasi & Profil"]
            UC04["UC-04\nLogin"]
            UC05["UC-05\nLogout"]
            UC06["UC-06\nKelola Profil"]
            UC07["UC-07\nReset Password"]
        end

        subgraph LAYAN["Layanan Warga"]
            UC08["UC-08\nLihat Persyaratan\n(setelah login)"]
            UC09["UC-09\nAjukan Surat"]
            UC10["UC-10\nUnggah Dokumen"]
            UC11["UC-11\nPantau Status\n(Dashboard Warga)"]
            UC12["UC-12\nLihat Notifikasi & Riwayat"]
            UC13["UC-13\nUnduh / Cetak Surat"]
            UC14["UC-14\nAjukan Ulang"]
        end

        subgraph ADMIN["Pengelolaan Admin"]
            UC15["UC-15\nDashboard Admin"]
            UC16["UC-16\nKelola Jenis Surat"]
            UC17["UC-17\nVerifikasi Pengajuan"]
            UC18["UC-18\nKelola Surat Diproses"]
            UC19["UC-19\nTetapkan Jadwal\nPengambilan"]
            UC20["UC-20\nScan QR Pengambilan"]
            UC21["UC-21\nRekap & Ekspor CSV"]
        end

        subgraph SYS["Proses Otomatis Sistem"]
            UC22["UC-22\nGenerate Surat PDF"]
            UC23["UC-23\nNomor Surat Resmi"]
            UC24["UC-24\nKirim Notifikasi"]
        end

    end

    Tamu --> UC01
    Tamu --> UC02
    Tamu --> UC03
    Tamu --> UC04

    Warga --> UC04
    Warga --> UC05
    Warga --> UC06
    Warga --> UC07
    Warga --> UC08
    Warga --> UC09
    Warga --> UC10
    Warga --> UC11
    Warga --> UC12
    Warga --> UC13
    Warga --> UC14

    Admin --> UC04
    Admin --> UC05
    Admin --> UC06
    Admin --> UC07
    Admin --> UC15
    Admin --> UC16
    Admin --> UC17
    Admin --> UC18
    Admin --> UC19
    Admin --> UC20
    Admin --> UC21

    UC09 -.->|"«include»"| UC10
    UC17 -.->|"«include»"| UC22
    UC22 -.->|"«include»"| UC23
    UC17 -.->|"«include»"| UC24
    UC19 -.->|"«include»"| UC24
    UC14 -.->|"«extend»"| UC09
```

## Daftar Use Case

| Kode | Nama | Aktor | Detail |
|------|------|-------|--------|
| UC-01 | Melihat Beranda | Publik/Tamu | [uc-publik.md](uc-publik.md) |
| UC-02 | Melihat Persyaratan (tanpa login) | Publik/Tamu | [uc-publik.md](uc-publik.md) |
| UC-03 | Mendaftar Akun Warga | Publik/Tamu | [uc-publik.md](uc-publik.md) |
| UC-04 | Login | Warga, Admin | [uc-warga.md](uc-warga.md) |
| UC-05 | Logout | Warga, Admin | [uc-warga.md](uc-warga.md) |
| UC-06 | Kelola Profil | Warga, Admin | [uc-warga.md](uc-warga.md) |
| UC-07 | Reset Password | Warga, Admin | [uc-warga.md](uc-warga.md) |
| UC-08 | Lihat Persyaratan (login) | Warga | [uc-warga.md](uc-warga.md) |
| UC-09 | Ajukan Surat | Warga | [uc-warga.md](uc-warga.md) |
| UC-10 | Unggah Dokumen | Warga | [uc-warga.md](uc-warga.md) |
| UC-11 | Pantau Status (Dashboard) | Warga | [uc-warga.md](uc-warga.md) |
| UC-12 | Lihat Notifikasi & Riwayat | Warga | [uc-warga.md](uc-warga.md) |
| UC-13 | Unduh / Cetak Surat | Warga | [uc-warga.md](uc-warga.md) |
| UC-14 | Ajukan Ulang | Warga | [uc-warga.md](uc-warga.md) |
| UC-15 | Dashboard Admin | Admin | [uc-admin.md](uc-admin.md) |
| UC-16 | Kelola Jenis Surat | Admin | [uc-admin.md](uc-admin.md) |
| UC-17 | Verifikasi Pengajuan | Admin | [uc-admin.md](uc-admin.md) |
| UC-18 | Kelola Surat Diproses | Admin | [uc-admin.md](uc-admin.md) |
| UC-19 | Tetapkan Jadwal Pengambilan | Admin | [uc-admin.md](uc-admin.md) |
| UC-20 | Scan QR Pengambilan | Admin | [uc-admin.md](uc-admin.md) |
| UC-21 | Rekap & Ekspor CSV | Admin | [uc-admin.md](uc-admin.md) |
| UC-22 | Generate Surat PDF | Sistem | [uc-admin.md](uc-admin.md) |
| UC-23 | Nomor Surat Resmi | Sistem | [uc-admin.md](uc-admin.md) |
| UC-24 | Kirim Notifikasi | Sistem | [uc-admin.md](uc-admin.md) |
