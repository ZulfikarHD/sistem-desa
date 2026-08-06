# ADR-021: Dedicated Surat Diproses page + siap_diambil_at (US-8.5/8.6)

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** UI location of ADR-018 / US-7.5 panel on verifikasi detail (logic kept on `SuratTerbit`)

## Context

Mixing “verify new submissions” and “set pickup date for in-progress letters” on one verifikasi detail page overloaded admin UX. Phase 08 also needs an accurate timestamp for the diproses → siap_diambil transition for the upcoming timeline (US-8.7). `updated_at` alone is unreliable.

## Decision

1. Add admin Livewire pages `/admin/surat-diproses` (list `diproses` only) and `/admin/surat-diproses/{id}` (detail + Siap Diambil).
2. Remove the siap-diambil panel from `DetailPengajuanVerifikasi`.
3. Add nullable `surat_terbit.siap_diambil_at`, set in `SuratTerbit::tandaiSiapDiambil`.
4. Enforce past-date blocking with HTML `min` (WIB today) and Laravel `after_or_equal` against WIB today, plus existing jam kerja / libur validation.
5. Align warga notification copy with US-8.6 AC wording.

## Consequences

### Positive
- Clear task separation for admin workflows.
- Accurate timeline source for US-8.7.
- Stronger past-date UX (client + server).

### Negative
- Existing bookmarks to verifikasi detail no longer expose siap-diambil controls; admins must use the new menu.
- Extra admin PDF routes to maintain.

### Neutral
- Domain method `tandaiSiapDiambil` remains the single write path.
