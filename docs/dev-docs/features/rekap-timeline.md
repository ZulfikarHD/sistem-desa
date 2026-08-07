# Rekap Timeline Detail (US-8.7)

## Overview

Admin can open any row in Rekap Pengajuan and view a courier-style chronological process timeline for that submission — from creation through approval/rejection, ready-for-pickup, and QR completion. Only events that have already occurred are shown (future steps are omitted, not greyed out).

## Architecture Diagram

```mermaid
flowchart TD
    A[Admin opens /admin/rekap-pengajuan] --> B[Clicks Lihat Detail]
    B --> C[DetailRekapPengajuan mount]
    C --> D[Eager load user, jenisSurat, logVerifikasi.admin, suratTerbit.*]
    D --> E[Ringkasan Pengajuan]
    D --> F[timelineItems]
    F --> G{status ditolak?}
    G -->|yes| H[dibuat + ditolak only]
    G -->|no| I[dibuat + setujui + siap_diambil? + selesai?]
    E --> J[Blade vertical timeline UI]
    I --> J
    H --> J
```

## Data Model

```mermaid
erDiagram
    pengajuan_surat ||--o{ log_verifikasi : has
    pengajuan_surat ||--o| surat_terbit : has
    log_verifikasi }o--|| users : admin
    surat_terbit }o--|| users : diterbitkan_oleh
    surat_terbit }o--o| users : qr_digunakan_oleh
    pengajuan_surat {
        bigint id PK
        string nomor_pengajuan
        string status
        timestamp created_at
    }
    log_verifikasi {
        string aksi
        string keterangan
        timestamp created_at
    }
    surat_terbit {
        string nomor_surat
        timestamp siap_diambil_at
        date tanggal_pengambilan
        string jam_kerja_label
        timestamp qr_digunakan_at
    }
```

No new tables. Timeline is reconstructed from existing relations. Legacy rows without `siap_diambil_at` fall back to `surat_terbit.updated_at` (marked as estimasi).

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Livewire | `app/Livewire/Rekap/DetailRekapPengajuan.php` | Detail page + `timelineItems()` builder |
| Blade | `resources/views/livewire/rekap/detail-rekap-pengajuan.blade.php` | Ringkasan + vertical timeline UI |
| Blade (list) | `resources/views/livewire/rekap/rekap-pengajuan.blade.php` | Adds **Lihat Detail** column |
| Route | `routes/web.php` | `rekap-pengajuan.show` → `/admin/rekap-pengajuan/{pengajuan}` |
| Feature tests | `tests/Feature/DetailRekapPengajuanTest.php` | Auth, timeline cases, PDF gate |
| E2E | `e2e/rekap-timeline.spec.ts` | Playwright coverage for US-8.7 |

## Flow Explanation

1. **User triggers** — Admin clicks **Lihat Detail** on a rekap table row.
2. **Request handling** — `auth` + `verified` + `role:admin`; route model binding loads `PengajuanSurat`.
3. **Business logic** — `timelineItems()` builds only occurred steps:
   - Created (`pengajuan_surat.created_at`, aktor: Sistem)
   - Approved & processed (`log_verifikasi` aksi=setujui + nomor surat)
   - Rejected (`log_verifikasi` aksi=tolak) — stops here when rejected
   - Ready for pickup (`siap_diambil_at` or `updated_at` fallback)
   - Completed (`qr_digunakan_at` + `qr_digunakan_oleh`)
4. **Response** — Ringkasan + vertical timeline; **Unduh PDF Surat** when `surat_terbit` exists and `pastikanFilePdf()` succeeds (lazy regen if file missing); **Kembali ke Rekap**.

## API Endpoints (if applicable)

| Method | URI | Purpose | Auth |
|--------|-----|---------|------|
| GET | `/admin/rekap-pengajuan/{pengajuan}` | Full-page detail + timeline | admin |
| GET | `/admin/surat-diproses/{pengajuan}/pdf/unduh` | PDF download (reused) | admin |

No public JSON API.

## Decisions & Trade-offs

- Route uses `/admin/rekap-pengajuan/{pengajuan}` (existing naming) rather than the plan’s literal `/admin/rekap/{id}` — see [ADR-023](../decisions/023-rekap-timeline-detail-page.md).
- Siap-diambil actor uses `diterbitkan_oleh` (fallback `diverifikasi_oleh`) because the plan’s data model does not add `siap_diambil_oleh`.
- Timeline logic lives in the Livewire component (no service class) per project architecture convention.

## Related

- [Rekap Pengajuan & Reporting](rekap-pengajuan.md)
- [Surat Diproses](surat-diproses.md)
- [QR Sekali Pakai](qr-sekali-pakai.md)
- [Unduh/Cetak Surat Warga](unduh-surat-warga.md)
- [ADR-023](../decisions/023-rekap-timeline-detail-page.md)
- [ADR-024](../decisions/024-hybrid-pdf-lazy-regenerate.md)
- Phase 08 US-8.7
