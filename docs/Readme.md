# Project Documentation

Index of all documentation for **Sistem Informasi Pelayanan Surat Keterangan** (sistem-desa).

## Architecture

- [System Architecture](architecture.md)

## Developer Docs

See [dev-docs/README.md](dev-docs/README.md)

| Document | Description |
|----------|-------------|
| [Public Pages](dev-docs/features/public-pages.md) | Welcome + auth layout brand redesign |
| [Database Seeders](dev-docs/features/database-seeders.md) | Local admin/warga seed accounts |
| [Citizen Registration (US-1.1)](dev-docs/features/citizen-registration.md) | Technical docs for warga account registration |
| [Role-Based Login (US-1.2)](dev-docs/features/role-based-login.md) | Technical docs for login + role dashboards |
| [Role-Based Middleware (US-1.3)](dev-docs/features/role-middleware.md) | Technical docs for role gate + 403 |
| [Profile Management (US-1.4)](dev-docs/features/profile-management.md) | Technical docs for profile edit + password change |
| [Password Reset (US-1.5)](dev-docs/features/password-reset.md) | Technical docs for forgot-password flow |
| [Jenis Surat Management (US-2.1)](dev-docs/features/jenis-surat.md) | Admin CRUD jenis surat + soft/hard delete |
| [Persyaratan Dokumen Warga (US-2.2)](dev-docs/features/persyaratan-dokumen.md) | Warga browse + detail persyaratan dokumen |
| [Akses Publik Persyaratan Dokumen (US-2.3)](dev-docs/features/persyaratan-dokumen-publik.md) | Guest browse persyaratan tanpa login + CTA daftar/login |
| [Form Pengajuan Surat (US-3.1)](dev-docs/features/pengajuan-surat-form.md) | Warga submit pengajuan + auto nomor_pengajuan |
| [Unggah Dokumen Persyaratan (US-3.2)](dev-docs/features/pengajuan-surat-dokumen.md) | KTP/KK upload, preview, private storage |
| [Validasi Kelengkapan Pengajuan (US-3.3)](dev-docs/features/pengajuan-surat-kelengkapan.md) | Required-doc submit blocking + clear error messages |
| [Ajukan Ulang Setelah Ditolak (US-3.4)](dev-docs/features/pengajuan-surat-ajukan-ulang.md) | Resubmit ditolak pengajuan + riwayat page |
| [ADR-001: Keep `name` column + logout after register](dev-docs/decisions/001-registration-name-column-and-redirect.md) | Why `name` (not `nama`) and guest redirect after register |
| [ADR-002: Role-based login redirect](dev-docs/decisions/002-role-based-login-redirect.md) | Why custom LoginResponse + dual dashboards |
| [ADR-003: Role middleware 403](dev-docs/decisions/003-role-middleware-403.md) | Why denied access uses HTTP 403 |
| [ADR-004: Profile + password reset via Fortify](dev-docs/decisions/004-profile-password-reset-fortify.md) | Why Fortify/settings over Breeze for US-1.4/1.5 |
| [ADR-005: Public pages brand redesign](dev-docs/decisions/005-public-pages-brand-redesign.md) | Why forest/saffron brand + split auth layout |
| [ADR-006: jenis_surat table + admin CRUD](dev-docs/decisions/006-jenis-surat-table-and-admin-crud.md) | Why `jenis_surat` naming, modal CRUD, soft/hard delete |
| [ADR-007: Warga persyaratan dokumen view](dev-docs/decisions/007-warga-persyaratan-dokumen-view.md) | Why warga-only route + modal detail for US-2.2 |
| [ADR-008: Public persyaratan dokumen access](dev-docs/decisions/008-public-persyaratan-dokumen-access.md) | Why same public route + layouts/public for guests |
| [ADR-009: pengajuan_surat table + nomor format](dev-docs/decisions/009-pengajuan-surat-table-and-nomor-format.md) | Why singular table name + PJ-YYYYMMDD-#### nomor |
| [ADR-010: dokumen persyaratan upload](dev-docs/decisions/010-dokumen-persyaratan-text-detection-and-storage.md) | Text-based KTP/KK detection + private disk storage |

## User Docs

See [user-docs/README.md](user-docs/README.md)

| Document | Description |
|----------|-------------|
| [Panduan Beranda, Masuk, dan Daftar](user-docs/guides/public-pages.md) | Cara memakai beranda dan akun uji |
| [Panduan Registrasi Akun Warga](user-docs/guides/citizen-registration.md) | Cara warga mendaftar akun |
| [Panduan Login Berbasis Role](user-docs/guides/role-based-login.md) | Cara masuk dan keluar sesuai role |
| [Panduan Proteksi Akses Role](user-docs/guides/role-middleware.md) | Batasan akses warga vs admin |
| [Panduan Manajemen Profil](user-docs/guides/profile-management.md) | Cara edit profil dan ganti password |
| [Panduan Lupa Password](user-docs/guides/password-reset.md) | Cara reset password via email |
| [Panduan Kelola Jenis Surat](user-docs/guides/jenis-surat.md) | Cara admin menambah/ubah/arsip/hapus jenis surat |
| [Panduan Persyaratan Dokumen](user-docs/guides/persyaratan-dokumen.md) | Cara warga melihat persyaratan jenis surat |
| [Panduan Akses Publik Persyaratan Dokumen](user-docs/guides/persyaratan-dokumen-publik.md) | Cara pengunjung tanpa akun melihat persyaratan |
| [Panduan Pengajuan Surat](user-docs/guides/pengajuan-surat-form.md) | Cara warga mengajukan surat keterangan online |
| [Panduan Unggah Dokumen Persyaratan](user-docs/guides/pengajuan-surat-dokumen.md) | Cara mengunggah KTP/KK pada formulir pengajuan |
| [Panduan Validasi Kelengkapan Pengajuan](user-docs/guides/pengajuan-surat-kelengkapan.md) | Aturan dokumen wajib sebelum kirim pengajuan |
| [Panduan Ajukan Ulang Pengajuan](user-docs/guides/pengajuan-surat-ajukan-ulang.md) | Cara ajukan ulang setelah pengajuan ditolak |
