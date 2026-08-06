# Phase 07 - Penerbitan Surat Keterangan

**Sprint Goal**: Membangun mekanisme penerbitan dokumen surat keterangan resmi (PDF) setelah verifikasi disetujui, lengkap dengan status lanjutan `siap_diambil` → `selesai`, QR code **sekali pakai** (invalid setelah scan pertama), alur admin set tanggal pengambilan + jam kerja, notifikasi ke warga, serta unduh/cetak surat.

**Estimated Duration**: 4-5 days

**Depends on**: Phase 03–06 (sudah selesai). Phase ini **menambah** fitur di atas baseline yang sudah jalan — jangan rewrite plan Phase 03/04.

**Note:** Bab III (3.2 Objek Penelitian) menyatakan cakupan penelitian *"hingga penerbitan surat oleh pihak berwenang di desa"*, namun implementasi saat ini berhenti pada status `disetujui` tanpa menghasilkan dokumen surat yang bisa dipakai warga. Phase ini menutup celah tersebut.

---

## Baseline vs Target (penting)

Phase 03 & 04 **sudah diimplementasi**. Plan file Phase 03/04 tetap sebagai catatan historis — perubahan perilaku status dilakukan **di scope Phase 07**, bukan dengan mengedit ulang plan phase yang sudah selesai.

**As-is (kode sekarang):**
```
diajukan → diproses (admin buka detail, US-4.4) → disetujui | ditolak
```

**Target end-state (Phase 07):**
```
diajukan
    ├─→ disetujui          (verifikasi data OK)
    │       └─→ diproses   (PDF + nomor + QR digenerate)
    │               └─→ siap_diambil
    │                       └─→ selesai   (scan QR sekali → QR invalid)
    └─→ ditolak            (terminal)
```

Artinya Phase 07 termasuk **migrasi perilaku** di kode verifikasi (hapus auto `diajukan→diproses` saat buka detail; pindahkan `diproses` ke setelah `disetujui` + generate surat). Plan Phase 04 tidak diubah; hanya kode & tes yang tersentuh dicatat di US-7.1.

---

## Why This Feature

- Warga yang pengajuannya `disetujui` saat ini tidak memperoleh surat yang bisa dipakai
- Setelah verifikasi OK, warga perlu progres nyata: surat diproses → siap diambil → selesai
- Kepastian **kapan** diambil (tanggal + jam kerja kantor)
- QR **sekali pakai**: scan pertama sukses → invalid permanen; scan ulang (admin mana pun) ditolak

---

## Makna Status (setelah Phase 07)

| Status | Arti |
|--------|------|
| `diajukan` | Menunggu verifikasi data |
| `disetujui` | Verifikasi data OK — belum berarti surat siap |
| `ditolak` | Verifikasi gagal — alur berhenti |
| `diproses` | **Pasca-disetujui**: PDF + nomor surat + QR digenerate |
| `siap_diambil` | Admin set tanggal pengambilan; warga dapat notifikasi |
| `selesai` | Admin scan QR sukses sekali; **QR invalid** |

---

## User Stories

### US-7.1: Migrasi Alur Status (sentuh kode Phase 04 yang sudah ada)

**As the** system
**I want** status transitions aligned to the target end-state
**So that** `diproses` means letter preparation after approval, not “admin opened the review page”

**Acceptance Criteria:**
- [ ] Hapus/nonaktifkan transisi otomatis `diajukan → diproses` saat admin membuka detail (perilaku US-4.4 lama di kode)
- [ ] Admin setujui dari `diajukan` (atau status menunggu verifikasi) → `disetujui`
- [ ] Admin tolak → `ditolak` (terminal; tidak masuk `diproses`)
- [ ] Setelah `disetujui`, sistem otomatis lanjut `diproses` + trigger generate PDF (US-7.2)
- [ ] Update tes/feature yang mengunci perilaku lama US-4.4 agar sesuai target
- [ ] Filter/ringkasan di Phase 05 & 06 tetap konsisten dengan status baru (`siap_diambil`, `selesai`)

