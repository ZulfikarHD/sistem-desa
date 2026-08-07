# Livewire Components

Complete inventory of all Livewire components in Sistem Informasi Pelayanan Surat Keterangan.

## Component Map

```
app/Livewire/
├── Actions/
│   └── Logout.php
├── Dashboard/
│   ├── AdminDashboard.php
│   └── WargaDashboard.php
├── JenisSurat/
│   ├── DataJenisSurat.php
│   └── PersyaratanDokumen.php
├── Notifikasi/
│   └── PanelNotifikasi.php
├── Pengajuan/
│   ├── DetailPengajuanWarga.php
│   ├── FormPengajuanSurat.php
│   └── RiwayatPengajuan.php
├── Rekap/
│   ├── DetailRekapPengajuan.php
│   └── RekapPengajuan.php
├── SuratDiproses/
│   ├── DaftarSuratDiproses.php
│   └── DetailSuratDiproses.php
└── Verifikasi/
    ├── DaftarPengajuanVerifikasi.php
    ├── DetailPengajuanVerifikasi.php
    └── ScanQrPengambilan.php
```

---

## Actions

### `Actions/Logout`

| | |
|-|-|
| **Type** | Invokable class (not a full component) |
| **Route** | — (called via `wire:click`) |
| **Role** | Any authenticated |

Handles POST logout via Fortify, then redirects to `/`. Invoked from the navigation sidebar.

---

## Dashboard

### `Dashboard/AdminDashboard`

| | |
|-|-|
| **Route** | `GET /admin/dashboard` → `dashboard.admin` |
| **View** | `livewire/dashboard/admin-dashboard.blade.php` |
| **Role** | `admin` |
| **Stories** | US-1.2, US-8.1, ADR-022 |

Displays the admin operations center:
- **Aging cards** — count of pengajuan per status with elapsed-day threshold coloring (amber/red)
- **Urgent queue** — pengajuan `diajukan` oldest first with a quick "tangani" shortcut
- **Active table** — all non-terminal status (excludes `selesai`, `ditolak`)

Key methods: `tangani(int $pengajuanId)` redirects to detail verifikasi; `lihatDetail()` navigates to appropriate detail page based on status.

---

### `Dashboard/WargaDashboard`

| | |
|-|-|
| **Route** | `GET /dashboard` → `dashboard` |
| **View** | `livewire/dashboard/warga-dashboard.blade.php` |
| **Role** | `warga` |
| **Stories** | US-1.2, US-8.2 |

Displays the warga personal overview:
- **Hero status card** — latest active pengajuan with status badge + unduh button
- **Active pengajuan summary** — pengajuan that are not yet selesai/ditolak
- **Riwayat shortcut** — link to full riwayat page
- **Unread notifikasi count** — badge on bell icon

`penjelasanStatus(string $status)` returns a human-friendly explanation of each status for the warga.

---

## JenisSurat

### `JenisSurat/DataJenisSurat`

| | |
|-|-|
| **Route** | `GET /admin/jenis-surat` → `jenis-surat.index` |
| **View** | `livewire/jenis-surat/data-jenis-surat.blade.php` |
| **Role** | `admin` |
| **Stories** | US-2.1, ADR-006 |

Full CRUD for master jenis surat with inline Flux modal form:
- `create()` / `edit(int $id)` / `save()` — create or update a jenis surat record
- `softDelete(int $id)` — soft-delete if no active pengajuan references it
- `restore(int $id)` — restore soft-deleted record
- `confirmForceDelete(int $id)` / `forceDelete()` — permanent delete (only if no FK references)
- `$search` — live search filter on `nama_surat`
- `$showTrashed` — toggle to include soft-deleted records in the list

---

### `JenisSurat/PersyaratanDokumen`

| | |
|-|-|
| **Route** | `GET /persyaratan-dokumen` → `persyaratan-dokumen.index` |
| **View** | `livewire/jenis-surat/persyaratan-dokumen.blade.php` |
| **Role** | Public (no auth required) |
| **Stories** | US-2.2, US-2.3, ADR-007, ADR-008 |

Browse-and-detail view for jenis surat persyaratan, accessible by guests and authenticated users:
- `$search` — live search filter on `nama_surat`
- `openDetail(int $id)` / `closeDetail()` — show/hide inline detail modal with persyaratan dokumen

The component uses `layouts/public` for guests and the standard app layout for authenticated users.

---

## Notifikasi

