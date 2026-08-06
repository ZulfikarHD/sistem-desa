# ADR-007: Warga persyaratan dokumen via Livewire modal

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** —

## Context
US-2.2 requires warga to see letter types with descriptions and document requirements, and to open detail before applying. US-2.3 will expose the same information publicly later; Phase 03 owns the actual pengajuan form. Soft-deleted master data from US-2.1 must not appear as available options.

## Decision
1. Add authenticated warga route `/persyaratan-dokumen` (`role:warga`) with class-based Livewire `PersyaratanDokumen`.
2. Use a responsive card list plus Flux modal for detail (one component, one route).
3. Query only non-trashed `JenisSurat`; do not implement public access or pengajuan submit in this story.

## Consequences

### Positive
- Matches architecture convention (1 route = 1 Livewire component)
- Clear separation from admin CRUD and future public/pengajuan stories
- Soft-delete semantics stay consistent for warga consumers

### Negative
- Guests still cannot browse requirements until US-2.3
- “Ajukan” remains informational until Phase 03

### Neutral
- Search and pagination added for usability beyond minimal AC wording
