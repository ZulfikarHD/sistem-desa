# Phase 06 - Rekap Pengajuan & Reporting (Admin)

**Sprint Goal**: Membangun halaman rekapitulasi pengajuan surat bagi admin/petugas desa sebagai pengganti pencatatan buku manual, lengkap dengan filter dan export data untuk kebutuhan pelaporan desa.

**Estimated Duration**: 2-3 days

**Depends on**: Phase 03 (Pengajuan Surat Keterangan), Phase 04 (Verifikasi Pengajuan)

**Note:** Modul penutup yang menggabungkan seluruh data pengajuan dan verifikasi menjadi laporan siap pakai bagi Kepala Desa.

---

## Why This Feature

- Pencatatan pengajuan surat masih manual berbasis buku register, rentan hilang dan sulit direkapitulasi
- Kepala Desa dan petugas membutuhkan laporan berkala tanpa merekap manual dari buku
- Filter berdasarkan jenis surat, status, dan rentang tanggal diperlukan untuk analisis volume pengajuan

---

## User Stories

### US-6.1: Halaman Rekap Pengajuan dengan Filter

**As an** admin/petugas desa
**I want** to view all submissions in one table, filterable by letter type, status, and date range
**So that** I can generate periodic reports without manual recap

**Acceptance Criteria:**
- [ ] Tabel: nomor_pengajuan, nama warga, jenis surat, tanggal pengajuan, status, admin yang memverifikasi
- [ ] Filter: jenis_surat, status, rentang tanggal (dari - sampai)
- [ ] Pagination untuk data dalam jumlah besar
- [ ] Ringkasan jumlah pengajuan per status di bagian atas halaman (total, diajukan, disetujui, ditolak)

### US-6.2: Export Data Rekap

**As an** admin/petugas desa
**I want** to export the filtered recap data as CSV
**So that** I can share reports with the Kepala Desa outside the system

**Acceptance Criteria:**
- [ ] Tombol export CSV pada halaman rekap
- [ ] Export mengikuti filter yang sedang aktif (bukan seluruh data)
- [ ] Kolom export sesuai kolom tabel yang ditampilkan

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-6.1 Halaman Rekap dengan Filter | 5 | Must |
| 2 | US-6.2 Export Data Rekap | 3 | Should |

**Total Story Points: 8**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Data pengajuan sangat banyak, query filter menjadi lambat | Index kolom status, jenis_surat_id, tanggal_pengajuan di database |
| Export CSV format tidak terbuka rapi di Excel (masalah encoding) | Gunakan UTF-8 with BOM saat generate file CSV |
