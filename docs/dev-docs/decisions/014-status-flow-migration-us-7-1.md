# ADR-014: Status Flow Migration (US-7.1)

**Date:** 2026-08-06
**Status:** superseded
**Superseded by:** ADR-020 (approve path only: setujui → diproses langsung)
**Supersedes:** US-4.4 auto `diajukan` → `diproses` on detail open (behavior only; Phase 04 plan file unchanged)

## Context

Phase 04 used `diproses` to mean “admin opened the review page.” Phase 07 needs `diproses` to mean “letter PDF is being prepared after approval,” with later states `siap_diambil` and `selesai`. Keeping the old auto-transition would collide with the new meaning and break filters/ringkasan.

## Decision

1. Remove auto status change (and its notification) from `DetailPengajuanVerifikasi::mount`.
2. Allow verify only from `diajukan`.
3. On approve: `diajukan` → `disetujui` (log + notif) → `diproses` (notif + `triggerGenerateSurat` → PDF via US-7.2 / ADR-015).
4. On reject: `diajukan` → `ditolak` only.
5. Add `siap_diambil` / `selesai` to model constants and Phase 05/06 filters + rekap ringkasan.
6. One-time data migration: `diproses` + `diverifikasi_oleh IS NULL` → `diajukan`.

## Consequences

### Positive

- Status vocabulary matches Phase 07 end-state.
- Opening detail is safe/idempotent for warga notifications.
- Rekap/riwayat can filter upcoming issuance states without schema change.

### Negative

- Existing tests/E2E locked to US-4.4 had to be rewritten.
- `disetujui` is often transient (immediately followed by `diproses`), so that ringkasan card may stay near zero until a pause is introduced later.

### Neutral

- Full PDF/QR generation is implemented in US-7.2 (ADR-015); scan/unduh remain later stories.
