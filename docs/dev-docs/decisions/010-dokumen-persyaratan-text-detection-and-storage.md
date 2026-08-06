# ADR-010: Dokumen Persyaratan Text Detection and Private Storage

**Date:** 2026-08-06
**Status:** accepted

## Context

US-3.2 requires KTP/KK upload fields that match each `jenis_surat`'s document requirements. Phase 02 stores requirements as free-text `persyaratan_dokumen` (not structured flags). Identity files are sensitive and must not be publicly accessible.

## Decision

1. **Detect required upload slots** by keyword scan on `persyaratan_dokumen`: `KTP` → KTP field; `KK` or `Kartu Keluarga` → KK field.
2. **Store files** on Laravel's default `local` disk (`storage/app/private`) under `pengajuan-dokumen/{pengajuan_id}/`.
3. **Persist metadata** in `dokumen_persyaratan` with `jenis_dokumen` enum values `KTP` / `KK` and unique `(pengajuan_id, jenis_dokumen)`.
4. **Mandatory completeness (US-3.3, implemented)** — `FormPengajuanSurat::rules()` applies `required` to `dokumenKtp` / `dokumenKk` when the corresponding type is detected; US-3.2 still owns upload UI, format/size validation, and storage.

## Consequences

### Positive

- No migration change to `jenis_surat`; reuses existing admin workflow.
- Private disk reduces accidental public exposure of KTP/KK scans.
- Clear separation: US-3.2 upload/preview/storage vs US-3.3 submit blocking.

### Negative

- Keyword detection can mis-parse unusual admin wording (e.g. typo without "KTP").
- Admin cannot require document types beyond KTP/KK without code change.

### Neutral

- Preview for images uses Livewire `temporaryUrl()`; PDF shows filename only (Livewire security restriction).
