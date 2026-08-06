# ADR-013: Rekap summary filter semantics and CSV UTF-8 BOM

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

Phase 06 needs an admin recap page with summary counts and CSV export. Two ambiguities needed an explicit choice:

1. Should summary cards (total / per status) respect the table’s status filter?
2. How should CSV be encoded so Excel on Windows opens Indonesian text correctly?

Phase 07 will later add `nomor_surat` / `tanggal_terbit` to this same page — those columns are out of scope here.

## Decision

1. Summary counts respect **jenis surat + date range** filters, but **ignore the status filter**, so operators can narrow the table by status without collapsing the volume overview.
2. Summary includes **`diproses`** in addition to the AC list (total, diajukan, disetujui, ditolak), because Phase 04 introduced that status system-wide.
3. CSV export uses Livewire `streamDownload` with a **UTF-8 BOM** (`EF BB BF`) and columns matching the table.
4. Default filters are empty (show all). Access is `role:admin` only.

## Consequences

### Positive
- Ringkasan remains useful while filtering the table by status
- Excel opens CSV without mojibake for Indonesian labels
- Logic stays inside one Livewire component (project architecture convention)

### Negative
- Operators must understand that status filter does not change the cards (documented in user guide)
- Extra `diproses` card goes slightly beyond the literal AC parenthetical list

### Neutral
- Indexes on `status` and `(jenis_surat_id, status, tanggal_pengajuan)` support filter performance
- Phase 07 will extend the same page/export for issued-letter columns