### US-7.2: Generate Surat PDF Saat Masuk Diproses

**As the** system
**I want** to generate a PDF surat keterangan when a submission enters `diproses` after approval
**So that** warga gets a real document as part of letter preparation

**Acceptance Criteria:**
- [ ] Setelah `disetujui`, sistem otomatis lanjut ke `diproses` dan generate PDF surat
- [ ] Template PDF berisi: kop surat desa, nomor surat resmi, data pemohon (nama, NIK, alamat), jenis surat, keperluan, tanggal terbit, nama & jabatan penandatangan, **QR sekali pakai (US-7.4)**
- [ ] PDF disimpan ke storage, path tercatat pada `surat_terbit`
- [ ] Template berbeda per jenis_surat sesuai kebutuhan format
- [ ] `ditolak` tidak generate PDF/QR

### US-7.3: Nomor Surat Resmi Otomatis

**As an** admin/petugas desa
**I want** the system to generate an official surat number following the village's numbering convention
**So that** issued letters comply with standard administrative format

**Acceptance Criteria:**
- [ ] Format nomor surat mengikuti pola administrasi desa (misal: `470/{urut}/DS-WDN/{bulan romawi}/{tahun}`)
- [ ] Nomor unik dan berurutan per tahun, terpisah dari nomor_pengajuan
- [ ] Nomor tercetak pada PDF

### US-7.4: QR Code Sekali Pakai (Invalid Setelah Scan Pertama)

**As an** admin/petugas desa
**I want** each issued surat to carry a QR that becomes permanently invalid after the first successful scan
**So that** pickup is registered exactly once — scanning again (any admin) must fail

**Acceptance Criteria:**
- [ ] Generate `qr_token` unik + `qr_status = valid`, cetak QR pada PDF
- [ ] Tidak ada TTL/kadaluarsa waktu — valid sampai scan sukses sekali
- [ ] Halaman/aksi "Scan QR Pengambilan" (kamera atau input token manual)
- [ ] Scan hanya jika status `siap_diambil` **dan** `qr_status = valid`
- [ ] Scan pertama sukses: `qr_status = invalid` (permanen), isi `qr_digunakan_at` + `qr_digunakan_oleh`, status → `selesai`, notifikasi warga
- [ ] Scan ulang token `invalid`: **selalu ditolak** ("QR sudah digunakan / tidak valid") — tanpa pengecualian antar-admin
- [ ] Token tidak dikenal / status belum `siap_diambil`: ditolak
- [ ] Enforcement **server-side** (conditional update `WHERE qr_status = valid`)

**Catatan desain (wajib):**
- Bisa di-scan berkali-kali = **tidak memenuhi AC**
- Unduh PDF ulang tidak regenerasi token / tidak mengembalikan ke `valid`
- Token opaque (bukan NIK plain text)

### US-7.5: Tandai Dokumen Siap Diambil (Admin) + Notifikasi Warga

**As an** admin/petugas desa
**I want** to set a pickup date and mark a processed document as ready for pickup
**So that** warga knows when to come during government work hours

**Acceptance Criteria:**
- [ ] Dari status `diproses` (PDF sudah ada), admin pilih **tanggal pengambilan**
- [ ] Jam = jam kerja kantor pemerintah Indonesia (bukan time-picker bebas):
  - Senin–Kamis: 08.00–16.00 WIB
  - Jumat: 08.00–16.30 WIB
  - Sabtu–Minggu / libur nasional: tutup (tolak atau peringatan)
- [ ] Tombol **"Dokumen Siap Diambil"** aktif setelah tanggal valid
- [ ] Status `diproses` → `siap_diambil`; simpan `tanggal_pengambilan`
- [ ] Notifikasi warga: jenis surat, `siap_diambil`, tanggal, jam kerja
- [ ] Riwayat warga menampilkan status + tanggal & jam kerja
- [ ] Scan QR (US-7.4) → `selesai` + QR `invalid`

