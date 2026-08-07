# Phase 09 - Persyaratan Terstruktur & Aturan Unggah

**Sprint Goal**: Mengganti persyaratan jenis surat dari teks bebas + deteksi kata kunci menjadi daftar syarat terstruktur, agar admin (petugas desa non-teknis) dapat mendefinisikan dengan jelas: apakah syarat perlu diunggah online, dibawa ke kantor, wajib, atau boleh dikosongkan — tanpa mengandalkan “magic keyword”.

**Estimated Duration**: 3–4 hari

**Depends on**: Phase 02 (Jenis Surat & Persyaratan), Phase 03 (Form Pengajuan & Unggah), Phase 04 (Verifikasi). Phase ini adalah **penyempurnaan master data + dampak ke alur pengajuan/verifikasi** — tidak mengubah alur status Phase 08.

**Note:** Gap produk yang ditutup: admin saat ini hanya menulis `persyaratan_dokumen` (textarea). Sistem Phase 03 mendeteksi slot unggah lewat kata `KTP` / `KK` / `Kartu Keluarga` (ADR-010). Syarat lain (pengantar RT/RW, “jika ada”, dokumen fisik) hanya jadi teks informasi — tidak ada cara eksplisit menandai **unggah vs bawa ke kantor** atau **wajib vs boleh dikosongkan**.

---

## Baseline vs Target

### Sebelum Phase 09

```
Admin menulis textarea bebas
        ↓
persyaratan_dokumen (text)
        ↓
Form warga: scan kata kunci "KTP" / "KK"
        ↓
Slot unggah KTP/KK (selalu wajib jika terdeteksi)
Syarat lain = teks saja (tidak ada upload, tidak ada flag wajib/opsional)
```

### Sesudah Phase 09

```
Admin menambah baris syarat (checklist)
  ├─ Nama syarat
  ├─ Cara memenuhi: Unggah di aplikasi | Bawa ke kantor desa | Tidak perlu file
  └─ Jika unggah: Wajib | Boleh dikosongkan
        ↓
jenis_surat_persyaratan (terstruktur) + ringkasan teks untuk tampilan/cari
        ↓
Form warga:
  - Slot unggah hanya untuk cara = "unggah"
  - Validasi required hanya jika is_wajib = true
  - Syarat "bawa ke kantor" tampil sebagai checklist/info (tanpa file input)
Halaman persyaratan publik/warga: badge jelas per item
```

**Perubahan kunci:**

| Sebelum | Sesudah |
|---------|---------|
| Satu textarea bebas | Daftar baris syarat (tambah/hapus/urut) |
| Upload muncul karena kata “KTP”/“KK” | Upload muncul karena admin pilih **Unggah di aplikasi** |
| Semua upload terdeteksi = wajib | Admin pilih **Wajib** atau **Boleh dikosongkan** |
| Pengantar RT/RW hanya teks | Bisa ditandai **Bawa ke kantor desa** |
| Hanya jenis dokumen KTP & KK di form | Slot unggah dinamis per baris syarat yang “unggah” (nama mengikuti nama syarat) |
| ADR-010 keyword detection | Digantikan aturan terstruktur (ADR baru / supersede ADR-010 untuk aturan unggah) |

---

## Why This Phase

- Admin desa (sering generasi boomer, tidak akrab istilah teknis) tidak punya kontrol eksplisit atas “perlu file atau tidak” — mereka hanya mengetik daftar seperti di papan pengumuman
- Deteksi kata kunci rapuh: salah ketik “Fotokopy KTP” tanpa huruf yang cocok → slot unggah hilang; menulis “KTP” di catatan opsional → slot wajib muncul tanpa sengaja
- Praktik desa nyata: banyak syarat **fisik dibawa ke kantor** (pengantar RT/RW), bukan scan online
- Ada syarat **opsional / jika ada** (bukti pendukung, NPWP, slip gaji) yang di seeder sudah tertulis “jika ada”, tetapi sistem tetap tidak bisa membedakannya dari syarat wajib
- Warga bingung: daftar persyaratan panjang, tapi form hanya minta KTP/KK — atau sebaliknya merasa harus upload sesuatu yang seharusnya dibawa ke kantor
- Petugas verifikasi tidak punya checklist “dokumen fisik yang harus dicek saat warga datang”

---

