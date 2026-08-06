# Developer Documentation

Technical documentation for contributors working on sistem-desa.

## Features

| Feature | Doc |
|---------|-----|
| Public Pages (Welcome / Auth UI) | [features/public-pages.md](features/public-pages.md) |
| Database Seeders | [features/database-seeders.md](features/database-seeders.md) |
| Citizen Registration (US-1.1) | [features/citizen-registration.md](features/citizen-registration.md) |
| Role-Based Login (US-1.2) | [features/role-based-login.md](features/role-based-login.md) |
| Role-Based Middleware (US-1.3) | [features/role-middleware.md](features/role-middleware.md) |
| Profile Management (US-1.4) | [features/profile-management.md](features/profile-management.md) |
| Password Reset (US-1.5) | [features/password-reset.md](features/password-reset.md) |
| Jenis Surat Management (US-2.1) | [features/jenis-surat.md](features/jenis-surat.md) |
| Persyaratan Dokumen Warga (US-2.2) | [features/persyaratan-dokumen.md](features/persyaratan-dokumen.md) |
| Akses Publik Persyaratan Dokumen (US-2.3) | [features/persyaratan-dokumen-publik.md](features/persyaratan-dokumen-publik.md) |
| Form Pengajuan Surat (US-3.1) | [features/pengajuan-surat-form.md](features/pengajuan-surat-form.md) |
| Unggah Dokumen Persyaratan (US-3.2) | [features/pengajuan-surat-dokumen.md](features/pengajuan-surat-dokumen.md) |
| Validasi Kelengkapan Pengajuan (US-3.3) | [features/pengajuan-surat-kelengkapan.md](features/pengajuan-surat-kelengkapan.md) |
| Ajukan Ulang Setelah Ditolak (US-3.4) | [features/pengajuan-surat-ajukan-ulang.md](features/pengajuan-surat-ajukan-ulang.md) |
| Verifikasi Pengajuan (US-4.1 – US-4.3 + US-7.1) | [features/verifikasi-pengajuan.md](features/verifikasi-pengajuan.md) |
| Migrasi Alur Status (US-7.1) | [features/migrasi-alur-status.md](features/migrasi-alur-status.md) |
| Generate Surat PDF (US-7.2) | [features/generate-surat-pdf.md](features/generate-surat-pdf.md) |
| Nomor Surat Resmi Otomatis (US-7.3) | [features/nomor-surat-resmi.md](features/nomor-surat-resmi.md) |
| QR Code Sekali Pakai (US-7.4) | [features/qr-sekali-pakai.md](features/qr-sekali-pakai.md) |
| Dokumen Siap Diambil (US-7.5) | [features/dokumen-siap-diambil.md](features/dokumen-siap-diambil.md) |
| Unduh/Cetak Surat Warga (US-7.6) | [features/unduh-surat-warga.md](features/unduh-surat-warga.md) |
| Notifikasi & Riwayat Pengajuan (US-5.1 – US-5.3) | [features/notifikasi-pengajuan.md](features/notifikasi-pengajuan.md) |
| Rekap Pengajuan & Reporting (US-6.1 – US-6.2) | [features/rekap-pengajuan.md](features/rekap-pengajuan.md) |

## API

No public JSON API for auth yet — Fortify uses session-based form POSTs. Endpoint notes live in the feature docs.

## Decisions (ADR)

| ADR | Title |
|-----|-------|
| [001](decisions/001-registration-name-column-and-redirect.md) | Keep `name` column; logout + redirect to login after register |
| [002](decisions/002-role-based-login-redirect.md) | Role-based login redirect via LoginResponse |
| [003](decisions/003-role-middleware-403.md) | Role middleware denies with HTTP 403 |
| [004](decisions/004-profile-password-reset-fortify.md) | Profile + password reset via Fortify settings (not Breeze) |
| [005](decisions/005-public-pages-brand-redesign.md) | Public pages brand redesign (forest / saffron + split auth) |
| [006](decisions/006-jenis-surat-table-and-admin-crud.md) | `jenis_surat` naming, modal CRUD, soft/hard delete |
| [007](decisions/007-warga-persyaratan-dokumen-view.md) | Warga persyaratan view via Livewire modal |
| [008](decisions/008-public-persyaratan-dokumen-access.md) | Public `/persyaratan-dokumen` + layouts/public for guests |
| [009](decisions/009-pengajuan-surat-table-and-nomor-format.md) | `pengajuan_surat` naming + PJ-YYYYMMDD-#### nomor generation |
| [010](decisions/010-dokumen-persyaratan-text-detection-and-storage.md) | KTP/KK text detection + private file storage |
| [011](decisions/011-verifikasi-dokumen-secure-route.md) | Admin-only secure routes for dokumen preview/download |
| [012](decisions/012-verifikasi-log-and-concurrent-lock.md) | `log_verifikasi` audit table + pessimistic locking |
| [013](decisions/013-rekap-summary-filters-and-csv-bom.md) | Rekap summary ignores status filter; CSV UTF-8 BOM |
| [014](decisions/014-status-flow-migration-us-7-1.md) | Status flow migration: approve → disetujui → diproses |
| [015](decisions/015-dompdf-surat-terbit-on-approve.md) | DomPDF + surat_terbit generated on approve |
| [016](decisions/016-nomor-surat-resmi-format.md) | Official nomor surat format + per-year sequence |
| [017](decisions/017-qr-sekali-pakai-conditional-update.md) | QR once-only scan via conditional update |
| [018](decisions/018-jam-kerja-dan-libur-nasional-config.md) | Jam kerja labels + libur nasional config; reject invalid dates |
| [019](decisions/019-warga-unduh-cetak-existing-pdf.md) | Warga unduh/cetak existing PDF; no QR regeneration |
