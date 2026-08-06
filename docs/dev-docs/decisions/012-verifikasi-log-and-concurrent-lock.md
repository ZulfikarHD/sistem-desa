# ADR-012: log_verifikasi audit table and pessimistic locking for approve/reject

**Date:** 2026-08-06
**Status:** accepted

## Context

US-4.3 requires every approve/reject action to be recorded in `log_verifikasi` with `admin_id`, `aksi`, `keterangan`, and timestamp. Phase 04 risk register notes two admins may attempt to verify the same pengajuan concurrently.

## Decision

1. Create singular table `log_verifikasi` (no `updated_at`) with FK to `pengajuan_surat` and `users`.
2. Store `aksi` as string constants (`setujui`, `tolak`) on `LogVerifikasi` model.
3. Wrap approve/reject in `DB::transaction` with `lockForUpdate()` on the `pengajuan_surat` row before checking `status === diproses`.
4. Require `catatan_admin` (min 5 chars) only for reject; approve clears `catatan_admin`.
5. Auto-transition `diajukan` → `diproses` in `mount()` without log entry (US-4.4); notification insert deferred to Phase 05.

## Consequences

### Positive

- Full audit trail for verification decisions
- Concurrent double-action mitigated at database level
- Reject reason persisted on both `pengajuan_surat.catatan_admin` and `log_verifikasi.keterangan`

### Negative

- `diproses` transition has no log row (only final decisions logged)
- In-app notification for `diproses` not sent until Phase 05

### Neutral

- Action buttons hidden when status is not `diproses` (including already decided pengajuan)
