# ADR-017: QR Sekali Pakai via Conditional Update (US-7.4)

**Date:** 2026-08-07
**Status:** accepted

## Context

Pickup must be registered exactly once. Two admins could scan the same QR at the same time. Re-downloading the PDF must not mint a new token or restore `valid`. The plan requires server-side enforcement with a conditional update `WHERE qr_status = valid`, no TTL, opaque tokens, and a dedicated Scan QR page (camera or manual).

## Decision

1. Keep token generation in `SuratTerbit::terbitkanUntuk` / `generateQrToken` (US-7.2); scan invalidation in `SuratTerbit::scanUntukPengambilan`.
2. Use DB transaction + `lockForUpdate` on surat and pengajuan, then `UPDATE ... WHERE qr_status = valid` to flip to `invalid` and record `qr_digunakan_at` / `qr_digunakan_oleh`.
3. Only allow success when pengajuan status is `siap_diambil`; then set status to `selesai` and notify warga.
4. Admin UI: Livewire `ScanQrPengambilan` at `/admin/scan-qr-pengambilan` with `BarcodeDetector` camera + manual token field.
5. Do not regenerate tokens on idempotent re-issue (`terbitkanUntuk` returns existing row).

## Consequences

### Positive

- Double-scan and concurrent-scan races cannot both succeed.
- Matches AC wording for rejection messages and permanence of `invalid`.
- No new npm QR-scanner dependency.

### Negative

- Related: [Dokumen Siap Diambil (US-7.5)](../features/dokumen-siap-diambil.md) sets `siap_diambil` before scan.
- `BarcodeDetector` is not available in all browsers; manual input is the universal path.

### Neutral

- Rekap QR status columns remain US-7.7.
- Warga unduh PDF remains US-7.6 and must keep using the same stored token.