## Prinsip UX (non-negotiable untuk persona admin desa)

1. **Bahasa sehari-hari**, bukan jargon: “Unggah di aplikasi”, “Bawa ke kantor desa”, “Wajib”, “Boleh dikosongkan” — hindari *mandatory / optional / flag / enum* di UI.
2. **Satu baris = satu syarat** — seperti menulis checklist di kertas.
3. **Pertanyaan berurutan**: dulu “Bagaimana warga memenuhi?”; baru jika pilih unggah → “Wajib atau boleh dikosongkan?”
4. **Default aman**: baris unggah baru → **Wajib**; admin harus sadar memilih “Boleh dikosongkan”.
5. **Pratinjau warga** di form admin sebelum simpan, agar admin melihat badge yang sama dengan yang dilihat warga.
6. **Template cepat** (opsional di US, Should): “KTP + KK + Pengantar RT” satu klik untuk jenis surat umum.

---

## User Stories

---

### US-9.1: Kelola Persyaratan Terstruktur (Admin)

**As an** admin/petugas desa
**I want** to add each document requirement as a checklist row with clear choices for how the warga fulfills it
**So that** I control what must be uploaded online, what is brought to the office, and what may be left empty — without guessing keywords

**Konteks desain:**
Form “Tambah/Ubah Jenis Surat” tidak lagi mengandalkan satu textarea sebagai sumber kebenaran aturan unggah. Textarea boleh tetap ada sebagai **ringkasan otomatis** (generated) untuk kompatibilitas pencarian/tampilan lama, tetapi aturan bisnis diambil dari baris terstruktur.

**Acceptance Criteria:**

**A. Form baris persyaratan**

- [ ] Pada modal/halaman tambah & ubah jenis surat, admin dapat menambah, mengubah, menghapus, dan mengurutkan baris persyaratan
- [ ] Setiap baris punya field **Nama syarat** (wajib diisi), contoh: `Fotokopi KTP`, `Surat pengantar RT/RW`
- [ ] Setiap baris punya pilihan **Cara memenuhi** (wajib dipilih), label UI tepat seperti ini:
  - **Unggah di aplikasi** — helper: “Warga kirim foto/scan lewat HP di form pengajuan.”
  - **Bawa ke kantor desa** — helper: “Warga bawa berkas fisik; tidak ada tombol unggah.”
  - **Tidak perlu file** — helper: “Hanya catatan/informasi; tidak perlu diunggah maupun dibawa.”
- [ ] Jika cara = **Unggah di aplikasi**, muncul pilihan kedua:
  - **Wajib** (default) — “Harus diunggah sebelum pengajuan dikirim.”
  - **Boleh dikosongkan** — “Opsional / jika ada; pengajuan tetap bisa dikirim tanpa file ini.”
- [ ] Jika cara ≠ unggah, pilihan wajib/boleh-dikosongkan **tidak ditampilkan** (tidak relevan)
- [ ] Minimal 1 baris persyaratan saat simpan jenis surat (atau aturan setara: jenis surat harus punya ≥1 syarat)
- [ ] Validasi pesan error dalam Bahasa Indonesia yang jelas (bukan istilah teknis)

**B. Pratinjau & template**

- [ ] Ada **Pratinjau untuk warga** di form admin: daftar syarat + badge (`Wajib diunggah` / `Boleh dikosongkan` / `Bawa ke kantor` / `Informasi`)
- [ ] (Should) Tombol template cepat: isi 3 baris default Domisili-style: KTP (unggah wajib) + KK (unggah wajib) + Pengantar RT/RW (bawa ke kantor)

**C. Akses & konsistensi arsitektur**

- [ ] Tetap di halaman yang sama dengan kelola jenis surat (1 route = 1 Livewire component, sesuai konvensi proyek) — jangan buat service/repository terpisah
- [ ] Hanya role admin yang dapat mengubah persyaratan
- [ ] Soft-delete / arsip jenis surat tetap berlaku seperti Phase 02; baris syarat ikut jenis surat (cascade atau setara)

**Primary surface:** `/admin/jenis-surat` (`jenis-surat.index`) — perluasan `DataJenisSurat`  
**Docs (setelah implementasi):** update `docs/dev-docs/features/jenis-surat.md`, user guide admin jenis surat, ADR baru superseding bagian aturan unggah ADR-010

