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
| [Verifikasi / Daftar Pengajuan (US-4.x + US-8.3/8.4)](dev-docs/features/verifikasi-pengajuan.md) | Admin list, detail preview, setujui/tolak, log audit |
| [Rename Daftar Pengajuan Surat (US-8.3)](dev-docs/features/daftar-pengajuan-surat-rename.md) | Label sidebar/heading: Verifikasi → Daftar Pengajuan Surat |
| [Setujui Langsung Diproses (US-8.4)](dev-docs/features/setujui-langsung-diproses.md) | Approve path: diajukan → diproses + satu notifikasi |
| [Migrasi Alur Status (US-7.1 / US-8.4)](dev-docs/features/migrasi-alur-status.md) | Status flow: diajukan → diproses; siap_diambil/selesai filters |
| [Generate Surat PDF (US-7.2)](dev-docs/features/generate-surat-pdf.md) | Auto PDF + nomor + QR on approve into diproses |
| [Nomor Surat Resmi Otomatis (US-7.3)](dev-docs/features/nomor-surat-resmi.md) | Official letter number format, year sequence, PDF print |
| [QR Code Sekali Pakai (US-7.4)](dev-docs/features/qr-sekali-pakai.md) | One-time pickup QR scan → selesai + invalid |
| [Dokumen Siap Diambil (US-7.5)](dev-docs/features/dokumen-siap-diambil.md) | Admin set pickup date + jam kerja + notifikasi warga |
| [Surat Diproses (US-8.5 & US-8.6)](dev-docs/features/surat-diproses.md) | Dedicated list/detail + Siap Diambil + siap_diambil_at |
| [Unduh/Cetak Surat Warga (US-7.6)](dev-docs/features/unduh-surat-warga.md) | Warga download/print issued PDF; detail shows pickup hours |
| [Notifikasi & Riwayat Pengajuan (US-5.1 – US-5.3)](dev-docs/features/notifikasi-pengajuan.md) | In-app notifications, bell panel, warga detail & riwayat |
| [Rekap Pengajuan & Reporting (US-6.1 – US-6.2)](dev-docs/features/rekap-pengajuan.md) | Admin filterable recap table, summary counts, CSV export |
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
| [ADR-011: verifikasi dokumen secure route](dev-docs/decisions/011-verifikasi-dokumen-secure-route.md) | Admin-only preview/download routes for private dokumen |
| [ADR-012: log_verifikasi and concurrent lock](dev-docs/decisions/012-verifikasi-log-and-concurrent-lock.md) | Audit log table + lockForUpdate on approve/reject |
| [ADR-013: Rekap summary filters + CSV BOM](dev-docs/decisions/013-rekap-summary-filters-and-csv-bom.md) | Ringkasan ignores status filter; CSV UTF-8 BOM |
| [ADR-014: Status flow migration US-7.1](dev-docs/decisions/014-status-flow-migration-us-7-1.md) | Remove auto diproses on open; approve path superseded by ADR-020 |
| [ADR-015: DomPDF surat_terbit on approve](dev-docs/decisions/015-dompdf-surat-terbit-on-approve.md) | Generate PDF + nomor + QR into surat_terbit on setujui |
| [ADR-016: Official nomor surat format](dev-docs/decisions/016-nomor-surat-resmi-format.md) | `470/{urut}/DS-WDN/{romawi}/{tahun}` + year sequence |
| [ADR-017: QR sekali pakai conditional update](dev-docs/decisions/017-qr-sekali-pakai-conditional-update.md) | Scan once; concurrent-safe invalidation |
| [ADR-018: Jam kerja + libur nasional config](dev-docs/decisions/018-jam-kerja-dan-libur-nasional-config.md) | Reject invalid pickup dates; labels from config |
| [ADR-019: Warga unduh/cetak existing PDF](dev-docs/decisions/019-warga-unduh-cetak-existing-pdf.md) | Serve stored PDF only; never regenerate QR on download |
| [ADR-020: Setujui langsung diproses US-8.4](dev-docs/decisions/020-setujui-langsung-diproses-us-8-4.md) | Approve → diproses in one step; keep disetujui for historis |
| [ADR-021: Surat Diproses page + siap_diambil_at](dev-docs/decisions/021-surat-diproses-page-and-siap-diambil-at.md) | Dedicated list/detail; relocate Siap Diambil UI; timestamp for timeline |

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
| [Panduan Verifikasi / Daftar Pengajuan](user-docs/guides/verifikasi-pengajuan.md) | Cara admin memeriksa, setujui/tolak pengajuan |
| [Panduan Daftar Pengajuan & Alur Setujui](user-docs/guides/daftar-pengajuan-dan-alur-setujui.md) | Rename menu + setujui langsung diproses (US-8.3/8.4) |
| [Panduan Migrasi Alur Status](user-docs/guides/migrasi-alur-status.md) | Arti status dan alur setujui → diproses |
| [Panduan Generate Surat PDF](user-docs/guides/generate-surat-pdf.md) | Cara surat PDF otomatis dibuat saat admin setujui |
| [Panduan Nomor Surat Resmi](user-docs/guides/nomor-surat-resmi.md) | Arti format nomor surat resmi otomatis |
| [Panduan Scan QR Pengambilan](user-docs/guides/qr-sekali-pakai.md) | Cara admin scan QR sekali pakai saat pengambilan |
| [Panduan Dokumen Siap Diambil](user-docs/guides/dokumen-siap-diambil.md) | Cara admin set tanggal pengambilan + notifikasi warga |
| [Panduan Surat Diproses & Siap Diambil](user-docs/guides/surat-diproses.md) | Menu Surat Diproses + tandai siap diambil (US-8.5/8.6) |
| [Panduan Unduh/Cetak Surat](user-docs/guides/unduh-surat-warga.md) | Cara warga mengunduh atau mencetak PDF surat |
| [Panduan Notifikasi & Riwayat Pengajuan](user-docs/guides/notifikasi-pengajuan.md) | Cara warga melihat notifikasi status dan riwayat pengajuan |
| [Panduan Rekap Pengajuan](user-docs/guides/rekap-pengajuan.md) | Cara admin memfilter dan ekspor laporan pengajuan |
