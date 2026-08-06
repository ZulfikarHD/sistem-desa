# Dashboard Warga (US-8.2)

## Overview

After login, warga see active submission status as hero content — jenis surat, large status badge, plain-language explanation, elapsed days, optional Unduh Surat, and prominent pickup schedule when `siap_diambil`. Secondary blocks cover recent history, notifications, and a non-dominant “Ajukan Surat Baru” CTA. Unread notifications also show a top banner that opens the bell panel.

## Architecture Diagram

```mermaid
flowchart TD
    A[Warga opens /dashboard] --> B[WargaDashboard Livewire]
    B --> C{Active pengajuan?}
    C -->|No| D[Empty hero + Ajukan Surat Sekarang]
    C -->|Yes| E[Hero cards per active status]
    E --> F[Unduh if diproses/siap_diambil]
    E --> G[Pickup date/jam if siap_diambil]
    B --> H[Riwayat 3 terbaru]
    B --> I[Notifikasi 3 unread-first]
    B --> J[Banner if unreadCount > 0]
    J --> K[Dispatch buka-panel-notifikasi]
```

## Data Model

Uses existing `pengajuan_surat`, `surat_terbit`, and `notifikasi`. No schema changes for US-8.2.

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Livewire | `app/Livewire/Dashboard/WargaDashboard.php` | Hero data, penjelasan status, riwayat, notif |
| Blade | `resources/views/livewire/dashboard/warga-dashboard.blade.php` | Hero + secondary sections |
| Panel | `resources/views/livewire/notifikasi/panel-notifikasi.blade.php` | Listens `buka-panel-notifikasi` |
| Routes | `routes/web.php` | `dashboard` → Livewire |
| Pest | `tests/Feature/DashboardWargaTest.php` | Feature coverage |
| Playwright | `e2e/dashboard.spec.ts` | E2E US-8.2 |

## Flow Explanation

1. **User triggers** — Warga opens `/dashboard` (`role:warga`).
2. **Request handling** — Livewire loads own active pengajuan (not selesai/ditolak), sorted oldest-in-status first.
3. **Business logic** — Map status → citizen-friendly copy; compute elapsed days; gate Unduh via `dapatUnduhSurat()`; prioritize unread notifs.
4. **Response** — Status-tinted hero cards; banner + list for unread; links to riwayat; CTA Ajukan Surat Baru below hero (or primary CTA when empty).

## API Endpoints (if applicable)

| Method | URI | Purpose | Auth |
|--------|-----|---------|------|
| GET | `/dashboard` | Warga dashboard page | warga |

## Decisions & Trade-offs

- “Lihat Semua Notifikasi” opens the existing bell panel (no dedicated notifikasi page in backlog).
- Hero color per status uses left border + light background per Phase 08 UI notes.
- Elapsed > 7 days while still `diajukan` uses amber text as a soft nudge.

## Related

- [Dashboard Admin (US-8.1)](dashboard-admin.md)
- [Notifikasi & Riwayat](notifikasi-pengajuan.md)
- [Unduh Surat Warga](unduh-surat-warga.md)
- [ADR-022](../decisions/022-dashboard-aging-and-status-helpers.md)
