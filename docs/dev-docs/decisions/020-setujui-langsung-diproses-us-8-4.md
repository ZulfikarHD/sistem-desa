# ADR-020: Setujui langsung ke diproses (US-8.4)

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** ADR-014 (approve path only — `diajukan` → `disetujui` → `diproses`)

## Context

Phase 07 US-7.1 required an intermediate `disetujui` write before auto-advancing to `diproses`, producing two in-app notifications and a status warga never needed to act on. Phase 08 US-8.4 removes that intermediate step while keeping the `disetujui` enum value for historical rows.

## Decision

- `DetailPengajuanVerifikasi::setujui()` sets `status = diproses` in one transaction (with `log_verifikasi` `aksi=setujui`, single warga notification, and PDF generation).
- Do not write `disetujui` on the new approval path.
- `PengajuanSurat::statusLabel('disetujui')` displays **Diproses**; filter option remains **Disetujui (historis)**.

## Consequences

### Positive

- One meaningful outcome per Setujui click; one notification matching the AC wording.
- Aligns UI flow with Phase 08 dashboards and Surat Diproses (US-8.5/8.6).

### Negative

- Tests and docs from Phase 07 that asserted the intermediate `disetujui` step must be updated.
- Rekap still has a separate ringkasan card for historical `disetujui` counts.

### Neutral

- DB enum/column value `disetujui` retained; no migration to rewrite historical statuses.
