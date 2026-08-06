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
| [ADR-001: Keep `name` column + logout after register](dev-docs/decisions/001-registration-name-column-and-redirect.md) | Why `name` (not `nama`) and guest redirect after register |
| [ADR-002: Role-based login redirect](dev-docs/decisions/002-role-based-login-redirect.md) | Why custom LoginResponse + dual dashboards |

## User Docs

See [user-docs/README.md](user-docs/README.md)

| Document | Description |
|----------|-------------|
| [Panduan Registrasi Akun Warga](user-docs/guides/citizen-registration.md) | Cara warga mendaftar akun |
| [Panduan Login Berbasis Role](user-docs/guides/role-based-login.md) | Cara masuk dan keluar sesuai role |
