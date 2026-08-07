# ADR-025: Bukti pengambilan PDF + Pengaturan Desa DB

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** ADR-015 (template-as-surat-keterangan intent), partially ADR-024 (unduh gate narrowed)

## Context

Generated PDFs looked like official *surat keterangan* (“menerangkan dengan sesungguhnya…”) but only echoed pemohon + keperluan — misleading for kelahiran/kematian/etc. Product intent is a **pickup slip** with QR, not digital letter issuance. Desa identity (kabupaten, alamat, kepala desa) lived only in `.env` with no admin UI. Warga could unduh while still `diproses`, before a pickup date existed.

## Decision

1. Single PDF template `pdf.surat.bukti-pengambilan`: title **Bukti Pengambilan Berkas**, identitas pemohon/jenis, tanggal+jam (or “Belum ditetapkan”), QR; **no** Kepala Desa signature / no “menerangkan”.
2. Warga unduh/cetak only for `siap_diambil` | `selesai`. Labels: Unduh/Cetak Bukti Pengambilan.
3. On `tandaiSiapDiambil`, call `regenerasiFilePdf()` (same QR/nomor, overwrite file with tanggal).
4. Store desa identity in single-row table `pengaturan_desa`; admin Livewire `FormPengaturanDesa`. Runtime uses `PengaturanDesa::untukSurat()` (DB + config jam/libur). Do not write `.env` from UI.

## Consequences

### Positive

- PDF wording matches real purpose (klaim berkas).
- Unduh only when pickup is scheduled.
- Admin can edit kop without deploy/.env.

### Negative

- Existing jenis-specific Blade templates removed; full digital surat keterangan deferred.
- First siap-diambil request pays DomPDF regen cost.

### Neutral

- Admin may still preview PDF at `diproses` (jadwal belum ditetapkan).
- ADR-024 hybrid `pastikanFilePdf()` remains for missing files.
