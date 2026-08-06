# Phase 02 - Jenis Surat & Persyaratan Dokumen (Master Data)

**Sprint Goal**: Membangun modul pengelolaan data jenis surat keterangan beserta persyaratan dokumennya, sebagai master data acuan bagi proses pengajuan warga.

**Estimated Duration**: 2-3 days

**Depends on**: Phase 01 (Authentication & Role Management)

**Note:** Modul ini menyediakan data referensi (`jenis_surat`) yang dipakai oleh modul Pengajuan Surat (Phase 03). Admin harus mengisi data ini terlebih dahulu sebelum warga dapat mengajukan surat.

---

## Why This Feature

- Warga saat ini tidak mengetahui persyaratan dokumen sebelum datang ke kantor desa, menyebabkan kunjungan berulang
- Terdapat 3 jenis surat utama (Domisili, Kelahiran/Kematian, Tidak Mampu) dengan persyaratan berbeda-beda
- Admin perlu dapat menambah/mengubah jenis surat tanpa mengubah kode program — data-driven, bukan hardcoded

---

## User Stories

### US-2.1: Kelola Data Jenis Surat (Admin)

**As an** admin/petugas desa
**I want** to add, edit, and view types of surat keterangan
**So that** the list of available letters stays accurate and up to date

**Acceptance Criteria:**
- [ ] Halaman daftar jenis surat (list + pencarian)
- [ ] Form tambah/ubah: nama_surat, deskripsi, persyaratan_dokumen
- [ ] Validasi nama_surat wajib diisi dan tidak duplikat
- [ ] Hanya role admin yang dapat mengakses halaman ini

### US-2.2: Tampilan Persyaratan Dokumen untuk Warga

**As a** warga
**I want** to view document requirements for each type of letter before applying
**So that** I can prepare complete documents and avoid repeat visits to the office

**Acceptance Criteria:**
- [ ] Halaman warga menampilkan daftar jenis surat beserta deskripsi dan persyaratan dokumen
- [ ] Warga dapat membuka detail per jenis surat sebelum memilih untuk mengajukan
- [ ] Tampilan responsif (dapat diakses dari smartphone)

### US-2.3: Akses Publik ke Informasi Persyaratan Dokumen

**As a** calon pemohon (belum punya akun / belum login)
**I want** to view document requirements without needing to register or log in first
**So that** I can decide whether to apply and prepare documents before creating an account

**Acceptance Criteria:**
- [ ] Halaman daftar jenis surat & persyaratan dapat diakses tanpa login (route publik, dikecualikan dari middleware auth Phase 01 US-1.3)
- [ ] Halaman menampilkan ajakan "Daftar/Login untuk Mengajukan" bagi pengunjung yang belum punya akun
- [ ] Konten tetap read-only untuk pengunjung publik — tidak bisa submit pengajuan tanpa login

**Data Model:**
```
jenis_surat
  - id (PK, AI)
  - nama_surat (varchar 100)
  - deskripsi (text)
  - persyaratan_dokumen (text)
  - timestamps
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-2.1 Kelola Data Jenis Surat | 3 | Must |
| 2 | US-2.2 Tampilan Persyaratan Dokumen | 2 | Must |
| 3 | US-2.3 Akses Publik Persyaratan Dokumen | 2 | Must |

**Total Story Points: 7**

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Admin menghapus jenis surat yang sudah dipakai di pengajuan lama | Soft delete, atau larangan hapus jika masih direferensikan oleh `pengajuan_surat` |
| Persyaratan dokumen ditulis bebas teks sehingga format tidak konsisten | Sediakan placeholder/contoh format saat admin mengisi field |
