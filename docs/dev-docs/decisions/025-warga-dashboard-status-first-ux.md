# ADR-025: Warga dashboard status-first UX redesign

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** —

## Context

US-8.2 requires the first thing after login to answer “Sudah sampai mana surat saya?” — not a welcome banner or menu. The initial implementation met the data AC but still read like a generic dashboard (large “Dashboard Warga” heading, modest cards, admin-style riwayat table), so warga did not get an immediate status answer in the first viewport.

## Decision

1. Keep all US-8.2 acceptance criteria (hero tinting, penjelasan copy, elapsed amber, unduh, pickup schedule, banner, riwayat 3, notifikasi 3, role gate).
2. Make the visible H1 **status-focused**; keep “Dashboard Warga” as a small label for orientation and `data-test="dashboard-warga-heading"`.
3. Enlarge hero cards and add a three-step progress track (`Diajukan` → `Diproses` → `Siap diambil`) derived in `WargaDashboard` (`langkahAlur`, `indeksLangkahAktif`).
4. Replace the riwayat Flux table with a linked list that still exposes the same four fields.
5. Place “Ajukan Surat Baru” below the hero (secondary hierarchy); optional ghost CTA in the header does not replace it.

## Consequences

### Positive

- First viewport answers the warga’s primary job-to-be-done.
- Progress track reduces cognitive load without new backend status values.
- List riwayat is more readable on mobile than a four-column table.

### Negative

- Slight duplication of CTA affordances (header ghost + below-hero filled button) on wide screens.

### Neutral

- No schema or route changes; Pest/Playwright selectors largely preserved.
