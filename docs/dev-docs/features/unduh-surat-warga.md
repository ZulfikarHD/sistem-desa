# Unduh/Cetak Surat oleh Warga (US-7.6)

## Overview

After a submission reaches `diproses` (PDF already generated), warga can download or print the issued surat PDF from riwayat and detail pages. Re-download serves the stored file when present. If the file is missing on disk, the system **lazy-regenerates** it once from frozen `surat_terbit` fields and **does not** mint a new QR token. When pickup has been scheduled (US-7.5), the warga detail page also shows `tanggal_pengambilan` and `jam_kerja_label`.

## Architecture Diagram

```mermaid
flowchart TD
    A[Warga opens Riwayat / Detail] --> B{dapatUnduhSurat?}
    B -->|no| C[No Unduh button]
    B -->|yes| D[Unduh Surat / Cetak Surat]
    D --> E[Route unduh-surat or cetak-surat]
    E --> F{owner + status OK?}
    F -->|no| G[403]
    F -->|yes| H[SuratTerbit::pastikanFilePdf]
    H --> I{file on disk?}
    I -->|yes| J[Serve stored PDF]
    I -->|no| K[Render DomPDF with same qr_token]
    K --> L[Save canonical path + update file_path]
    L --> J
    J --> M[Download attachment or inline PDF]
    M --> N[QR token unchanged]
```

## Data Model

```mermaid
erDiagram
    PENGAJUAN_SURAT ||--o| SURAT_TERBIT : has
    SURAT_TERBIT {
        string file_path
        string nomor_surat
        string qr_token
        string qr_status
        date tanggal_pengambilan
        string jam_kerja_label
    }
    PENGAJUAN_SURAT {
        string status
        int user_id
    }
```

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Model | `app/Models/PengajuanSurat.php` | `dapatUnduhSurat()` / `statusBolehUnduhSurat()` |
| Model | `app/Models/SuratTerbit.php` | `pastikanFilePdf()` hybrid serve/regen |
| Routes | `routes/web.php` | `pengajuan-surat.unduh-surat`, `pengajuan-surat.cetak-surat` |
| Livewire | `app/Livewire/Pengajuan/RiwayatPengajuan.php` | Eager-load suratTerbit for Unduh button |
| Blade | `resources/views/livewire/pengajuan/riwayat-pengajuan.blade.php` | Unduh Surat on allowed rows |
| Livewire | `app/Livewire/Pengajuan/DetailPengajuanWarga.php` | Load suratTerbit for detail UI |
| Blade | `resources/views/livewire/pengajuan/detail-pengajuan-warga.blade.php` | Tanggal/jam + Unduh/Cetak |
| Pest | `tests/Feature/UnduhSuratWargaTest.php` | Auth, status, re-download, lazy regen |
| E2E | `e2e/unduh-surat-warga.spec.ts` | Browser download + missing-file edge |

## Flow Explanation

1. **User triggers** — Warga clicks **Unduh Surat** on riwayat (status `diproses` / `siap_diambil` / `selesai`) or **Unduh Surat** / **Cetak Surat** on detail.
2. **Request handling** — Route under `auth` + `verified` + `role:warga`. Abort unless `user_id` matches and `dapatUnduhSurat()`; abort 404 only if `surat_terbit` is missing or regen fails.
3. **Business logic** — `pastikanFilePdf()` serves existing file or regenerates with the **same** `qr_token` / `nomor_surat` / `tanggal_terbit`, then persists to `surat-terbit/{pengajuan_id}/surat.pdf`.
4. **Response** — Unduh: `Storage::download` (attachment). Cetak: `Storage::response` with `application/pdf` (inline, new tab).

## API Endpoints (if applicable)

| Method | URI | Purpose | Auth |
|--------|-----|---------|------|
| GET | `/pengajuan-surat/{pengajuan}/unduh-surat` | Download PDF attachment | warga (owner) |
| GET | `/pengajuan-surat/{pengajuan}/cetak-surat` | Inline PDF for print | warga (owner) |

## Decisions & Trade-offs

- Hybrid store + lazy regen (ADR-024) instead of hard 404 when the file is missing.
- Cetak is a separate inline response (story title Unduh/Cetak); no future story owned print-only UX.
- Detail also exposes Unduh/Cetak even though AC text highlights riwayat rows — needed for tanggal/jam AC on the same page.
- Admin PDF preview/download uses the same `pastikanFilePdf()` helper.

## Related

- [Generate Surat PDF (US-7.2)](generate-surat-pdf.md)
- [Dokumen Siap Diambil (US-7.5)](dokumen-siap-diambil.md)
- [QR Code Sekali Pakai (US-7.4)](qr-sekali-pakai.md)
- [ADR-024: Hybrid PDF lazy regenerate](../decisions/024-hybrid-pdf-lazy-regenerate.md)
- [ADR-019 (superseded)](../decisions/019-warga-unduh-cetak-existing-pdf.md)