---

### US-9.2: Migrasi Data Persyaratan Lama → Terstruktur

**As a** system maintainer / admin desa yang sudah punya data jenis surat
**I want** existing free-text requirements converted into structured rows without losing meaning
**So that** production/demo data tetap jalan setelah Phase 09 tanpa admin mengisi ulang semuanya dari nol

**Acceptance Criteria:**

- [ ] Migration membuat tabel (atau struktur setara) untuk baris persyaratan per `jenis_surat`
- [ ] Seeder / migrasi data:
  - Baris yang mengandung indikasi KTP → `cara_pemenuhan = unggah`, `is_wajib = true`, nama dinormalisasi wajar
  - Baris yang mengandung KK / Kartu Keluarga → sama
  - Baris yang mengandung frasa “jika ada” / “(opsional)” → jika dijadikan unggah maka `is_wajib = false`; jika tidak jelas unggah → default **Bawa ke kantor** atau **Tidak perlu file** sesuai aturan migrasi yang didokumentasikan
  - Baris lain (pengantar RT/RW, pernyataan, dll.) → default **Bawa ke kantor desa** (aman untuk praktik desa; admin bisa ubah kemudian)
- [ ] `persyaratan_dokumen` (text) tetap terisi sebagai **ringkasan generated** dari baris terstruktur agar pencarian & halaman lama tidak kosong
- [ ] Jenis surat tanpa baris setelah migrasi ditandai / punya fallback jelas (tidak silent-fail di form warga)
- [ ] Feature test: migrasi/seeder menghasilkan baris terstruktur untuk data seeder Domisili & SKTM

**Catatan keputusan migrasi (wajib dicatat di ADR):**  
Default “bawa ke kantor” untuk baris non-KTP/KK sengaja konservatif — lebih aman menampilkan info fisik daripada memaksa upload yang salah.

---

### US-9.3: Form Pengajuan Warga Mengikuti Aturan Terstruktur

**As a** warga
**I want** the submission form to show upload fields only for requirements marked “unggah”, and only block submit for those marked wajib
**So that** I am not forced to upload things I should bring to the office, and optional docs do not block my application

**Acceptance Criteria:**

**A. Tampilan form**

- [ ] Setelah memilih jenis surat, warga melihat daftar persyaratan dengan badge yang sama seperti di pratinjau admin
- [ ] Area unggah muncul **hanya** untuk syarat dengan cara **Unggah di aplikasi**
- [ ] Label field unggah memakai **nama syarat** (bukan hardcode hanya “KTP” / “KK”, kecuali nama syarat memang itu)
- [ ] Syarat **Bawa ke kantor desa** tampil sebagai daftar info/checklist tanpa input file, dengan teks bantuan singkat (contoh: “Siapkan berkas ini dan bawa saat diminta petugas / saat pengambilan.”)
- [ ] Syarat **Tidak perlu file** tampil sebagai info ringkas (opsional digabung di seksi informasi)

**B. Validasi & penyimpanan**

- [ ] Submit ditolak jika ada syarat unggah **wajib** yang belum ada file
- [ ] Syarat unggah **boleh dikosongkan** tidak memblokir submit
- [ ] Format/ukuran file tetap: jpg/png/pdf, max 2MB (ikuti Phase 03 kecuali ada keputusan ubah)
- [ ] File tersimpan di disk privat; metadata terhubung ke pengajuan **dan** ke baris syarat sumber (atau setara yang memungkinkan admin verifikasi tahu syarat mana)
- [ ] Mengganti jenis surat di form mereset file sementara (perilaku setara Phase 03)
- [ ] **Tidak lagi** mengandalkan `detectRequiredDokumenTypes()` berbasis kata kunci untuk aturan wajib/slot (boleh dihapus atau diganti helper berbasis relasi terstruktur)

**C. Ajukan ulang (regresi Phase 03 US-3.4)**

- [ ] Ajukan ulang tetap berfungsi: prefill jenis + keperluan; unggah mengikuti aturan jenis surat terkini
- [ ] Test regresi kelengkapan & ajukan ulang diperbarui

**Primary surface:** `/pengajuan-surat` (`pengajuan-surat.create`) — `FormPengajuanSurat`  
**Impacts:** `dokumen_persyaratan` schema mungkin perlu kolom referensi ke `jenis_surat_persyaratan_id` dan/atau `nama_dokumen` fleksibel (bukan hanya enum KTP/KK)

