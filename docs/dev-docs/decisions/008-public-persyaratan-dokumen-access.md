# ADR-008: Public Persyaratan Dokumen Access

**Date:** 2026-08-06
**Status:** accepted
**Supersedes:** — (extends ADR-007; public access no longer deferred)

## Context

US-2.3 requires calon pemohon (guests) to view document requirements without registering or logging in. US-2.2 had already built an authenticated warga browse page at `/persyaratan-dokumen` behind `auth` + `role:warga`. ADR-007 explicitly deferred guest access to US-2.3. The app layout (`layouts::app`) assumes `auth()->user()` for sidebar/header, so guests cannot reuse it safely.

## Decision

1. Move `persyaratan-dokumen.index` **outside** the `auth` middleware group (single public Livewire route).
2. Reuse `PersyaratanDokumen` for guests and authenticated users; choose layout dynamically: `layouts::public` when guest, `layouts::app` when authenticated.
3. Show guest-only CTA **Daftar/Login untuk Mengajukan** with links to register and login; keep content read-only (no pengajuan submit).
4. Add a welcome-page link **Lihat Persyaratan Dokumen** for discoverability (not specified in AC; no later story owns it).

## Consequences

### Positive

- Guests can prepare documents before creating an account
- One component/route — no duplicated list/detail UI
- Aligns with Phase 01 note that this route is excluded from auth middleware

### Negative

- Admin is no longer blocked with 403 on this URL (acceptable for a public info page)
- US-2.2 E2E expectations for guest redirect / admin 403 must be updated

### Neutral

- Soft-deleted jenis surat remain hidden for everyone
- Phase 03 still owns actual pengajuan submission