### `Notifikasi/PanelNotifikasi`

| | |
|-|-|
| **Route** | Embedded component in the app navigation layout |
| **View** | `livewire/notifikasi/panel-notifikasi.blade.php` |
| **Role** | `warga` (embedded in warga layout) |
| **Stories** | US-5.1, US-5.2 |

Notification bell panel rendered inside the navigation bar:
- `bukaNotifikasi(int $notifikasiId)` — marks a notifikasi as `dibaca` then navigates to the related pengajuan detail
- `refreshNotifikasi()` — re-queries unread count (called by Livewire polling or event)
- Displays unread count badge; lists most recent notifikasi

---

## Pengajuan

### `Pengajuan/FormPengajuanSurat`

| | |
|-|-|
| **Route** | `GET /pengajuan-surat` → `pengajuan-surat.create` |
| **Alt Route** | `GET /pengajuan-surat/ajukan-ulang/{pengajuan}` → `pengajuan-surat.resubmit` |
| **View** | `livewire/pengajuan/form-pengajuan-surat.blade.php` |
| **Role** | `warga` |
| **Stories** | US-3.1, US-3.2, US-3.3, US-3.4 |

Dual-mode form — create new pengajuan or resubmit a rejected one:
- `mount(?PengajuanSurat $pengajuan)` — if `$pengajuan` is provided, enters resubmit mode: pre-fills keperluan and jenis surat, removes old rejected pengajuan on submit
- `updatedJenisSuratId()` — loads persyaratan dokumen list when jenis surat changes
- `requiredDokumenTypes()` — returns required dokumen types for the selected jenis surat
- `submit()` — validates, generates `nomor_pengajuan`, saves `pengajuan_surat` + `dokumen_persyaratan`, triggers notifikasi
- `removeDokumenKtp()` / `removeDokumenKk()` — removes a staged upload from LivewireTemp
- `createAnother()` — resets form to submit a new pengajuan

---

### `Pengajuan/RiwayatPengajuan`

| | |
|-|-|
| **Route** | `GET /riwayat-pengajuan` → `pengajuan-surat.riwayat` |
| **View** | `livewire/pengajuan/riwayat-pengajuan.blade.php` |
| **Role** | `warga` |
| **Stories** | US-3.4, US-5.3 |

Filterable list of all warga's own pengajuan:
- `$statusFilter` — filter by status; default shows all
- `statusOptions()` — returns status label map for the filter dropdown
- Links to `DetailPengajuanWarga` and `FormPengajuanSurat` (resubmit)

---

### `Pengajuan/DetailPengajuanWarga`

| | |
|-|-|
| **Route** | `GET /pengajuan-surat/detail/{pengajuan}` → `pengajuan-surat.show` |
| **View** | `livewire/pengajuan/detail-pengajuan-warga.blade.php` |
| **Role** | `warga` (must own the pengajuan) |
| **Stories** | US-5.3, US-7.6 |

Read-only detail page for warga:
- Shows status, nomor pengajuan, keperluan, log notifikasi
- Displays unduh/cetak buttons if `dapatUnduhSurat()` is true
- `mount()` enforces ownership via `abort_unless()`

---

## Rekap

### `Rekap/RekapPengajuan`

| | |
|-|-|
| **Route** | `GET /admin/rekap-pengajuan` → `rekap-pengajuan.index` |
| **View** | `livewire/rekap/rekap-pengajuan.blade.php` |
| **Role** | `admin` |
| **Stories** | US-6.1, US-6.2, ADR-013 |

Admin reporting page with multi-filter table and CSV export:
- `$jenisSuratFilter`, `$statusFilter`, `$tanggalDari`, `$tanggalSampai` — reactive filters
- `resetFilters()` — resets all filter properties
- `exportCsv()` — streams a UTF-8 BOM CSV response with current filter applied
- Summary counts (ringkasan) are computed **without** the status filter to always show full totals

---

### `Rekap/DetailRekapPengajuan`

| | |
|-|-|
| **Route** | `GET /admin/rekap-pengajuan/{pengajuan}` → `rekap-pengajuan.show` |
| **View** | `livewire/rekap/detail-rekap-pengajuan.blade.php` |
| **Role** | `admin` |
| **Stories** | US-8.7, ADR-023 |

Chronological process timeline for a single pengajuan:
- `timelineItems()` — assembles ordered events: submission, verifikasi log entries, surat terbit, siap_diambil, QR scan
- `formatWaktuWib(CarbonInterface $waktu)` — formats timestamps in WIB timezone
- `dapatUnduhPdf()` — checks if PDF is available to download from this admin view

