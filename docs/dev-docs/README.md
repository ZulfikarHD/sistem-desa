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
