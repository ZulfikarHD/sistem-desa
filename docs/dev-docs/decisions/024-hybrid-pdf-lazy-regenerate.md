# ADR-024: Hybrid PDF — store on issue, lazy regenerate if missing

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** ADR-019

## Context

US-7.6 and admin PDF routes previously returned **404** whenever `surat_terbit.file_path` was absent from the `local` disk — even though metadata (nomor, QR token, tanggal) still existed. Demo seeders used factory fake paths without writing files, so unduh/cetak on seeded `selesai` rows failed. Pure on-demand generation every request would burn CPU and risk minting a new QR if implemented carelessly. Pure “file must exist” was too brittle for disk loss / reseed / deploy without `storage`.

## Decision

1. Keep writing the PDF once on terbit (`SuratTerbit::terbitkanUntuk`) to `surat-terbit/{pengajuan_id}/surat.pdf`.
2. On unduh/cetak (warga) and admin PDF show/download, call `SuratTerbit::pastikanFilePdf()`:
   - If the stored file exists → serve it.
   - If missing → regenerate **once** via DomPDF using frozen `nomor_surat`, `qr_token`, and `tanggal_terbit` (never mint a new QR), write to the canonical path, update `file_path`, then serve.
3. Demo seeder uses `terbitkanUntuk` so demo rows have real PDFs.
4. Layout may follow the **current** Blade template on lazy regen (no template versioning).

## Consequences

### Positive

- Unduh/cetak no longer 404 when the row exists but the file was lost.
- QR token remains stable across re-download and lazy regen (US-7.4).
- Happy path stays a cheap file read.

### Negative

- First request after file loss pays DomPDF cost.
- Regenerated PDF may differ visually if templates changed since original issue.

### Neutral

- Admin preview/unduh and rekap unduh share the same helper as warga routes.
- ADR-019’s “404 if missing” rule is replaced by this hybrid.