---

## SuratDiproses

### `SuratDiproses/DaftarSuratDiproses`

| | |
|-|-|
| **Route** | `GET /admin/surat-diproses` → `surat-diproses.index` |
| **View** | `livewire/surat-diproses/daftar-surat-diproses.blade.php` |
| **Role** | `admin` |
| **Stories** | US-8.5 |

List of all pengajuan with status `diproses` or `siap_diambil`:
- `openDetail(int $pengajuanId)` — navigates to `DetailSuratDiproses`
- Shows nomor surat, nama warga, jenis surat, tanggal terbit, status

---

### `SuratDiproses/DetailSuratDiproses`

| | |
|-|-|
| **Route** | `GET /admin/surat-diproses/{pengajuan}` → `surat-diproses.show` |
| **View** | `livewire/surat-diproses/detail-surat-diproses.blade.php` |
| **Role** | `admin` |
| **Stories** | US-7.5, US-8.6 |

Detail page for a single surat in progress; allows admin to set the pickup date:
- `$tanggalPengambilan` — date picker value (min = today)
- `jamKerjaPreview()` — calls `SuratTerbit::jamKerjaLabelUntuk()` live as user selects a date
- `isTanggalPengambilanSiap()` — validates the selected date is a working day before enabling submit
- `tandaiSiapDiambil()` — calls `SuratTerbit::tandaiSiapDiambil()` + creates notifikasi for warga
- `sudahLewatDiproses()` — flags if the letter has been in `diproses` longer than threshold
- `suratPdfExists()` — verifies the PDF file is physically present on disk

---

## Verifikasi

### `Verifikasi/DaftarPengajuanVerifikasi`

| | |
|-|-|
| **Route** | `GET /admin/verifikasi` → `verifikasi.index` |
| **View** | `livewire/verifikasi/daftar-pengajuan-verifikasi.blade.php` |
| **Role** | `admin` |
| **Stories** | US-4.1, US-8.3 |

Filterable list of all pengajuan across all statuses:
- `$statusFilter` — default `diajukan`; can switch to any status
- `statusOptions()` — returns the full status label map
- `openDetail(int $pengajuanId)` — navigates to `DetailPengajuanVerifikasi`

---

### `Verifikasi/DetailPengajuanVerifikasi`

| | |
|-|-|
| **Route** | `GET /admin/verifikasi/{pengajuan}` → `verifikasi.show` |
| **View** | `livewire/verifikasi/detail-pengajuan-verifikasi.blade.php` |
| **Role** | `admin` |
| **Stories** | US-4.2, US-4.3, US-8.4, ADR-012, ADR-020 |

Core verification page — preview documents, approve, or reject:
- `canVerify()` — only allows action when status is `diajukan`
- `setujui()` — inside `DB::transaction` + `lockForUpdate`: sets status → `diproses`, generates PDF via `SuratTerbit::terbitkanUntuk()`, creates log_verifikasi, creates notifikasi for warga
- `tolak()` — sets status → `ditolak`, saves `catatan_admin`, creates log_verifikasi, creates notifikasi for warga
- `openTolakModal()` / `closeTolakModal()` — controls the rejection reason modal
- `isPreviewableImage()` / `isPreviewablePdf()` — determines inline preview type for uploaded dokumen
- `fileExists()` — verifies a private dokumen file exists on disk before rendering preview link

---

### `Verifikasi/ScanQrPengambilan`

| | |
|-|-|
| **Route** | `GET /admin/scan-qr-pengambilan` → `scan-qr-pengambilan.index` |
| **View** | `livewire/verifikasi/scan-qr-pengambilan.blade.php` |
| **Role** | `admin` |
| **Stories** | US-7.4, ADR-017 |

QR code scanner for document pickup:
- `$qrToken` — bound to a text input (or filled by a hardware QR scanner)
- `prosesScan(?string $token)` — calls `SuratTerbit::scanUntukPengambilan()`: marks QR `invalid`, transitions pengajuan → `selesai`, creates notifikasi
- `$hasilSukses` — drives the success/failure feedback UI
- Concurrent-safe via conditional `UPDATE WHERE qr_status = 'valid'`

---

## Related

- [Route Map](routes.md)
- [Database Architecture](database.md)
- [System Architecture](../architecture.md)
