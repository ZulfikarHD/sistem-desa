# ADR-022: Dashboard aging helpers on PengajuanSurat + component thresholds

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** —

## Context

US-8.1/8.2 need calendar-day aging, badge colors, and “entered current status” timestamps from multiple sources (`created_at`, `tanggal_terbit`, `siap_diambil_at`). Thresholds must not be magic numbers scattered in Blade. Historical `disetujui` rows still exist after US-8.4.

## Decision

1. Keep aging **warning/urgent thresholds** as `public const` on `AdminDashboard` (and warga elapsed-amber const on `WargaDashboard`).
2. Put reusable helpers on `PengajuanSurat`: `waktuMasukStatusSaatIni()`, `hariDiStatusSaatIni()`, `statusBadgeColor()`, `statusAktif()`, `statusDiprosesDashboard()`.
3. Count historical `disetujui` with `diproses` on the admin “Sedang Diproses” card.
4. Replace static Blade dashboards with Livewire page components on the same named routes.

## Consequences

### Positive

- Single source for “days in status” for admin and warga UIs.
- Thresholds easy to find and tune without a new config surface.
- Legacy `disetujui` rows remain visible to admin.

### Negative

- Loading all active rows into memory for ranking is fine at research scale but may need SQL-side ranking later.

### Neutral

- Badge color helper is available for other pages; dashboards use it first.