---

### US-9.4: Tampilan Persyaratan Publik & Warga dengan Badge Jelas

**As a** calon pemohon (publik) atau warga
**I want** to see each requirement labeled clearly (upload wajib / boleh dikosongkan / bawa ke kantor)
**So that** I know what to scan at home and what to bring to the village office before I apply

**Acceptance Criteria:**

- [ ] Halaman `/persyaratan-dokumen` (list + detail) menampilkan persyaratan sebagai **daftar item**, bukan satu blok teks mentah tanpa makna
- [ ] Setiap item menampilkan badge:
  - **Wajib diunggah**
  - **Boleh dikosongkan**
  - **Bawa ke kantor**
  - **Informasi** (untuk “Tidak perlu file”)
- [ ] Soft-deleted jenis surat tetap disembunyikan
- [ ] Akses publik tanpa login tetap berlaku (US-2.3)
- [ ] Pencarian tetap berfungsi (nama surat / deskripsi / teks syarat)
- [ ] Responsif di smartphone

**Primary surface:** `/persyaratan-dokumen` — `PersyaratanDokumen`

---

### US-9.5: Checklist Fisik di Verifikasi Admin (Should)

**As an** admin verifying a submission
**I want** to see which requirements were uploaded online vs which must be checked as physical documents
**So that** I don’t assume everything is in the system and I know what to ask the warga to bring

**Acceptance Criteria:**

- [ ] Di detail verifikasi pengajuan, seksi dokumen memisahkan:
  - **Diunggah online** — file yang ada (dengan preview/unduh seperti sekarang)
  - **Harus dicek / dibawa ke kantor** — daftar nama syarat cara = bawa ke kantor (checklist visual; tidak wajib dicentang sistem di MVP kecuali diputuskan)
- [ ] Syarat unggah opsional yang tidak diunggah ditandai jelas (“Tidak diunggah — diperbolehkan”)
- [ ] Tidak mengubah alur setujui/tolak Phase 08

**Primary surface:** detail verifikasi admin (Phase 04)

---

## Data Model (target)

```
jenis_surat
  - id
  - nama_surat
  - deskripsi (nullable)
  - persyaratan_dokumen (text, ringkasan generated — tetap ada untuk search/backward display)
  - timestamps
  - deleted_at

jenis_surat_persyaratan   (baru)
  - id (PK, AI)
  - jenis_surat_id (FK -> jenis_surat, cascade on delete)
  - nama (varchar, required)
  - cara_pemenuhan (string: unggah | bawa_kantor | info)
  - is_wajib (boolean, default true; hanya relevan jika cara = unggah)
  - urutan (unsigned int)
  - timestamps

dokumen_persyaratan       (disesuaikan)
  - id
  - pengajuan_id (FK)
  - jenis_surat_persyaratan_id (FK, nullable untuk data lama)  -- baru
  - jenis_dokumen / nama_dokumen (fleksibel; tidak lagi terbatas enum KTP/KK saja)
  - file_path
  - timestamps
  - unique yang masuk akal per pengajuan + syarat (bukan hanya enum lama)
```

> **Konvensi proyek:** simpan const / opsi `cara_pemenuhan` di Livewire component atau model terkait — **jangan** buat file Enum/Service terpisah kecuali benar-benar perlu dipakai di 3+ tempat identik.

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority | Status |
|---|-------|-------------|----------|--------|
| 1 | US-9.1 Kelola Persyaratan Terstruktur (Admin) | 5 | Must | ⬜ Todo |
| 2 | US-9.2 Migrasi Data Persyaratan Lama | 3 | Must | ⬜ Todo |
| 3 | US-9.3 Form Pengajuan Mengikuti Aturan Terstruktur | 5 | Must | ⬜ Todo |
| 4 | US-9.4 Tampilan Persyaratan Publik/Warga + Badge | 3 | Must | ⬜ Todo |
| 5 | US-9.5 Checklist Fisik di Verifikasi Admin | 2 | Should | ⬜ Todo |

**Total Story Points: 18** (Must: 16 / Should: 2)

**Urutan implementasi disarankan:** US-9.2 (schema + migrasi) → US-9.1 (admin UI) → US-9.3 (form warga) → US-9.4 (publik) → US-9.5 (verifikasi).

