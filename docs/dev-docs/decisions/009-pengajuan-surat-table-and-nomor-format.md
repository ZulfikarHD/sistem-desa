# ADR-009: pengajuan_surat table naming and nomor_pengajuan format

**Date:** 2026-08-06
**Status:** accepted

## Context

Phase 03 US-3.1 introduces the core transaction table for citizen letter submissions. The scrum data model specifies table `pengajuan_surat` (singular) and unique `nomor_pengajuan` (varchar 30). Risk notes warn about collision when two warga submit concurrently. Phase 07 will introduce a separate official letter number — `nomor_pengajuan` is internal tracking only.

## Decision

1. Use table name `pengajuan_surat` on model `PengajuanSurat` (override `$table`), consistent with `jenis_surat`.
2. Generate `nomor_pengajuan` as `PJ-{Ymd}-{4-digit daily sequence}` inside a DB transaction with `lockForUpdate`, plus retry on unique constraint violation.
3. Keep generation logic in the Livewire `FormPengajuanSurat` component (no separate service class).
4. Initial status `diajukan`; `tanggal_pengajuan` = submit date (`now()->toDateString()`).

## Consequences

### Positive

- Unique index + transaction mitigates concurrent submit collisions.
- Readable nomor helps warga and admin support without exposing official letter numbering.
- Matches flat Livewire architecture used elsewhere in the project.

### Negative

- Daily sequence resets at midnight — nomor is not globally monotonic across days (acceptable for internal tracking).
- Document upload not included in US-3.1 — submissions may exist without attachments until US-3.2/3.3.

### Neutral

- `diverifikasi_oleh` and `catatan_admin` columns exist for Phase 04 but are unused in US-3.1.
