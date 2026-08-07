# ADR-026: Structured Persyaratan Rows Supersede Keyword Upload Rules

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** ADR-010 (upload/wajib **rules** only — private storage path and `dokumen_persyaratan` table remain)

## Context

Phase 02–03 stored letter requirements as free-text `jenis_surat.persyaratan_dokumen`. Form pengajuan detected upload slots by scanning for keywords `KTP` / `KK` / `Kartu Keluarga` (ADR-010). That approach was fragile (typos hide slots; incidental “KTP” creates mandatory uploads) and could not express “bring physical document to the office” or optional uploads (“jika ada”).

Phase 09 adds structured rows (US-9.1 / US-9.2) and switches the warga form to consume them (US-9.3).

## Decision

1. Add table `jenis_surat_persyaratan` with `nama`, `cara_pemenuhan` (`unggah` | `bawa_kantor` | `info`), `is_wajib` (meaningful when `unggah`), and `urutan`. FK to `jenis_surat` with `cascadeOnDelete`.
2. Keep `persyaratan_dokumen` as a **generated bullet summary** for search and legacy display; regenerate on every admin save via `JenisSurat::syncPersyaratan()`.
3. One-time data migration parses existing free text:
   - Lines with KTP / KK / Kartu Keluarga → `unggah` (`is_wajib = false` if “jika ada” / opsional / “jika relevan”)
   - All other non-empty lines → `bawa_kantor` (conservative default for desa practice)
   - Empty text → one `info` fallback row (“Persyaratan belum diatur…”)
4. **US-9.3:** `FormPengajuanSurat` loads structured rows for badges, upload slots, and validation. `detectRequiredDokumenTypes()` is removed. `dokumen_persyaratan` gains nullable `jenis_surat_persyaratan_id`, widened `jenis_dokumen`, and unique `(pengajuan_id, jenis_surat_persyaratan_id)`.
5. Const / option maps live on `JenisSuratPersyaratan` / Livewire — no separate Enum or Service class.

## Consequences

### Positive

- Admin (non-technical) can mark upload vs bring-to-office vs info without guessing keywords.
- Optional uploads are first-class (`is_wajib = false`).
- Form warga and admin pratinjau share the same badge labels.
- Existing search keeps working via generated summary.

### Negative

- Migration may misclassify unusual wording; admin must review after deploy.
- Legacy `dokumen_persyaratan` rows may lack `jenis_surat_persyaratan_id` (display falls back to `jenis_dokumen`).

### Neutral

- Soft-deleted jenis surat retain child rows until hard delete cascades them.
- US-9.4/9.5 still own public badge list polish and verifikasi physical checklist UI.
- Seeder Domisili/SKTM ship structured rows used by the form.
