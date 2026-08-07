# ADR-028: Dedicated create/edit pages for Jenis Surat

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** ADR-006 (modal CRUD UI portion only; table naming and soft/hard delete decisions remain)

## Context

US-2.1 originally shipped admin CRUD as a single list page with a Flux modal for create/edit (ADR-006), and US-9.1 kept that surface so structured persyaratan rows lived in the same modal. The persyaratan editor (multiple rows, cara memenuhi, wajib toggle, pratinjau, Domisili template) is tall and dense in a modal. Product feedback preferred a dedicated page for tambah/ubah, matching other full-page forms in the app (`FormPengajuanSurat`, `FormPengaturanDesa`).

Phase 09 AC C said “tetap di halaman yang sama / 1 route = 1 Livewire component.” That constraint was intentionally overridden: architecture still means **one Livewire class per page with logic inline** (no service/repository), not “all CRUD must share one URL.”

## Decision

1. Keep list + archive/delete on `DataJenisSurat` at `/admin/jenis-surat` (`jenis-surat.index`).
2. Move create/edit (including structured persyaratan editor) to `FormJenisSurat`:
   - `/admin/jenis-surat/create` (`jenis-surat.create`)
   - `/admin/jenis-surat/{jenisSurat}/edit` (`jenis-surat.edit`)
3. After successful save (or cancel), redirect to the list with `navigate: true`. Soft-deleted records remain 404 on edit via default Eloquent binding.
4. Force-delete confirmation stays as a modal on the list page only.

## Consequences

### Positive

- More space for persyaratan rows and pratinjau without modal scrolling friction.
- Clearer URLs and browser back/forward behavior.
- Aligns with existing dedicated-form page patterns.

### Negative

- Two Livewire components instead of one for admin jenis surat (list vs form).
- Slightly more navigation between list and form.

### Neutral

- Soft/hard delete, unique `nama_surat`, and `syncPersyaratan()` behavior unchanged.
- Sidebar `jenis-surat.*` route matching still highlights Jenis Surat for create/edit URLs.
