# Phase 05 - Notifikasi In-App & Status/Riwayat Pengajuan (Warga)

**Sprint Goal**: Membangun sistem notifikasi in-app real-time dan halaman status/riwayat pengajuan bagi warga, sehingga warga mendapat kepastian tanpa perlu datang langsung ke kantor desa.

**Estimated Duration**: 3 days

**Depends on**: Phase 03 (Pengajuan Surat Keterangan), Phase 04 (Verifikasi Pengajuan)

**Note:** Modul ini melengkapi loop end-to-end sistem: begitu admin melakukan aksi verifikasi (Phase 04), warga langsung mendapat notifikasi dan bisa memantau status pengajuannya.

---

## Why This Feature

- Warga tidak memperoleh informasi status pengajuan secara transparan, menimbulkan keluhan berulang ke kantor desa
- Solusi notifikasi in-app dipilih (bukan WhatsApp Business API resmi) karena keterbatasan biaya/skala penelitian
- Riwayat pengajuan memberi transparansi layanan bagi warga

---

## User Stories

### US-5.1: Notifikasi Otomatis Perubahan Status

**As a** warga
**I want** to receive an in-app notification whenever my submission status changes
**So that** I know the outcome without needing to check manually

**Acceptance Criteria:**
- [ ] Setiap perubahan status (disetujui/ditolak) oleh admin memicu insert record baru di tabel notifikasi
- [ ] Notifikasi berisi pesan singkat (jenis surat, status baru)
- [ ] status_baca default `belum`

### US-5.2: Panel Notifikasi In-App

**As a** warga
**I want** to open a notification panel showing my recent notifications
**So that** I can quickly check what's new without opening full history

**Acceptance Criteria:**
- [ ] Icon/dropdown notifikasi di header, menampilkan badge jumlah belum dibaca
- [ ] Klik notifikasi menandai status_baca menjadi `dibaca`
- [ ] Klik notifikasi mengarahkan ke detail pengajuan terkait
- [ ] Interaktivitas dropdown menggunakan Alpine.js (tanpa reload halaman)

### US-5.3: Halaman Status & Riwayat Pengajuan

**As a** warga
**I want** to view the current status and full history of all my submissions
**So that** I can track progress and reference past requests

**Acceptance Criteria:**
- [ ] Tabel: nomor_pengajuan, jenis surat, tanggal pengajuan, status, catatan_admin (jika ditolak)
- [ ] Filter/sort berdasarkan status
- [ ] Detail per baris dapat dibuka untuk melihat info lengkap

**Data Model:**
```
notifikasi
  - id (PK, AI)
  - user_id (FK -> users)
  - pengajuan_id (FK -> pengajuan_surat)
  - pesan (text)
  - status_baca (enum: dibaca, belum)
  - created_at (timestamp)
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-5.1 Notifikasi Otomatis Perubahan Status | 3 | Must |
| 2 | US-5.2 Panel Notifikasi In-App | 3 | Must |
| 3 | US-5.3 Halaman Status & Riwayat Pengajuan | 3 | Must |

**Total Story Points: 9**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Notifikasi tidak real-time tanpa reload (butuh polling/websocket) | Gunakan polling interval sederhana (misal tiap 30 detik) via Alpine.js — cukup untuk skala penelitian |
| Warga tidak sadar ada notifikasi baru | Badge counter jelas + warna berbeda untuk item belum dibaca |
| Volume notifikasi menumpuk seiring waktu | Pagination pada panel notifikasi, tampilkan terbaru dulu |
