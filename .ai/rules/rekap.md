---
paths:
  - 'app/Livewire/Rekap/**'
---

# Rekap

## Rekap summary vs table filters
US-6.1 rekap at /admin/rekap-pengajuan (role:admin). Table filters: jenis_surat, status, tanggal dari-sampai (default all empty). Ringkasan counts (total/diajukan/diproses/disetujui/ditolak) respect jenis+date filters but ignore status filter so cards stay useful. CSV export via Livewire streamDownload follows table filters, UTF-8 BOM. Phase 07 owns nomor_surat/tanggal_terbit columns — do not add here.
