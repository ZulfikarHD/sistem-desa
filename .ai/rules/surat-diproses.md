---
paths:
  - 'app/Livewire/SuratDiproses/**'
---

# Surat Diproses

## US-8.5/8.6 Surat Diproses pages
DaftarSuratDiproses at /admin/surat-diproses lists only status=diproses with pagination. DetailSuratDiproses owns tanggal_pengambilan + Siap Diambil (relocated from DetailPengajuanVerifikasi). Server validation uses after_or_equal WIB today + SuratTerbit::validasiTanggalPengambilan; date input min=today WIB. Admin PDF via surat-diproses.pdf.show/download registered BEFORE show route.
