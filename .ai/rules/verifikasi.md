---
paths:
  - 'app/Livewire/Verifikasi/**'
---

# Verifikasi

## Verifikasi pengajuan admin pages
US-4.1 list defaults statusFilter to diajukan. US-4.2 detail shows preview via admin-only routes verifikasi.dokumen.show/download registered BEFORE verifikasi.show. US-4.3 setujui/tolak with log_verifikasi + diverifikasi_oleh; tolak requires catatanAdmin. US-4.4 mount auto-transitions diajukan→diproses on first detail open; notification hook deferred to Phase 05 US-5.1.

## US-7.1 status flow supersedes US-4.4 auto diproses
Opening detail does NOT auto diajukan→diproses. canVerify only for diajukan. setujui: diajukan→disetujui (log+notif)→diproses (notif+triggerGenerateSurat). tolak: diajukan→ditolak only. Legacy diproses with diverifikasi_oleh null reset to diajukan via migration.

## US-7.2 triggerGenerateSurat generates PDF
US-7.2 fills triggerGenerateSurat: creates surat_terbit + PDF inside the setujui transaction. ditolak path still skips generation.

## Scan QR pengambilan admin page
US-7.4 ScanQrPengambilan is admin Livewire page route scan-qr-pengambilan.index. Camera uses BarcodeDetector + getUserMedia; manual token always available. prosesScan calls SuratTerbit::scanUntukPengambilan.

## US-7.5 panel Dokumen Siap Diambil on detail
DetailPengajuanVerifikasi shows tanggal_pengambilan date input + jam kerja preview + Dokumen Siap Diambil button only when canMarkSiapDiambil (diproses + suratTerbit). Button disabled until isTanggalPengambilanSiap. Action tandaiDokumenSiapDiambil calls SuratTerbit::tandaiSiapDiambil then redirects to verifikasi.index.
