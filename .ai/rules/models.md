---
paths:
  - app/Models/User.php
  - app/Models/JenisSurat.php
  - app/Models/PengajuanSurat.php
  - app/Models/SuratTerbit.php
---

# Models

## User display name stays column name
Phase 01 plan field nama is stored as Laravel column name. Domain fields nik, no_telepon, alamat, role were added. Do not rename name to nama without a coordinated migration across Fortify, profile, and factories.

## jenis_surat table name is not pluralized
Phase 02 data model uses table `jenis_surat` (singular). Model sets protected $table = 'jenis_surat'. Do not rename to jenis_surats. Unique index is on nama_surat.

## PengajuanSurat status labels and Phase 07 statuses
Constants include siap_diambil and selesai. Use PengajuanSurat::statusLabel() and statusOptions() for filters/UI — do not ucfirst raw status (breaks siap_diambil).

## surat_terbit table and PDF generate on approve
US-7.2: table surat_terbit (singular). On setujui→diproses, DetailPengajuanVerifikasi::triggerGenerateSurat calls SuratTerbit::terbitkanUntuk (DomPDF + bacon QR via GD). PDF on local disk surat-terbit/{id}/surat.pdf. Nomor format 470/{urut}/DS-WDN/{romawi}/{tahun} via config/desa.php. Reject never generates. Scan QR UI is US-7.4; unduh warga is US-7.6.

## US-7.3 nomor surat resmi format and year sequence
US-7.3: nomor_surat format {kode_klasifikasi}/{urut}/{kode_desa}/{bulanRomawi}/{tahun} via config/desa.php (default 470/.../DS-WDN/...). Sequential per calendar year of tanggal_terbit under Cache::lock + DB::transaction + lockForUpdate; unique column. Separate from nomor_pengajuan. Printed on PDF templates. Rekap display remains US-7.7; no manual override UI.
