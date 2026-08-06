# ADR-019: Warga unduh/cetak existing PDF without regenerating QR

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** —

## Context

US-7.6 requires warga to download the generated surat PDF for statuses `diproses`, `siap_diambil`, and `selesai`, and to re-download anytime without minting a new QR (US-7.4). The story title also includes Cetak. Detail must show pickup date and work hours when set (US-7.5). Existing admin dokumen routes already use `Storage::download` / `Storage::response` closures under role middleware.

## Decision

1. Add warga-only routes `pengajuan-surat.unduh-surat` (attachment) and `pengajuan-surat.cetak-surat` (inline PDF).
2. Gate with owner check + `PengajuanSurat::dapatUnduhSurat()`; 404 if file missing.
3. Serve `surat_terbit.file_path` from the `local` disk only — never call `terbitkanUntuk` or rewrite `qr_token`.
4. Show Unduh on riwayat rows; show Unduh + Cetak and pickup fields on warga detail.

## Consequences

### Positive

- Matches admin private-file pattern; no new service layer.
- Re-download cannot revive an invalid QR.
- Cetak works via browser print of inline PDF without a separate print stack.

### Negative

- Two near-duplicate route closures (unduh vs cetak) share authorization logic inline.
- Admin has no dedicated unduh-surat route (deferred; rekap archive is US-7.7).

### Neutral

- Detail Unduh is beyond the AC’s explicit “baris riwayat” wording but required for usable detail + silent-gap closure per sprint prompt.
