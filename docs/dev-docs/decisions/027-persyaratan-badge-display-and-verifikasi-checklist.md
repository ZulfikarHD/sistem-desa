# ADR-027: Persyaratan Badge Display & Visual Physical Checklist

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** — (extends ADR-026 display surfaces)

## Context

Phase 09 US-9.4 requires `/persyaratan-dokumen` (list + detail) to show structured requirement rows with plain-language badges instead of a raw `persyaratan_dokumen` text block. US-9.5 requires admin verification detail to separate online uploads from physical “bawa ke kantor” requirements, and to mark optional uploads that were left empty — without changing Phase 08 approve/reject flow or requiring interactive physical checkboxes (explicitly out of scope for MVP).

## Decision

1. **US-9.4:** `PersyaratanDokumen` eager-loads `jenis_surat_persyaratan` and renders item lists with `JenisSuratPersyaratan::badgeLabel()` / `badgeColor()` on both cards and detail modal. Search also matches `persyaratan.nama` via `orWhereHas`. Soft-delete + public access unchanged.
2. **US-9.5:** `DetailPengajuanVerifikasi` builds:
   - **Diunggah online** — unggah rows with file preview/download; optional empty → badge `Tidak diunggah — diperbolehkan`; legacy dokumen without matching unggah row still listed.
   - **Harus dicek / dibawa ke kantor** — visual list of `bawa_kantor` rows only (icon + helper text; **no** required checkbox before Setujui).
3. Info-only (`cara = info`) rows are not shown in the physical checklist (not physical, not uploaded).
4. Setujui/Tolak behavior remains US-8.4 / US-7.1 unchanged.

## Consequences

### Positive

- Public/warga see the same badge language as admin pratinjau and form pengajuan.
- Petugas know what is already online vs what to ask for physically.
- Avoids over-engineering interactive physical gates before approve.

### Negative

- Surat Diproses / Rekap detail do not show the same checklist (no dokumen section there today).
- Shared e2e DB can still pollute paginated lists if helpers use competing future dates — e2e helpers must keep far-future dates consistent.

### Neutral

- Empty unggah-only jenis surat still show the physical section empty-state copy.
