# Persyaratan Dokumen (Warga View)

## Overview

Authenticated warga can browse active letter types (`jenis_surat`) with description and document requirements, then open a detail modal before preparing to apply. This is the warga-facing read-only surface for Phase 02 US-2.2. The same route is also public for guests (US-2.3 — see [persyaratan-dokumen-publik.md](persyaratan-dokumen-publik.md)); actual submission is owned by Phase 03.

## Architecture Diagram

```mermaid
flowchart TD
    A[User opens /persyaratan-dokumen] --> B{Authenticated?}
    B -->|No| C[layouts::public + guest CTA]
    B -->|Yes| D[layouts::app, no guest CTA]
    C --> E[PersyaratanDokumen Livewire]
    D --> E
    E --> F[List active JenisSurat]
    E --> G[Detail modal]
    F --> H[(jenis_surat)]
    G --> H
```

## Data Model

```mermaid
erDiagram
    jenis_surat {
        int id PK
        string nama_surat UK
        text deskripsi "nullable"
        text persyaratan_dokumen
        datetime deleted_at "soft delete — hidden from browse"
    }
```

Same table as admin CRUD (US-2.1). Soft-deleted rows are excluded by Eloquent SoftDeletes defaults.

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Livewire | `app/Livewire/JenisSurat/PersyaratanDokumen.php` | List, search, detail modal state + layout switch |
| Blade | `resources/views/livewire/jenis-surat/persyaratan-dokumen.blade.php` | Responsive card grid + Flux modal + guest CTA |
| Routes | `routes/web.php` | Public `persyaratan-dokumen.index` (no auth middleware) |
| Nav | `resources/views/layouts/app/sidebar.blade.php`, `header.blade.php` | Warga sidebar/header link |
| Model | `app/Models/JenisSurat.php` | Shared master data |
| Pest | `tests/Feature/PersyaratanDokumenTest.php` | Guest/warga/admin, list, detail, search, soft-delete |
| Playwright | `e2e/persyaratan-dokumen.spec.ts` | E2E warga AC + edge cases |

## Flow Explanation

1. **User triggers** — warga opens **Persyaratan Dokumen** from the sidebar or `/persyaratan-dokumen`.
2. **Request handling** — route is public; authenticated users get `layouts::app`, guests get `layouts::public`.
3. **Business logic** — `PersyaratanDokumen` paginates active `JenisSurat` (9 per page), optional `?q=` search across nama/deskripsi/persyaratan. `openDetail($id)` loads an active row into a Flux modal; archived IDs raise `ModelNotFoundException`.
4. **Response** — responsive card grid (1/2/3 columns) with nama, deskripsi, persyaratan preview; modal shows full text. No pengajuan submit (Phase 03). Guests see CTA to daftar/login.

## API Endpoints (if applicable)

| Method | URI | Purpose | Auth |
|--------|-----|---------|------|
| GET | `/persyaratan-dokumen` | Requirements browse page (Livewire) | Public |

## Decisions & Trade-offs

- **Shared public route** — US-2.3 opened the same URL to guests; warga UX unchanged aside from no guest CTA. See ADR-008.
- **Single route + detail modal** — matches architecture (1 page = 1 Livewire component) and admin modal pattern.
- **Hide soft-deleted** — archived types must not appear as apply-ready options.
- **Search included** — not in AC wording but low-cost and consistent with admin list UX.
- **Pengajuan CTA deferred** — Phase 03 owns submission; modal shows informational callout only.

## Related

- Public access (US-2.3): [persyaratan-dokumen-publik.md](persyaratan-dokumen-publik.md)
- Admin CRUD: [jenis-surat.md](jenis-surat.md)
- Role middleware: [role-middleware.md](role-middleware.md)
- User guide: [../../user-docs/guides/persyaratan-dokumen.md](../../user-docs/guides/persyaratan-dokumen.md)
- ADR: [007-warga-persyaratan-dokumen-view.md](../decisions/007-warga-persyaratan-dokumen-view.md), [008-public-persyaratan-dokumen-access.md](../decisions/008-public-persyaratan-dokumen-access.md)
