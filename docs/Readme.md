# Project Documentation

Index of all documentation for **Sistem Informasi Pelayanan Surat Keterangan** (sistem-desa).

## Architecture

- [System Architecture](architecture.md)
- [Database Architecture](dev-docs/database.md) — ER diagram, table dictionary, indexes
- [Route Map](dev-docs/routes.md) — all routes grouped by role + middleware
- [Livewire Components](dev-docs/livewire-components.md) — component inventory with responsibility summary

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
| [Dashboard Admin (US-8.1)](dev-docs/features/dashboard-admin.md) | Aging cards, urgent queue, active table |
| [Dashboard Warga (US-8.2)](dev-docs/features/dashboard-warga.md) | Hero status, unduh, riwayat, notifikasi |
| [Unduh/Cetak Surat Warga (US-7.6)](dev-docs/features/unduh-surat-warga.md) | Warga download/print issued PDF; hybrid lazy regen if file missing |
| [Notifikasi & Riwayat Pengajuan (US-5.1 – US-5.3)](dev-docs/features/notifikasi-pengajuan.md) | In-app notifications, bell panel, warga detail & riwayat |
| [Rekap Pengajuan & Reporting (US-6.1 – US-6.2)](dev-docs/features/rekap-pengajuan.md) | Admin filterable recap table, summary counts, CSV export |
| [Rekap Timeline Detail (US-8.7)](dev-docs/features/rekap-timeline.md) | Admin detail page with chronological process timeline |
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
| [ADR-019: Warga unduh/cetak existing PDF](dev-docs/decisions/019-warga-unduh-cetak-existing-pdf.md) | Superseded by ADR-024 |
| [ADR-024: Hybrid PDF lazy regenerate](dev-docs/decisions/024-hybrid-pdf-lazy-regenerate.md) | Store on issue; lazy regen if missing; never mint new QR |
| [ADR-020: Setujui langsung diproses US-8.4](dev-docs/decisions/020-setujui-langsung-diproses-us-8-4.md) | Approve → diproses in one step; keep disetujui for historis |
| [ADR-021: Surat Diproses page + siap_diambil_at](dev-docs/decisions/021-surat-diproses-page-and-siap-diambil-at.md) | Dedicated list/detail; relocate Siap Diambil UI; timestamp for timeline |
| [ADR-022: Dashboard aging helpers](dev-docs/decisions/022-dashboard-aging-and-status-helpers.md) | Component thresholds + PengajuanSurat status entered-at helpers |
| [ADR-023: Rekap timeline detail page](dev-docs/decisions/023-rekap-timeline-detail-page.md) | Detail path under rekap-pengajuan; actor/fallback rules for timeline |

## User Docs

See [user-docs/README.md](user-docs/README.md) — dikelompokkan per peran pengguna (Publik, Warga, Admin).

### Diagram Sistem

#### Use Case Diagram

| Diagram | Deskripsi |
|---------|-----------|
| [Gambaran Umum](user-docs/diagrams/usecase/uc-overview.md) | Semua aktor, 24 use case, relasi include/extend |
| [Publik / Tamu](user-docs/diagrams/usecase/uc-publik.md) | Use case beranda, persyaratan publik, registrasi |
| [Warga](user-docs/diagrams/usecase/uc-warga.md) | Use case login, pengajuan, unduh, notifikasi |
| [Admin / Petugas Desa](user-docs/diagrams/usecase/uc-admin.md) | Use case verifikasi, surat, QR, rekap |

#### Activity Diagram (1 proses = 1 file)

| Kode | Diagram | Aktor |
|------|---------|-------|
| AD-01 | [Registrasi Akun Warga](user-docs/diagrams/activity/ad-01-registrasi-akun-warga.md) | Publik |
| AD-02 | [Login & Redirect Dashboard](user-docs/diagrams/activity/ad-02-login-redirect-dashboard.md) | Warga/Admin |
| AD-03 | [Reset Password](user-docs/diagrams/activity/ad-03-reset-password.md) | Warga/Admin |
| AD-04 | [Pengajuan Surat Keterangan](user-docs/diagrams/activity/ad-04-pengajuan-surat.md) | Warga |
| AD-05 | [Verifikasi Pengajuan](user-docs/diagrams/activity/ad-05-verifikasi-pengajuan.md) | Admin |
| AD-06 | [Proses Surat & Jadwal Pengambilan](user-docs/diagrams/activity/ad-06-proses-surat-jadwal-pengambilan.md) | Admin |
| AD-07 | [Scan QR Pengambilan](user-docs/diagrams/activity/ad-07-scan-qr-pengambilan.md) | Admin |
| AD-08 | [Unduh / Cetak Surat](user-docs/diagrams/activity/ad-08-unduh-cetak-surat.md) | Warga |
| AD-09 | [Ajukan Ulang Setelah Ditolak](user-docs/diagrams/activity/ad-09-ajukan-ulang.md) | Warga |
| AD-10 | [Kelola Master Jenis Surat](user-docs/diagrams/activity/ad-10-kelola-jenis-surat.md) | Admin |
| AD-11 | [Rekap & Ekspor CSV](user-docs/diagrams/activity/ad-11-rekap-ekspor-csv.md) | Admin |
| AD-12 | [Alur Transisi Status Pengajuan](user-docs/diagrams/activity/ad-12-alur-status-pengajuan.md) | Sistem |
| AD-13 | [Detail Rekap & Timeline Proses](user-docs/diagrams/activity/ad-13-detail-rekap-timeline.md) | Admin |

