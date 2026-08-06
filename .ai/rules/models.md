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

## US-7.4 QR scan once via conditional update
Scan QR pengambilan is ScanQrPengambilan at /admin/scan-qr-pengambilan. SuratTerbit::scanUntukPengambilan uses DB transaction + lockForUpdate + conditional UPDATE WHERE qr_status=valid; sets invalid + qr_digunakan_* + pengajuan selesai + notifikasi. No TTL. Re-download/terbitkanUntuk must not regenerate token.

## US-7.5 tandai siap diambil + jam kerja WIB
SuratTerbit::tandaiSiapDiambil moves diproses→siap_diambil, saves tanggal_pengambilan + jam_kerja_label, notifies warga. Validate dates via validasiTanggalPengambilan using Asia/Jakarta: reject past, Sat/Sun, and config desa.libur_nasional (tolak not warn). Jam labels from config desa.jam_kerja (Senin–Kamis 08–16, Jumat 08–16.30). UI lives on DetailPengajuanVerifikasi when status diproses + PDF exists. Detail warga tanggal display remains US-7.6; rekap columns US-7.7.

## US-7.6 dapatUnduhSurat helper
PengajuanSurat::dapatUnduhSurat() / statusBolehUnduhSurat() gate warga PDF download for diproses, siap_diambil, selesai when suratTerbit exists. Do not allow diajukan/disetujui/ditolak.
