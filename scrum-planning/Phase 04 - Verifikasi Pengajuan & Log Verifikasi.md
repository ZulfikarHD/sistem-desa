# Phase 04 - Verifikasi Pengajuan & Log Verifikasi (Admin)

**Sprint Goal**: Membangun modul verifikasi bagi admin/petugas desa untuk memeriksa kelengkapan dokumen dan menyetujui/menolak pengajuan surat, lengkap dengan pencatatan log audit.

**Estimated Duration**: 3-4 days

**Depends on**: Phase 03 (Pengajuan Surat Keterangan)

**Note:** Modul ini menggantikan proses pemeriksaan berkas manual oleh petugas dan menjadi dasar bagi modul Notifikasi (Phase 05) — setiap keputusan di sini memicu notifikasi ke warga.

---

## Why This Feature

- Proses verifikasi manual seluruhnya bergantung pada kecepatan petugas memeriksa berkas satu per satu
- Tidak ada jejak audit (log) atas keputusan setuju/tolak yang bisa ditelusuri kembali
- Petugas perlu melihat pratinjau dokumen yang diunggah tanpa mencetak fisik

---

## User Stories

### US-4.1: Daftar Pengajuan Menunggu Verifikasi

**As an** admin/petugas desa
**I want** to see a list of submissions with status "diajukan"
**So that** I know which requests need my review

**Acceptance Criteria:**
- [ ] Halaman daftar pengajuan, default filter status = `diajukan`
- [ ] Menampilkan: nomor_pengajuan, nama warga, jenis surat, tanggal pengajuan
- [ ] Klik baris untuk membuka detail pengajuan

### US-4.2: Detail Pengajuan & Pratinjau Dokumen

**As an** admin/petugas desa
**I want** to view submission details along with a preview of uploaded documents
**So that** I can check completeness without printing physical copies

**Acceptance Criteria:**
- [ ] Halaman detail menampilkan data pengajuan lengkap + keperluan
- [ ] Pratinjau dokumen (KTP/KK) langsung di halaman (image/PDF viewer)
- [ ] Tombol Setujui dan Tolak tersedia di halaman ini

### US-4.3: Setujui / Tolak Pengajuan

**As an** admin/petugas desa
**I want** to approve or reject a submission, optionally with a note
**So that** the process is documented and warga get clear results

**Acceptance Criteria:**
- [ ] Aksi "Setujui" mengubah status pengajuan menjadi `disetujui`
- [ ] Aksi "Tolak" wajib mengisi catatan_admin (alasan penolakan), status menjadi `ditolak`
- [ ] Setiap aksi tercatat di log_verifikasi (admin_id, aksi, keterangan, timestamp)
- [ ] Kolom diverifikasi_oleh pada pengajuan_surat terisi user_id admin yang melakukan aksi
- [ ] Setelah aksi selesai, pengajuan hilang dari daftar "menunggu verifikasi"

### US-4.4: Transisi Status Otomatis ke "Diproses"

**As a** warga
**I want** my submission status to update to "diproses" once an admin opens it for review
**So that** I get more granular visibility into where my request stands, not just "diajukan" until a final decision

**Acceptance Criteria:**
- [ ] Saat admin membuka Halaman Detail Pengajuan (US-4.2) untuk pertama kali, status otomatis berubah dari `diajukan` menjadi `diproses`
- [ ] Perubahan status ini memicu notifikasi in-app ke warga (terhubung ke Phase 05, US-5.1, setelah phase tersebut dibangun)
- [ ] Status `diproses` tetap dapat berlanjut ke `disetujui` atau `ditolak` melalui US-4.3

**Data Model:**
```
log_verifikasi
  - id (PK, AI)
  - pengajuan_id (FK -> pengajuan_surat)
  - admin_id (FK -> users)
  - aksi (enum: setujui, tolak)
  - keterangan (text, nullable)
  - created_at (timestamp)
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-4.1 Daftar Pengajuan Menunggu Verifikasi | 3 | Must |
| 2 | US-4.2 Detail & Pratinjau Dokumen | 5 | Must |
| 3 | US-4.3 Setujui/Tolak Pengajuan | 5 | Must |
| 4 | US-4.4 Transisi Status ke "Diproses" | 2 | Should |

**Total Story Points: 15**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Admin lupa mengisi alasan penolakan | Validasi wajib isi catatan_admin saat aksi "tolak" |
| File dokumen korup/tidak bisa di-preview | Fallback tombol download langsung jika preview gagal |
| Dua admin memverifikasi pengajuan yang sama secara bersamaan | Row-level lock atau cek status terkini sebelum commit aksi |