### Panduan Pengguna — Publik / Tamu

| # | Panduan | Tautan |
|---|---------|--------|
| 1 | Beranda, Masuk, dan Daftar | [guides/publik/01-public-pages.md](user-docs/guides/publik/01-public-pages.md) |
| 2 | Akses Publik Persyaratan Dokumen | [guides/publik/02-persyaratan-dokumen-publik.md](user-docs/guides/publik/02-persyaratan-dokumen-publik.md) |
| 3 | Registrasi Akun Warga | [guides/publik/03-citizen-registration.md](user-docs/guides/publik/03-citizen-registration.md) |

### Panduan Pengguna — Warga

| # | Panduan | Tautan |
|---|---------|--------|
| 1 | Login Berbasis Role | [guides/warga/01-role-based-login.md](user-docs/guides/warga/01-role-based-login.md) |
| 2 | Manajemen Profil | [guides/warga/02-profile-management.md](user-docs/guides/warga/02-profile-management.md) |
| 3 | Lupa Password | [guides/warga/03-password-reset.md](user-docs/guides/warga/03-password-reset.md) |
| 4 | Persyaratan Dokumen | [guides/warga/04-persyaratan-dokumen.md](user-docs/guides/warga/04-persyaratan-dokumen.md) |
| 5 | Pengajuan Surat | [guides/warga/05-pengajuan-surat-form.md](user-docs/guides/warga/05-pengajuan-surat-form.md) |
| 6 | Unggah Dokumen Persyaratan | [guides/warga/06-pengajuan-surat-dokumen.md](user-docs/guides/warga/06-pengajuan-surat-dokumen.md) |
| 7 | Validasi Kelengkapan | [guides/warga/07-pengajuan-surat-kelengkapan.md](user-docs/guides/warga/07-pengajuan-surat-kelengkapan.md) |
| 8 | Dashboard Warga | [guides/warga/08-dashboard-warga.md](user-docs/guides/warga/08-dashboard-warga.md) |
| 9 | Notifikasi & Riwayat | [guides/warga/09-notifikasi-pengajuan.md](user-docs/guides/warga/09-notifikasi-pengajuan.md) |
| 10 | Unduh/Cetak Surat | [guides/warga/10-unduh-surat-warga.md](user-docs/guides/warga/10-unduh-surat-warga.md) |
| 11 | Ajukan Ulang | [guides/warga/11-pengajuan-surat-ajukan-ulang.md](user-docs/guides/warga/11-pengajuan-surat-ajukan-ulang.md) |
| 12 | Proteksi Akses Role | [guides/warga/12-role-middleware.md](user-docs/guides/warga/12-role-middleware.md) |

### Panduan Pengguna — Admin / Petugas Desa

| # | Panduan | Tautan |
|---|---------|--------|
| 1 | Login Berbasis Role | [guides/admin/01-role-based-login.md](user-docs/guides/admin/01-role-based-login.md) |
| 2 | Dashboard Admin | [guides/admin/02-dashboard-admin.md](user-docs/guides/admin/02-dashboard-admin.md) |
| 3 | Kelola Jenis Surat | [guides/admin/03-jenis-surat.md](user-docs/guides/admin/03-jenis-surat.md) |
| 4 | Verifikasi Pengajuan | [guides/admin/04-verifikasi-pengajuan.md](user-docs/guides/admin/04-verifikasi-pengajuan.md) |
| 5 | Daftar Pengajuan & Alur Setujui | [guides/admin/05-daftar-pengajuan-dan-alur-setujui.md](user-docs/guides/admin/05-daftar-pengajuan-dan-alur-setujui.md) |
| 6 | Generate Surat PDF | [guides/admin/06-generate-surat-pdf.md](user-docs/guides/admin/06-generate-surat-pdf.md) |
| 7 | Nomor Surat Resmi | [guides/admin/07-nomor-surat-resmi.md](user-docs/guides/admin/07-nomor-surat-resmi.md) |
| 8 | Surat Diproses | [guides/admin/08-surat-diproses.md](user-docs/guides/admin/08-surat-diproses.md) |
| 9 | Dokumen Siap Diambil | [guides/admin/09-dokumen-siap-diambil.md](user-docs/guides/admin/09-dokumen-siap-diambil.md) |
| 10 | Scan QR Pengambilan | [guides/admin/10-qr-sekali-pakai.md](user-docs/guides/admin/10-qr-sekali-pakai.md) |
| 11 | Rekap Pengajuan | [guides/admin/11-rekap-pengajuan.md](user-docs/guides/admin/11-rekap-pengajuan.md) |
| 12 | Detail Rekap & Timeline | [guides/admin/12-rekap-timeline.md](user-docs/guides/admin/12-rekap-timeline.md) |
| 13 | Migrasi Alur Status | [guides/admin/13-migrasi-alur-status.md](user-docs/guides/admin/13-migrasi-alur-status.md) |
| 14 | Proteksi Akses Role | [guides/admin/14-role-middleware.md](user-docs/guides/admin/14-role-middleware.md) |
| 15 | Manajemen Profil | [guides/admin/15-profile-management.md](user-docs/guides/admin/15-profile-management.md) |
| 16 | Lupa Password | [guides/admin/16-password-reset.md](user-docs/guides/admin/16-password-reset.md) |