### US-7.6: Unduh/Cetak Surat oleh Warga

**As a** warga
**I want** to download or print my surat once it has been processed
**So that** I can bring/use the document (termasuk QR) for pickup

**Acceptance Criteria:**
- [ ] Tombol "Unduh Surat" pada baris `diproses`, `siap_diambil`, atau `selesai` (Phase 05)
- [ ] Mengunduh PDF yang sudah digenerate
- [ ] Unduh ulang kapan saja (file tetap ada; **bukan** berarti QR bisa dipakai ulang)
- [ ] Detail menampilkan tanggal pengambilan + jam kerja jika sudah di-set (US-7.5)

### US-7.7: Riwayat Penerbitan Surat (Admin)

**As an** admin/petugas desa
**I want** to see issued letters with official numbers on the rekap
**So that** I have an archive record for Kepala Desa

**Acceptance Criteria:**
- [ ] Rekap (Phase 06) menampilkan nomor_surat, tanggal_terbit, tanggal_pengambilan, status QR (`valid`/`invalid`)
- [ ] Bisa difilter dan diekspor bersama data rekap yang ada

**Data Model:**
```
surat_terbit
  - id (PK, AI)
  - pengajuan_id (FK -> pengajuan_surat, unique)
  - nomor_surat (varchar 50, unique)
  - file_path (varchar 255)
  - tanggal_terbit (date)
  - tanggal_pengambilan (date, nullable)
  - jam_kerja_label (varchar 100, nullable)
  - qr_token (varchar 64, unique)
  - qr_status (enum: valid, invalid) — default valid; setelah scan → invalid permanen
  - qr_digunakan_at (timestamp, nullable)
  - qr_digunakan_oleh (FK -> users, nullable)
  - diterbitkan_oleh (FK -> users)
  - timestamps

pengajuan_surat.status (perluasan dari yang sudah ada)
  - diajukan | diproses | disetujui | ditolak | siap_diambil | selesai
  # urutan target: diajukan → disetujui|ditolak → (jika disetujui) diproses → siap_diambil → selesai
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-7.1 Migrasi Alur Status (kode Phase 04) | 3 | Must |
| 2 | US-7.2 Generate Surat PDF Saat Diproses | 5 | Must |
| 3 | US-7.3 Nomor Surat Resmi Otomatis | 3 | Must |
| 4 | US-7.4 QR Code Sekali Pakai | 5 | Must |
| 5 | US-7.5 Tandai Dokumen Siap Diambil + Notifikasi | 5 | Must |
| 6 | US-7.6 Unduh/Cetak Surat oleh Warga | 3 | Must |
| 7 | US-7.7 Riwayat Penerbitan Surat (Admin) | 2 | Should |

**Total Story Points: 26**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Migrasi status merusak tes/alur Phase 04–06 yang sudah hijau | US-7.1 eksplisit; jalankan suite terkait verifikasi, notifikasi, rekap setelah migrasi |
| Format surat berbeda per jenis | Template Blade terpisah per jenis_surat → PDF (mis. DomPDF) |
| Dua admin scan QR bersamaan | Conditional update `WHERE qr_status = valid` |
| Unduh PDF dianggap QR baru | Satu token per `surat_terbit`; unduh tidak regenerasi |
| Admin lupa set tanggal | Tombol siap diambil wajib tanggal valid; notifikasi memuat jam kerja |

---

## Cross-Reference

- **Phase 03–04 (selesai)** — plan file tidak diubah; baseline as-is di kode
- **US-7.1** — migrasi perilaku verifikasi/status di kode yang sudah ada (bukan rewrite plan Phase 04)
- **Phase 05** — notifikasi + unduh + tampilan tanggal/jam untuk status baru
- **Phase 06** — kolom nomor_surat, tanggal_pengambilan, status QR di rekap
