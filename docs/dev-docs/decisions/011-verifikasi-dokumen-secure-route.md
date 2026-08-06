# ADR-011: Secure Admin Route for Document Preview

**Date:** 2026-08-06
**Status:** accepted

## Context

Warga upload KTP/KK to the private `local` disk (`storage/app/private/pengajuan-dokumen/{id}/`). Admin needs inline preview on the verifikasi detail page without exposing files publicly. Phase 04 risk notes corrupt/unpreviewable files need a download fallback.

## Decision

Add admin-only GET routes under `role:admin`:

- `verifikasi/dokumen/{dokumen}` — stream file for `<img>` / `<iframe>` preview
- `verifikasi/dokumen/{dokumen}/unduh` — force download

Register these routes **before** `verifikasi/{pengajuan}` to prevent `{pengajuan}` catching `dokumen` as an ID. Use `Storage::disk('local')->response()` and `download()` with 404 when file missing.

## Consequences

### Positive

- Documents remain on private disk; no public symlink required
- Preview and download share one authorization gate (admin middleware)
- Missing files degrade gracefully to callout + download attempt (404)

### Negative

- Extra routes outside Livewire components (not full-page UI)
- No per-pengajuan ownership check on dokumen route beyond admin role (acceptable for single-desha admin scope)

### Neutral

- Approve/reject actions remain separate (US-4.3); preview routes are read-only
