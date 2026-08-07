# Jenis Surat Management (US-2.1 + US-9.1 / US-9.2)

## Overview

Admin/petugas desa manage master data for letter types (`jenis_surat`): list with search, create/edit (Flux modal), soft delete (archive), restore, and hard delete from archive. As of Phase 09, each jenis surat has **structured requirement rows** (`jenis_surat_persyaratan`) that control how warga fulfills each syarat (upload / bring to office / info only) and whether an upload is required. The free-text column `persyaratan_dokumen` remains as a **generated summary** for search and backward-compatible display. Form pengajuan upload slots still use keyword detection until US-9.3.

## Architecture Diagram

```mermaid
flowchart TD
    A[Admin opens /admin/jenis-surat] --> B{auth + verified + role:admin}
    B -->|guest| C[Redirect /login]
    B -->|warga| D[HTTP 403]
    B -->|admin| E[DataJenisSurat Livewire]
    E --> F{showTrashed?}
    F -->|no| G[Active list + search + modal CRUD]
    F -->|yes| H[Arsip list + restore / hard delete]
    G --> I[JenisSurat Eloquent]
    G --> J[syncPersyaratan rows]
    J --> K[(jenis_surat_persyaratan)]
    J --> L[Generate persyaratan_dokumen ringkasan]
    H --> I
    I --> M[(jenis_surat + deleted_at)]
```

## Data Model

```mermaid
erDiagram
    jenis_surat ||--o{ jenis_surat_persyaratan : has
    jenis_surat {
        bigint id PK
        string nama_surat UK "max 100"
        text deskripsi "nullable"
        text persyaratan_dokumen "ringkasan generated"
        datetime created_at
        datetime updated_at
        datetime deleted_at "soft delete"
    }
    jenis_surat_persyaratan {
        bigint id PK
        bigint jenis_surat_id FK
        string nama
        string cara_pemenuhan "unggah|bawa_kantor|info"
        boolean is_wajib "relevan jika unggah"
        unsignedInt urutan
        datetime created_at
        datetime updated_at
    }
```

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Migration | `database/migrations/2026_08_06_061348_create_jenis_surat_table.php` | Creates `jenis_surat` table |
| Migration | `database/migrations/2026_08_06_063547_add_soft_deletes_to_jenis_surat_table.php` | Adds `deleted_at` |
| Migration | `database/migrations/2026_08_07_041339_create_jenis_surat_persyaratan_table.php` | Creates structured rows table |
| Migration | `database/migrations/2026_08_07_041340_migrate_persyaratan_dokumen_to_structured_rows.php` | One-time free-text → rows backfill |
| Model | `app/Models/JenisSurat.php` | Eloquent + SoftDeletes + `syncPersyaratan()` |
| Model | `app/Models/JenisSuratPersyaratan.php` | Row model, parse/migrate helpers, badge labels |
| Factory | `database/factories/JenisSuratFactory.php` | Parses `persyaratan_dokumen` into rows after create |
| Factory | `database/factories/JenisSuratPersyaratanFactory.php` | Row factory + states |
| Seeder | `database/seeders/JenisSuratSeeder.php` | Seeds structured rows (not keyword-only text) |
| Livewire | `app/Livewire/JenisSurat/DataJenisSurat.php` | List, search, CRUD + persyaratan rows |
| Blade | `resources/views/livewire/jenis-surat/data-jenis-surat.blade.php` | Admin UI (Flux) + pratinjau badge |
| Routes | `routes/web.php` | `Route::livewire('jenis-surat', ...)` inside `role:admin` |
| Pest | `tests/Feature/DataJenisSuratTest.php`, `DataJenisSuratPersyaratanTerstrukturTest.php`, `PersyaratanTerstrukturMigrationTest.php` | Feature coverage |
| Playwright | `e2e/jenis-surat.spec.ts` | E2E AC + failure cases |

## Flow Explanation

1. **User triggers** — admin opens **Jenis Surat** from the sidebar or `/admin/jenis-surat`.
2. **Request handling** — `auth` → `verified` → `role:admin`. Guests redirect to login; warga get 403.
3. **Business logic** — `DataJenisSurat` paginates active or trashed records (`showTrashed`), filters by `search` (URL `?q=`), and opens a Flux modal for create/edit. Admin adds/edits/removes/reorders requirement rows with **Cara memenuhi** (`unggah` / `bawa_kantor` / `info`) and, for upload rows, **Wajib** vs **Boleh dikosongkan**. `save()` validates ≥1 row, syncs rows via `JenisSurat::syncPersyaratan()`, and regenerates `persyaratan_dokumen`. Soft delete archives; restore/force-delete operate on arsip only (force delete cascades rows).
4. **Response** — list re-renders; Flux toast confirms success; validation errors stay in the modal. Pratinjau badge mirrors warga-facing labels.

## API Endpoints (if applicable)

No JSON API. Session web route only:

| Method | URI | Purpose | Auth |
|--------|-----|---------|------|
| GET | `/admin/jenis-surat` | Admin CRUD page (Livewire) | auth + verified + role:admin |

## Decisions & Trade-offs

- **Table name `jenis_surat`** — matches Phase 02 data model literally (not Laravel’s default `jenis_surats`). See ADR-006.
- **Structured rows as source of truth** — admin chooses upload vs bring-to-office explicitly; keyword magic superseded for **rules** (ADR-026). Form warga still keyword-based until US-9.3.
- **`persyaratan_dokumen` kept as generated summary** — search + public/warga pages that still show free text stay non-empty.
- **Default migrasi non-KTP/KK → `bawa_kantor`** — conservative; safer than forcing wrong uploads.
- **Single page + modal** — one route / one Livewire component (architecture convention). No service/repository/enum files.
- **Soft + hard delete** — **Arsipkan** soft-deletes; hard delete from arsip cascades `jenis_surat_persyaratan`.
- **Class-based Livewire** — follows project architecture convention.

## Related

- User guide: [../../user-docs/guides/admin/04-jenis-surat.md](../../user-docs/guides/admin/04-jenis-surat.md)
- ADR-006: [006-jenis-surat-table-and-admin-crud.md](../decisions/006-jenis-surat-table-and-admin-crud.md)
- ADR-010 (superseded for upload rules): [010-dokumen-persyaratan-text-detection-and-storage.md](../decisions/010-dokumen-persyaratan-text-detection-and-storage.md)
- ADR-026: [026-persyaratan-terstruktur-supersede-keyword-upload.md](../decisions/026-persyaratan-terstruktur-supersede-keyword-upload.md)
- Plan: `scrum-planning/Phase 09 - Persyaratan Terstruktur & Aturan Unggah.md` (US-9.1 / US-9.2)