---

## Implementation Notes & Decisions

| Topik | Keputusan | Alasan |
|-------|-----------|--------|
| Sumber kebenaran aturan unggah | Tabel `jenis_surat_persyaratan`, bukan textarea | Admin eksplisit; hilangkan fragility keyword |
| Field `persyaratan_dokumen` | Tetap ada sebagai ringkasan generated | Search, kompatibilitas tampilan, minim break |
| Keyword detection ADR-010 | Supersede untuk **aturan** unggah/wajib | Deteksi teks boleh dipakai **hanya** di migrasi data sekali jalan |
| Label UI | Bahasa Indonesia plain language | Persona admin/warga desa non-teknis |
| Default `is_wajib` | `true` saat cara = unggah | Lebih aman menolak pengajuan kurang daripada menerima kurang |
| Default migrasi non-KTP/KK | `bawa_kantor` | Sesuai praktik desa; admin bisa ubah ke unggah nanti |
| Jenis dokumen di `dokumen_persyaratan` | Longgarkan dari enum ketat KTP/KK | Syarat unggah bisa bernama apa pun |
| Arsitektur | Logic di Livewire `DataJenisSurat` + `FormPengajuanSurat` | Ikuti flat architecture proyek |
| Scope dokumen unggah generik | Ya, dinamis per baris | Jangan hardcode hanya dua slot selamanya |
| Centang fisik di verifikasi | Tidak wajib di MVP (US-9.5 Should) | Cukup tampilkan daftar; hindari over-engineering |

---

## Risks

| Risk | Mitigation |
|------|-----------|
| Admin bingung tiga pilihan “cara memenuhi” | Helper text singkat di bawah radio; pratinjau badge; template cepat |
| Migrasi salah klasifikasi syarat lama | Default konservatif `bawa_kantor`; dokumentasikan di ADR; admin bisa edit setelah deploy |
| Regresi form warga & e2e KTP/KK | Perbarui Pest + Playwright; skenario wajib, opsional, dan bawa-kantor |
| Unique constraint lama `(pengajuan_id, jenis_dokumen)` bentrok dengan nama fleksibel | Migration sesuaikan unique ke referensi baris syarat |
| Ringkasan teks `persyaratan_dokumen` tidak sync | Generate ulang setiap kali simpan jenis surat |
| Verifikasi admin masih mengasumsikan hanya KTP/KK | US-9.5 + update label preview dokumen |
| Skripsi/BAB masih menyebut “teks bebas” | Update catatan di docs/user-docs & (jika diminta) catatan revisi skripsi |

---

## Out of Scope (Phase 09)

- Mengubah alur status pengajuan (tetap mengikuti Phase 08)
- Multi-file per satu syarat (satu syarat = maksimal satu file di MVP)
- OCR / validasi isi KTP otomatis
- Wajib mencentang checklist fisik sebelum Setujui (bisa phase berikutnya)
- Persyaratan berbeda per dusun/RT (terlalu dini)

---

## Definition of Done (Phase)

- [ ] Semua AC Must (US-9.1 s/d US-9.4) terpenuhi dan dicentang
- [ ] Pest feature tests + e2e terkait jenis surat / pengajuan / persyaratan hijau
- [ ] `vendor/bin/pint --dirty` bersih untuk PHP yang diubah
- [ ] ADR ditulis (supersede aturan unggah ADR-010) + docs user/dev terkait di-update
- [ ] Seeder jenis surat memakai baris terstruktur (bukan hanya mengandalkan keyword di teks)
- [ ] Demo manual: admin buat jenis surat dengan campuran unggah wajib + unggah opsional + bawa kantor; warga submit; admin lihat di verifikasi

---

## Related References

| Artefak | Keterangan |
|---------|------------|
| Phase 02 | Master data jenis surat + textarea persyaratan |
| Phase 03 | Form pengajuan + unggah + kelengkapan |
| ADR-010 | Keyword KTP/KK detection — digantikan untuk aturan unggah |
| `DataJenisSurat` | Surface admin yang diperluas |
| `FormPengajuanSurat` | Konsumen aturan baru |
| `PersyaratanDokumen` | Tampilan publik/warga |
| Diskusi UX (chat) | Persona boomer / petugas desa; label plain language |
