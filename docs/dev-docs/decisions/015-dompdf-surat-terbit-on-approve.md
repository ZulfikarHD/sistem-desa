# ADR-015: DomPDF + surat_terbit on Approve (US-7.2)

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** US-7.1 stub `triggerGenerateSurat()` (empty hook)

## Context

After US-7.1, approve ends in `diproses` but no letter file existed. US-7.2 requires an official PDF with kop, nomor, pemohon data, jenis, keperluan, tanggal terbit, penandatangan, and a one-time QR, stored under `surat_terbit`. The plan recommends DomPDF. No future story owned village letterhead settings.

## Decision

1. Add `barryvdh/laravel-dompdf` and table/model `surat_terbit`.
2. Implement `SuratTerbit::terbitkanUntuk()` called from `DetailPengajuanVerifikasi::triggerGenerateSurat`.
3. Use `config/desa.php` for kop + penandatangan + nomor codes.
4. Generate nomor (`470/{urut}/DS-WDN/{romawi}/{tahun}`) and opaque QR token as part of PDF issuance (needed by AC); leave scan UI to US-7.4.
5. Provide Blade templates per common jenis + default fallback.
6. Reject path never creates `surat_terbit` / PDF.

## Consequences

### Positive

- Warga/admin get a real document artifact as soon as status is `diproses`.
- Flat architecture preserved (logic on model + Livewire hook, no service layer).
- Idempotent issuance avoids duplicate files on retries.

### Negative

- New Composer dependency (DomPDF).
- US-7.3/7.4 will refine nomor/scan UX on top of fields already populated here.

### Neutral

- QR is printable and valid, but unused until US-7.4/US-7.5 complete the pickup flow.
- Unduh for warga remains US-7.6.
- Nomor format/sequence ownership clarified in ADR-016 (US-7.3).
