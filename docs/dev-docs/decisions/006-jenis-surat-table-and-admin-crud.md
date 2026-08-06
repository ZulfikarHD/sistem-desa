# ADR-006: jenis_surat table name and admin CRUD via Livewire modal

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context

US-2.1 needs master data for letter types. The Phase 02 plan specifies table `jenis_surat` with `nama_surat`, `deskripsi`, and `persyaratan_dokumen`. Laravel’s default pluralization would create `jenis_surats`. The architecture convention prefers one Livewire class component per page without service/repository layers. Phase risks recommend soft delete (and later FK guards for `pengajuan_surat`). Product follow-up made `persyaratan_dokumen` required and `deskripsi` optional, and added soft + hard delete.

## Decision

1. Use table name **`jenis_surat`** (explicit `$table` on `JenisSurat`) to match the plan data model.
2. Implement admin UI as class-based Livewire `DataJenisSurat` with list + search + Flux modal create/edit on a single route under the existing `role:admin` group (`/admin/jenis-surat`).
3. Validate `nama_surat` as required + unique; `persyaratan_dokumen` as required; leave `deskripsi` nullable.
4. Support **soft delete** (Arsipkan), **restore**, and **hard delete** (Hapus Permanen from arsip only, with confirmation modal). SoftDeletes column: `deleted_at`.

## Consequences

### Positive

- Schema matches Phase 02 docs and future FK expectations for pengajuan.
- One file pair for the admin page stays within architecture convention.
- Role protection reuses US-1.3 middleware without new auth patterns.
- Soft delete preserves history; hard delete frees unique names when truly unused.

### Negative

- Soft-deleted `nama_surat` still blocks unique reuse until hard-deleted (DB unique index).
- Free-text persyaratan may be inconsistently formatted until later UX guidance is enforced.
- When `pengajuan_surat` exists, hard delete of referenced rows may need an extra guard (not yet implemented).

### Neutral

- Warga/public read-only views remain US-2.2 / US-2.3.
