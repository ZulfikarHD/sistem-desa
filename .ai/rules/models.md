---
paths:
  - app/Models/User.php
  - app/Models/JenisSurat.php
  - app/Models/PengajuanSurat.php
---

# Models

## User display name stays column name
Phase 01 plan field nama is stored as Laravel column name. Domain fields nik, no_telepon, alamat, role were added. Do not rename name to nama without a coordinated migration across Fortify, profile, and factories.

## jenis_surat table name is not pluralized
Phase 02 data model uses table `jenis_surat` (singular). Model sets protected $table = 'jenis_surat'. Do not rename to jenis_surats. Unique index is on nama_surat.

## PengajuanSurat status labels and Phase 07 statuses
Constants include siap_diambil and selesai. Use PengajuanSurat::statusLabel() and statusOptions() for filters/UI — do not ucfirst raw status (breaks siap_diambil).
