---
paths:
  - 'app/Livewire/Pengajuan/**'
---

# Pengajuan

## pengajuan_surat table naming
Phase 03 uses table `pengajuan_surat` (singular). Model PengajuanSurat sets protected $table. Route pengajuan-surat.create is warga-only under role:warga. US-3.1 form only — no document upload until US-3.2/3.3.
