# Phase 03 - Pengajuan Surat Keterangan (Warga)

**Sprint Goal**: Membangun alur pengajuan surat keterangan oleh warga, lengkap dengan pengunggahan dokumen persyaratan (KTP/KK) dan penomoran pengajuan otomatis.

**Estimated Duration**: 4-5 days

**Depends on**: Phase 01 (Authentication), Phase 02 (Jenis Surat & Persyaratan Dokumen)

**Note:** Ini adalah **modul inti (core transaction)** dari sistem — menggantikan proses manual pengisian formulir kertas di kantor desa. Menjadi prasyarat bagi Phase 04 (Verifikasi) dan Phase 06 (Rekap).

---

## Why This Feature

- Proses pengajuan surat saat ini sepenuhnya manual (formulir kertas, buku register)
- Data pengajuan rentan hilang, sulit direkapitulasi, dan tidak dapat ditelusuri cepat
- Pencatatan digital memerlukan nomor pengajuan unik untuk pelacakan status

---

## User Stories

### US-3.1: Form Pengajuan Surat Keterangan

**As a** warga
**I want** to fill out a submission form choosing letter type and purpose
**So that** I can formally request a surat keterangan

**Acceptance Criteria:**
- [x] Form: pilih jenis_surat (dropdown dari master data Phase 02), keperluan (textarea)
- [x] Validasi jenis surat wajib dipilih, keperluan wajib diisi
- [x] Sistem generate nomor_pengajuan unik otomatis saat submit
- [x] Status awal pengajuan: `diajukan`
- [x] tanggal_pengajuan otomatis terisi (tanggal submit)

### US-3.2: Unggah Dokumen Persyaratan

**As a** warga
**I want** to upload required documents (KTP and/or KK) as part of my submission
**So that** admin can verify my documents digitally without me visiting in person

**Acceptance Criteria:**
- [x] Area unggah file untuk KTP dan/atau KK sesuai persyaratan jenis surat terpilih
- [x] Validasi format file (jpg/png/pdf) dan ukuran maksimum (misal 2MB)
- [x] File disimpan via Laravel Storage; path tersimpan di tabel dokumen_persyaratan
- [x] Preview file yang sudah diunggah sebelum submit final

### US-3.3: Validasi Kelengkapan Pengajuan

**As a** warga
**I want** the system to check my submission is complete before saving
**So that** incomplete submissions are caught immediately, not after visiting the office

**Acceptance Criteria:**
- [x] Sistem menolak submit jika dokumen wajib belum diunggah
- [x] Pesan error jelas menunjukkan field/dokumen mana yang kurang
- [x] Data hanya tersimpan dengan status `diajukan` jika seluruh validasi lolos

### US-3.4: Ajukan Ulang Setelah Ditolak

**As a** warga whose submission was rejected
**I want** to resubmit using my previous submission as a starting point
**So that** I don't have to re-enter everything from scratch, addressing the repeat-effort problem the system was meant to solve

**Acceptance Criteria:**
- [x] Tombol "Ajukan Ulang" muncul pada baris berstatus `ditolak` di Halaman Status & Riwayat Pengajuan (Phase 05)
- [x] Form pengajuan baru terisi otomatis dari data pengajuan sebelumnya (jenis surat, keperluan); warga hanya perlu memperbaiki bagian yang kurang, misalnya mengunggah ulang dokumen
- [x] Pengajuan ulang tersimpan sebagai record baru di pengajuan_surat dengan nomor_pengajuan baru, status `diajukan`
- [x] catatan_admin dari pengajuan yang ditolak tetap dapat dilihat warga sebagai referensi perbaikan

**Data Model:**
```
pengajuan_surat
  - id (PK, AI)
  - user_id (FK -> users)
  - jenis_surat_id (FK -> jenis_surat)
  - nomor_pengajuan (varchar 30, unique)
  - keperluan (text)
  - status (enum: diajukan, diproses, disetujui, ditolak)
  - catatan_admin (text, nullable)
  - diverifikasi_oleh (FK -> users, nullable)
  - tanggal_pengajuan (date)
  - timestamps

dokumen_persyaratan
  - id (PK, AI)
  - pengajuan_id (FK -> pengajuan_surat)
  - jenis_dokumen (enum: KTP, KK)
  - file_path (varchar 255)
  - timestamps
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-3.1 Form Pengajuan Surat | 5 | Must |
| 2 | US-3.2 Unggah Dokumen Persyaratan | 5 | Must |
| 3 | US-3.3 Validasi Kelengkapan Pengajuan | 3 | Must |
| 4 | US-3.4 Ajukan Ulang Setelah Ditolak | 3 | Should |

**Total Story Points: 16**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Ukuran/format file upload tidak sesuai, warga awam kebingungan | Validasi jelas + instruksi format ditampilkan di halaman form |
| Nomor pengajuan collision jika dua warga submit bersamaan | Generate nomor via DB transaction/unique constraint, bukan hanya di sisi aplikasi |
| Warga meninggalkan form di tengah jalan, data tidak tersimpan | Simpan draft sementara di session (opsional, nice-to-have) |
