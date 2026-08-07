# ADR-026: Structured Persyaratan Rows Supersede Keyword Upload Rules

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** ADR-010 (upload/wajib **rules** only — private storage path and `dokumen_persyaratan` table remain)

## Context

Phase 02–03 stored letter requirements as free-text `jenis_surat.persyaratan_dokumen`. Form pengajuan detected upload slots by scanning for keywords `KTP` / `KK` / `Kartu Keluarga` (ADR-010). That approach was fragile (typos hide slots; incidental “KTP” creates mandatory uploads) and could not express “bring physical document to the office” or optional uploads (“jika ada”).

Phase 09 (US-9.1 / US-9.2) needs admin-controlled structured rows before US-9.3 switches the warga form to consume them.

## Decision

1. Add table `jenis_surat_persyaratan` with `nama`, `cara_pemenuhan` (`unggah` | `bawa_kantor` | `info`), `is_wajib` (meaningful when `unggah`), and `urutan`. FK to `jenis_surat` with `cascadeOnDelete`.
2. Keep `persyaratan_dokumen` as a **generated bullet summary** for search and legacy display; regenerate on every admin save via `JenisSurat::syncPersyaratan()`.
3. One-time data migration parses existing free text:
   - Lines with KTP / KK / Kartu Keluarga → `unggah` (`is_wajib = false` if “jika ada” / opsional / “jika relevan”)
   - All other non-empty lines → `bawa_kantor` (conservative default for desa practice)
   - Empty text → one `info` fallback row (“Persyaratan belum diatur…”)
4. Keyword detection in `FormPengajuanSurat` remains until US-9.3; ADR-010 storage decisions stand.
5. Const / option maps live on `JenisSuratPersyaratan` / Livewire — no separate Enum or Service class.

## Consequences

### Positive

- Admin (non-technical) can mark upload vs bring-to-office vs info without guessing keywords.
- Optional uploads are first-class (`is_wajib = false`).
- Existing search and public/warga text previews keep working via generated summary.
- Seeder Domisili/SKTM ship structured rows ready for US-9.3.

### Negative

- Temporary dual model: structured rows exist while form warga still uses keywords until US-9.3.
- Migration may misclassify unusual wording; admin must review after deploy.

### Neutral

- Soft-deleted jenis surat retain child rows until hard delete cascades them.
- Template cepat Domisili (KTP + KK + Pengantar RT) is a Should convenience in the admin modal.
