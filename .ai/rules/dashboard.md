---
paths:
  - 'app/Livewire/Dashboard/**'
---

# Dashboard

## Dashboard aging thresholds as component constants
US-8.1/8.2 dashboards are Livewire page components (AdminDashboard, WargaDashboard). Aging thresholds live as public const on AdminDashboard (3/7 diajukan, 5/10 diproses); elapsed amber for warga diajukan >7 on WargaDashboard. Status entered-at helpers are on PengajuanSurat (waktuMasukStatusSaatIni, hariDiStatusSaatIni, statusBadgeColor). Historical disetujui counts in diproses card via statusDiprosesDashboard().

## Warga dashboard is status-first (not welcome chrome)
US-8.2 H1 answers “status surat”; “Dashboard Warga” is a small label only. Hero includes progress track via WargaDashboard::langkahAlur / indeksLangkahAktif. Riwayat is a linked list (not admin table). Keep data-test selectors used by e2e/dashboard.spec.ts. Diproses copy must not promise unduh; Unduh Bukti Pengambilan only when dapatUnduhSurat() (siap_diambil/selesai).
