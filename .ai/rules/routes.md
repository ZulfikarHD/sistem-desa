---
paths:
  - routes/web.php
---

# Routes

## Persyaratan dokumen excluded from auth
persyaratan-dokumen.index is registered outside the auth+verified group (US-2.3). Do not wrap it in role:warga again. Admin jenis-surat stays inside role:admin.

## US-7.6 warga unduh/cetak surat routes
Warga-only routes pengajuan-surat.unduh-surat and pengajuan-surat.cetak-surat under role:warga. Owner-only; status must be siap_diambil|selesai via PengajuanSurat::dapatUnduhSurat(); call pastikanFilePdf() then serve — never mint new QR/token. Unduh=download attachment; cetak=inline PDF response. UI labels: Unduh/Cetak Bukti Pengambilan.

## Surat Diproses admin routes
Admin routes: surat-diproses.index, surat-diproses.pdf.show, surat-diproses.pdf.download, surat-diproses.show. PDF routes must stay before {pengajuan} show.

## Dashboard routes are Livewire pages
dashboard and dashboard.admin are Route::livewire to WargaDashboard / AdminDashboard (US-8.1/8.2), not Route::view stubs.

## PDF routes use pastikanFilePdf
Warga unduh/cetak and admin surat-diproses.pdf.show/download call suratTerbit->pastikanFilePdf() before Storage download/response. Still never regenerate QR token. 404 only if surat_terbit null or regen fails.
