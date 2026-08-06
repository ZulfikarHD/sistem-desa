# Phase 07 - Penerbitan Surat Keterangan

**Sprint Goal**: Membangun mekanisme penerbitan dokumen surat keterangan resmi (PDF) secara otomatis setelah pengajuan disetujui, beserta halaman unduh/cetak bagi warga — menutup celah antara status "disetujui" dan surat fisik yang benar-benar diterima warga.

**Estimated Duration**: 3-4 days

**Depends on**: Phase 04 (Verifikasi Pengajuan & Log Verifikasi)

**Note:** Bab III (3.2 Objek Penelitian) menyatakan cakupan penelitian *"hingga penerbitan surat oleh pihak berwenang di desa"*, namun rancangan dan implementasi saat ini berhenti pada status `disetujui` tanpa menghasilkan dokumen surat yang bisa dipakai warga. Phase ini menutup celah tersebut agar sistem benar-benar menyelesaikan alur layanan, bukan hanya alur administratif internal.

---

## Why This Feature

- Warga yang pengajuannya `disetujui` saat ini tidak memperoleh apa pun yang bisa dipakai — status berubah, tapi tidak ada surat di tangan
- Tujuan penelitian sejak awal adalah menggantikan proses manual hingga surat diterbitkan, bukan hanya digitalisasi pencatatan pengajuan
- Tanpa dokumen resmi yang bisa diunduh, warga tetap harus datang ke kantor desa untuk mengambil surat fisik — sebagian masalah "kunjungan berulang" belum sepenuhnya terselesaikan

---

## User Stories

### US-7.1: Generate Surat PDF Otomatis Setelah Disetujui

**As the** system
**I want** to automatically generate a PDF surat keterangan when an admin approves a submission
**So that** warga has a usable document immediately after approval, without manual drafting by staff

**Acceptance Criteria:**
- [ ] Saat status pengajuan berubah menjadi `disetujui` (Phase 04, US-4.3), sistem otomatis generate PDF surat
- [ ] Template PDF berisi: kop surat desa, nomor surat resmi, data pemohon (nama, NIK, alamat), jenis surat, keperluan, tanggal terbit, nama & jabatan penandatangan
- [ ] PDF disimpan ke storage, path tercatat pada tabel surat_terbit
- [ ] Template berbeda per jenis_surat (Domisili, Kelahiran/Kematian, Tidak Mampu) sesuai kebutuhan format masing-masing

### US-7.2: Nomor Surat Resmi Otomatis

**As an** admin/petugas desa
**I want** the system to generate an official surat number following the village's numbering convention
**So that** issued letters comply with standard administrative format, not just an internal tracking number

**Acceptance Criteria:**
- [ ] Format nomor surat mengikuti pola administrasi desa (misal: `470/{urut}/DS-WDN/{bulan romawi}/{tahun}`)
- [ ] Nomor surat unik dan berurutan per tahun berjalan, terpisah dari nomor_pengajuan (yang hanya untuk tracking internal)
- [ ] Nomor surat tercetak pada dokumen PDF

### US-7.3: Unduh/Cetak Surat oleh Warga

**As a** warga
**I want** to download or print my approved surat keterangan
**So that** I can use the document for its intended purpose without visiting the office

**Acceptance Criteria:**
- [ ] Halaman Status & Riwayat Pengajuan (Phase 05) menampilkan tombol "Unduh Surat" pada baris berstatus `disetujui`
- [ ] Klik tombol mengunduh file PDF surat yang sudah digenerate
- [ ] Warga dapat mengunduh ulang kapan saja selama akun aktif (tidak hilang setelah diunduh sekali)

### US-7.4: Riwayat Penerbitan Surat (Admin)

**As an** admin/petugas desa
**I want** to see a log of all issued letters with their official numbers
**So that** I have an official record for village archives and reporting to Kepala Desa

**Acceptance Criteria:**
- [ ] Kolom tambahan pada Rekap Pengajuan (Phase 06) menampilkan nomor_surat dan tanggal_terbit untuk baris disetujui
- [ ] Data dapat difilter dan diekspor bersama data rekap yang sudah ada

**Data Model:**
```
surat_terbit
  - id (PK, AI)
  - pengajuan_id (FK -> pengajuan_surat, unique)
  - nomor_surat (varchar 50, unique)
  - file_path (varchar 255)
  - tanggal_terbit (date)
  - diterbitkan_oleh (FK -> users, admin yang menyetujui)
  - timestamps
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-7.1 Generate Surat PDF Otomatis | 5 | Must |
| 2 | US-7.2 Nomor Surat Resmi Otomatis | 3 | Must |
| 3 | US-7.3 Unduh/Cetak Surat oleh Warga | 3 | Must |
| 4 | US-7.4 Riwayat Penerbitan Surat (Admin) | 2 | Should |

**Total Story Points: 13**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Format surat berbeda antar jenis surat menyulitkan satu template generik | Buat template Blade/HTML terpisah per jenis_surat, dirender ke PDF via library (misal DomPDF) |
| Surat PDF tanpa tanda tangan basah tidak sepenuhnya sah secara hukum | Cantumkan catatan "surat sah tanpa tanda tangan basah, dicetak dari sistem resmi desa" atau sediakan QR verifikasi; tanda tangan elektronik penuh tetap sebagai saran pengembangan lanjut |
| Nomor surat bentrok jika dua surat disetujui bersamaan | Generate nomor via DB transaction dengan locking, bukan hitung manual di aplikasi |

---

## Cross-Reference

Phase ini melengkapi:
- **Phase 04** (US-4.3 Setujui/Tolak Pengajuan) — trigger otomatis generate surat saat status menjadi `disetujui`
- **Phase 05** (US-5.3 Halaman Status & Riwayat Pengajuan) — lokasi tombol unduh surat
- **Phase 06** (US-6.1 Halaman Rekap dengan Filter) — tambahan kolom nomor_surat pada laporan
