---
paths:
  - 'app/Livewire/Pengajuan/**'
---

# Pengajuan

## pengajuan_surat table naming
Phase 03 uses table `pengajuan_surat` (singular). Model PengajuanSurat sets protected $table. Route pengajuan-surat.create is warga-only under role:warga.

## dokumen_persyaratan (US-3.2)
Table `dokumen_persyaratan` (singular). Model DokumenPersyaratan. jenis_dokumen values: KTP, KK. Files stored on private `local` disk under `pengajuan-dokumen/{pengajuan_id}/`. Required upload slots detected from `jenis_surat.persyaratan_dokumen` text (KTP / KK / Kartu Keluarga keywords). US-3.3 blocks submit when required docs missing — `rules()` sets `required` on dokumenKtp/dokumenKk when detected in requiredDokumenTypes.
