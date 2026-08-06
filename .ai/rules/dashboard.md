---
paths:
  - 'app/Livewire/Dashboard/**'
---

# Dashboard

## Dashboard aging thresholds as component constants
US-8.1/8.2 dashboards are Livewire page components (AdminDashboard, WargaDashboard). Aging thresholds live as public const on AdminDashboard (3/7 diajukan, 5/10 diproses); elapsed amber for warga diajukan >7 on WargaDashboard. Status entered-at helpers are on PengajuanSurat (waktuMasukStatusSaatIni, hariDiStatusSaatIni, statusBadgeColor). Historical disetujui counts in diproses card via statusDiprosesDashboard().
