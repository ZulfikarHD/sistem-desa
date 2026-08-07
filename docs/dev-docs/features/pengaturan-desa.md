# Pengaturan Desa

## Overview

Admin-editable identitas kantor desa used on bukti pengambilan kop and nomor surat codes. Stored in single-row table `pengaturan_desa`. Jam kerja and libur nasional remain in `config/desa.php`.

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Model | `app/Models/PengaturanDesa.php` | `instance()`, `untukSurat()` |
| Livewire | `app/Livewire/Pengaturan/FormPengaturanDesa.php` | Edit form |
| Route | `pengaturan-desa.edit` | `/admin/pengaturan-desa` |
| Migration | `*_create_pengaturan_desas_table.php` | Table `pengaturan_desa` |

## Flow

1. Admin opens **Pengaturan Desa** from sidebar.
2. Form loads `PengaturanDesa::instance()` (creates from config defaults if empty).
3. Save updates the single row; next PDF/nomor uses DB values via `untukSurat()`.

## Related

- [ADR-025](../decisions/025-bukti-pengambilan-dan-pengaturan-desa.md)
