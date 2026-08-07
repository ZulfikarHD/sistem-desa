# ADR-023: Rekap timeline detail page path and actor sources

**Date:** 2026-08-07
**Status:** accepted
**Supersedes:** —

## Context

US-8.7 requires an admin detail page with a chronological process timeline. The plan writes the route as `/admin/rekap/{id}`, while the existing list lives at `/admin/rekap-pengajuan`. The plan also labels the siap-diambil step with “nama admin yang set”, but the data model only adds `siap_diambil_at` and states that the timeline can be reconstructed from existing tables (`diterbitkan_oleh`, `diverifikasi_oleh`, `qr_digunakan_oleh`, `log_verifikasi`).

## Decision

1. Register the detail route as `/admin/rekap-pengajuan/{pengajuan}` named `rekap-pengajuan.show`, matching the existing rekap index naming.
2. Resolve the siap-diambil actor from `surat_terbit.diterbitkan_oleh`, falling back to `pengajuan_surat.diverifikasi_oleh`, then `"Petugas desa"`.
3. For legacy rows missing `siap_diambil_at` while status is already `siap_diambil`/`selesai`, use `surat_terbit.updated_at` and mark the label as estimasi.
4. Keep timeline construction inside `DetailRekapPengajuan` (no service/repository layer).

## Consequences

### Positive

- URL naming stays consistent with `rekap-pengajuan.index`.
- No extra migration for `siap_diambil_oleh`.
- Matches Phase 08 “reconstruct from existing tables” guidance and legacy fallback risk note.

### Negative

- If a different admin marks siap diambil than the one who issued the PDF, the timeline actor may not match the person who clicked Siap Diambil.
- Literal plan path `/admin/rekap/{id}` is not used (semantic equivalent under `rekap-pengajuan`).

### Neutral

- PDF download reuses `surat-diproses.pdf.download` rather than a duplicate rekap-only PDF route.
