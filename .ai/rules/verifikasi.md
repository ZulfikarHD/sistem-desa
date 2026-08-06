---
paths:
  - 'app/Livewire/Verifikasi/**'
---

# Verifikasi

## Verifikasi pengajuan admin pages
US-4.1 list defaults statusFilter to diajukan. US-4.2 detail shows preview via admin-only routes verifikasi.dokumen.show/download registered BEFORE verifikasi.show. US-4.3 setujui/tolak with log_verifikasi + diverifikasi_oleh; tolak requires catatanAdmin. US-4.4 mount auto-transitions diajukan→diproses on first detail open; notification hook deferred to Phase 05 US-5.1.
