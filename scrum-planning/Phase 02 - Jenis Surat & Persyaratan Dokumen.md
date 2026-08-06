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

### US-2.1: Kelola Data Jenis Surat (Admin) — ✅ DONE

**As an** admin/petugas desa
**I want** to add, edit, and view types of surat keterangan
**So that** the list of available letters stays accurate and up to date

**Acceptance Criteria:**
- [x] Halaman daftar jenis surat (list + pencarian)
- [x] Form tambah/ubah: nama_surat, deskripsi, persyaratan_dokumen
- [x] Validasi nama_surat wajib diisi dan tidak duplikat
- [x] Hanya role admin yang dapat mengakses halaman ini

**Implemented extras (beyond AC wording):**
- Soft delete (**Arsipkan**) + restore (**Pulihkan**) + hard delete (**Hapus Permanen** dari arsip)
- `persyaratan_dokumen` wajib; `deskripsi` opsional
- Placeholder contoh format pada field persyaratan

**Primary route:** `/admin/jenis-surat` (`jenis-surat.index`) — Livewire `DataJenisSurat`  
**Docs:** `docs/dev-docs/features/jenis-surat.md`, `docs/user-docs/guides/jenis-surat.md`, ADR-006

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

**Data Model (as implemented):**
```
jenis_surat
  - id (PK, AI)
  - nama_surat (varchar 100, unique)
  - deskripsi (text, nullable)
  - persyaratan_dokumen (text, required at app layer)
  - timestamps
  - deleted_at (soft deletes)
```

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority | Status |
|---|-------|-------------|----------|--------|
| 1 | US-2.1 Kelola Data Jenis Surat | 3 | Must | ✅ Done |
| 2 | US-2.2 Tampilan Persyaratan Dokumen | 2 | Must | Pending |
| 3 | US-2.3 Akses Publik Persyaratan Dokumen | 2 | Must | Pending |

**Total Story Points: 7** (3 done / 4 remaining)

---

## Implementation Notes & Decisions (US-2.1)

| Topik | Keputusan | Alasan |
|-------|-----------|--------|
| Nama tabel | `jenis_surat` (bukan `jenis_surats`) | Ikuti data model plan; set `$table` di model |
| UI form | Satu halaman + Flux modal tambah/ubah | 1 route = 1 Livewire component (konvensi arsitektur) |
| Soft delete | Ya — tombol **Arsipkan** | Mitigasi risk Phase 02; data bisa dipulihkan |
| Hard delete | Ya — hanya dari arsip + modal konfirmasi | Bebaskan unique name; hindari hapus tidak sengaja dari daftar aktif |
| Guard FK `pengajuan_surat` | Belum | Tabel pengajuan belum ada (Phase 03); tambahkan guard saat FK tersedia |
| Validasi field | `nama_surat` + `persyaratan_dokumen` wajib; `deskripsi` opsional | Keputusan produk setelah AC (AC hanya sebut unique nama) |
| Unique vs soft delete | Unique DB tetap berlaku untuk baris terarsip | Nama diarsip tidak bisa dipakai ulang sampai hard delete / restore+rename |
| Akses | Route di dalam grup `role:admin` | Reuse middleware US-1.3 |
| Placeholder persyaratan | Disediakan di textarea | Mitigasi risk format teks bebas |

---

## Risks

| Risk | Mitigation | Status |
|------|-----------|--------|
| Admin menghapus jenis surat yang sudah dipakai di pengajuan lama | Soft delete diimplementasikan; larangan hapus jika masih direferensikan `pengajuan_surat` ditunda ke Phase 03 | Soft delete ✅; FK guard ⏳ |
| Persyaratan dokumen ditulis bebas teks sehingga format tidak konsisten | Placeholder/contoh format pada form admin | ✅ Placeholder ada |
