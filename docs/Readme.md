# Project Documentation

Index of all documentation for **Sistem Informasi Pelayanan Surat Keterangan** (sistem-desa).

## Architecture

- [System Architecture](architecture.md) *(placeholder — expand as the system grows)*

## Developer Docs

See [dev-docs/README.md](dev-docs/README.md)

| Document | Description |
|----------|-------------|
| [Citizen Registration (US-1.1)](dev-docs/features/citizen-registration.md) | Technical docs for warga account registration |
| [Role-Based Login (US-1.2)](dev-docs/features/role-based-login.md) | Technical docs for login + role dashboards |
| [Role-Based Middleware (US-1.3)](dev-docs/features/role-middleware.md) | Technical docs for role gate + 403 |
| [Profile Management (US-1.4)](dev-docs/features/profile-management.md) | Technical docs for profile edit + password change |
| [Password Reset (US-1.5)](dev-docs/features/password-reset.md) | Technical docs for forgot-password flow |
| [ADR-001: Keep `name` column + logout after register](dev-docs/decisions/001-registration-name-column-and-redirect.md) | Why `name` (not `nama`) and guest redirect after register |
| [ADR-002: Role-based login redirect](dev-docs/decisions/002-role-based-login-redirect.md) | Why custom LoginResponse + dual dashboards |
| [ADR-003: Role middleware 403](dev-docs/decisions/003-role-middleware-403.md) | Why denied access uses HTTP 403 |
| [ADR-004: Profile + password reset via Fortify](dev-docs/decisions/004-profile-password-reset-fortify.md) | Why Fortify/settings over Breeze for US-1.4/1.5 |

## User Docs

See [user-docs/README.md](user-docs/README.md)

| Document | Description |
|----------|-------------|
| [Panduan Registrasi Akun Warga](user-docs/guides/citizen-registration.md) | Cara warga mendaftar akun |
| [Panduan Login Berbasis Role](user-docs/guides/role-based-login.md) | Cara masuk dan keluar sesuai role |
| [Panduan Proteksi Akses Role](user-docs/guides/role-middleware.md) | Batasan akses warga vs admin |
| [Panduan Manajemen Profil](user-docs/guides/profile-management.md) | Cara edit profil dan ganti password |
| [Panduan Lupa Password](user-docs/guides/password-reset.md) | Cara reset password via email |
