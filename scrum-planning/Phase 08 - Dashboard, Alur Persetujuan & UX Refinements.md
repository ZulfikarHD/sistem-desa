# Phase 08 - Dashboard, Alur Persetujuan & UX Refinements

**Sprint Goal**: Membangun dashboard informatif untuk Admin dan Warga, menyederhanakan alur persetujuan (setujui → langsung `diproses`), memisahkan menu "Surat Diproses" dari "Daftar Pengajuan Surat", memperkuat UX pengisian tanggal pengambilan, dan menampilkan timeline proses lengkap di rekap pengajuan.

**Estimated Duration**: 4–5 hari

**Depends on**: Phase 01–07 (semua sudah selesai). Phase ini adalah **penambahan fitur & penyempurnaan UX** di atas seluruh phase yang sudah berjalan — tidak me-rewrite plan phase sebelumnya.

---

## Baseline vs Target

### Alur Status Sebelum Phase 08 (hasil Phase 07)

```
diajukan
  ├─→ disetujui     (admin klik Setujui)
  │       └─→ diproses  (auto setelah disetujui + generate PDF)
  │               └─→ siap_diambil  (admin set tanggal)
  │                       └─→ selesai  (QR scan)
  └─→ ditolak
```

### Alur Status Target Setelah Phase 08

```
diajukan
  ├─→ diproses      (admin klik "Setujui" → langsung diproses + generate PDF + notif warga)
  │       └─→ siap_diambil  (admin isi tanggal di halaman "Surat Diproses" + klik "Siap Diambil" + notif warga)
  │               └─→ selesai  (QR scan, Phase 07 US-7.4)
  └─→ ditolak       (admin klik "Tolak" + alasan → notif warga)
```

**Perubahan kunci:**

| Sebelum | Sesudah |
|---------|---------|
| Setujui → `disetujui` (tampil ke warga) → auto `diproses` | Setujui → `diproses` langsung (satu aksi) |
| Notifikasi dikirim saat `disetujui` | Notifikasi dikirim saat `diproses` (bukan `disetujui`) |
| "Verifikasi Pengajuan" (sidebar/header admin) | "Daftar Pengajuan Surat" |
| Halaman tanggal + "Siap Diambil" ada di dalam flow verifikasi lama | Dipindah ke halaman baru "Surat Diproses" |
| Rekap pengajuan hanya tabel tanpa detail riwayat | Klik baris/tombol → halaman detail timeline proses |

> **Catatan kompatibilitas:** State `disetujui` tetap dipertahankan di enum database untuk integritas data historis, tapi tidak lagi muncul sebagai status tampilan warga pada pengajuan baru. Kode Phase 07 US-7.1 dimodifikasi agar aksi "Setujui" langsung meng-set status ke `diproses` alih-alih ke `disetujui` dahulu.

---

## Why This Phase

- Admin tidak punya visibilitas umur/aging dokumen di tiap tahap — pengajuan bisa terkubur berminggu-minggu di `diajukan`, `diproses`, maupun `siap_diambil` tanpa ada sinyal peringatan
- Semua tahap aktif sama pentingnya: dokumen yang 2 minggu di `diproses` dan belum di-set tanggal pengambilan sama bermasalahnya dengan tumpukan verifikasi yang belum ditangani
- Satu-satunya pertanyaan warga ketika membuka aplikasi adalah *"Sudah sampai mana surat saya?"* — halaman yang ada tidak menjawab itu secara langsung
- Admin kewalahan melihat semua pengajuan bercampur dalam satu daftar — membutuhkan pemisahan antara "perlu diverifikasi" (diajukan) dan "sedang diproses" (diproses)
- Terminologi "Verifikasi Pengajuan" membingungkan karena nama tersebut tidak mencerminkan konteks penggunaannya sehari-hari
- Alur `disetujui` → `diproses` (dua langkah) tidak ada nilai tambahnya di UI; admin dan warga tidak mendapat informasi tambahan dari status perantara `disetujui`
- Date picker tanggal pengambilan tidak memblokir tanggal lampau, yang bisa menyebabkan data tidak valid
- Tidak ada cara bagi admin untuk melihat riwayat lengkap proses satu pengajuan dari awal hingga selesai

---

## User Stories

---

### US-8.1: Dashboard Admin

**As an** admin/petugas desa
**I want** a dashboard that shows the urgency and age of every active submission stage — not just counts
**So that** nothing gets buried for days or weeks without me noticing, regardless of which stage it's stuck at

**Konteks desain:**
Setiap tahap aktif (`diajukan`, `diproses`, `siap_diambil`) sama pentingnya. Pengajuan bisa mengendap di mana saja — bukan hanya di `diajukan`. Sebuah dokumen yang sudah 2 minggu berstatus `diproses` dan belum di-set tanggal pengambilan sama bahayanya dengan tumpukan verifikasi yang belum ditangani. Dashboard harus membuat kondisi ini tidak bisa diabaikan.

**Acceptance Criteria:**

**A. Kartu Status + Aging (3 kartu aktif + 1 arsip)**

- [ ] **Kartu "Menunggu Verifikasi"** (`diajukan`):
  - Jumlah total pengajuan berstatus `diajukan`
  - Sub-label: "X tertunda > 3 hari" (jika ada) — dihitung dari `pengajuan_surat.created_at`
  - Klik kartu → navigasi ke Daftar Pengajuan Surat
- [ ] **Kartu "Sedang Diproses"** (`diproses`):
  - Jumlah total pengajuan berstatus `diproses`
  - Sub-label: "X tertunda > 5 hari" (jika ada) — dihitung dari `surat_terbit.tanggal_terbit` (kapan PDF digenerate)
  - Klik kartu → navigasi ke halaman Surat Diproses
- [ ] **Kartu "Siap Diambil"** (`siap_diambil`):
  - Jumlah total pengajuan berstatus `siap_diambil`
  - Sub-label: "X jadwal terlewat" (jika `tanggal_pengambilan` < hari ini) — ini paling kritis: warga harusnya sudah ambil tapi belum
  - Klik kartu → navigasi ke rekap dengan filter `siap_diambil`
- [ ] **Kartu "Selesai Bulan Ini"** (arsip):
  - Jumlah pengajuan berstatus `selesai` dalam bulan kalender berjalan
  - Tidak ada sub-label aging (ini informasi positif, bukan peringatan)
  - Klik kartu → navigasi ke rekap dengan filter `selesai` + bulan berjalan

**B. Indikator Warna Kartu (prioritas visual)**

- [ ] Kartu berubah warna dinamis berdasarkan kondisi:
  - **Normal** (abu-abu/biru netral): semua item dalam batas wajar, tidak ada yang stale
  - **Warning** (amber/kuning): ada 1+ item yang melewati threshold hari (> 3 hari untuk `diajukan`, > 5 hari untuk `diproses`)
  - **Urgent** (merah): ada 1+ item melewati threshold kritis (> 7 hari untuk `diajukan`, > 10 hari untuk `diproses`, atau jadwal terlewat untuk `siap_diambil`)
- [ ] Perubahan warna ini **berlaku untuk keseluruhan kartu** (border + background tint), bukan hanya sub-label
- [ ] Aturan: jika ada yang urgent → kartu merah; jika hanya ada yang warning → kartu amber; jika semua normal → kartu netral

**C. Seksi "Perlu Ditindaklanjuti Segera"**

- [ ] Di bawah kartu, tampilkan tabel **maksimal 5 item paling mendesak** dari semua status aktif digabung
- [ ] Kriteria "mendesak" (urut prioritas):
  1. `siap_diambil` dengan `tanggal_pengambilan` < hari ini (jadwal terlewat)
  2. `diajukan` yang sudah > 7 hari
  3. `diproses` yang sudah > 10 hari
  4. `diajukan` yang sudah > 3 hari
  5. `diproses` yang sudah > 5 hari
- [ ] Kolom tabel: nomor_pengajuan, nama warga, jenis surat, status (badge), "Sudah berapa lama" (misal: "9 hari"), tombol "Tangani"
- [ ] "Sudah berapa lama" dihitung dari kapan masuk ke status saat ini (bukan dari tanggal pengajuan awal)
- [ ] Jika tidak ada item mendesak sama sekali, seksi ini tidak ditampilkan (bukan kosong — benar-benar hilang)

**D. Tabel "Semua Pengajuan Aktif Terbaru"**

- [ ] Tabel menampilkan 7 pengajuan aktif terbaru (status bukan `selesai` dan bukan `ditolak`), diurutkan: paling lama di status saat ini (paling atas) — bukan paling baru dibuat
- [ ] Kolom: nomor_pengajuan, nama warga, jenis surat, status (badge), "Di status ini selama X hari", tombol "Lihat Detail"
- [ ] Baris yang kondisinya warning/urgent mendapat highlight baris (background amber/merah muda ringan)

**E. Umum**

- [ ] Seluruh angka statistik dan tabel tidak menggunakan cache statis — di-refresh setiap halaman dimuat
- [ ] Halaman hanya dapat diakses oleh role `admin`
- [ ] Jika semua kartu aktif bernilai 0 (tidak ada yang sedang berjalan), tampilkan state kosong positif: "Tidak ada pengajuan yang perlu ditangani saat ini."

**Threshold Aging (default, dicatat sebagai konstanta di kode bukan hardcoded tersebar):**

| Status | Warning | Urgent |
|--------|---------|--------|
| `diajukan` | > 3 hari kalender | > 7 hari kalender |
| `diproses` | > 5 hari kalender | > 10 hari kalender |
| `siap_diambil` | `tanggal_pengambilan` = hari ini atau besok | `tanggal_pengambilan` < hari ini |

**UI Notes:**

- Warna badge status (konsisten di seluruh sistem):
  - `diajukan` → amber
  - `diproses` → biru
  - `siap_diambil` → hijau
  - `selesai` → abu-abu
  - `ditolak` → merah
- Layout halaman: kartu statistik (grid 2×2 atau 4 kolom) → seksi "Perlu Ditindaklanjuti" (kondisional) → tabel aktif terbaru
- Tombol quick action ("Verifikasi Pengajuan Baru" dan "Proses Surat") diletakkan di header halaman atau di dalam kartu yang relevan, bukan sebagai seksi terpisah

---

### US-8.2: Dashboard Warga

**As a** warga
**I want** the very first thing I see after login to be the current status of my submission — not a menu, not a welcome banner
**So that** I get my answer immediately without having to navigate anywhere

**Konteks desain:**
Motivasi utama warga membuka aplikasi ini adalah satu pertanyaan: *"Sudah sampai mana surat saya?"* Seluruh layout halaman harus dirancang dengan asumsi itu. Status pengajuan adalah hero content, bukan sidebar info.

**Acceptance Criteria:**

**A. Hero Section — Status Pengajuan Aktif (Paling Dominan di Halaman)**

- [ ] Bagian paling atas halaman menampilkan **kartu status pengajuan aktif** (pengajuan dengan status bukan `selesai` dan bukan `ditolak`) secara full-width atau setidaknya mengambil 60–70% visual area pertama layar
- [ ] Setiap kartu pengajuan aktif menampilkan:
  - Jenis surat + nomor_pengajuan
  - **Status badge besar** (lebih besar dari badge di tabel riwayat)
  - **Kalimat penjelasan status** yang mudah dipahami warga awam (lihat daftar di bawah)
  - **Elapsed time**: "Sudah X hari di status ini" — dihitung dari kapan masuk status saat ini
  - Jika status = `diproses` atau `siap_diambil`: tombol **"Unduh Surat"** langsung di kartu
  - Jika status = `siap_diambil`: tanggal + jam kerja pengambilan ditampilkan secara **menonjol** (bukan hanya teks kecil) — ini informasi paling penting saat itu
  - Jika status = `diajukan`: tidak ada aksi tambahan, cukup penjelasan status
- [ ] Kalimat penjelasan per status:
  - `diajukan` → "Pengajuan Anda sedang menunggu ditinjau oleh petugas desa."
  - `diproses` → "Surat Anda sedang disiapkan oleh petugas. Anda dapat mengunduh surat sementara di bawah."
  - `siap_diambil` → "Surat Anda **sudah siap diambil**! Datanglah ke kantor desa pada jadwal berikut:"
- [ ] Jika ada **lebih dari 1 pengajuan aktif**, tampilkan semuanya secara berurutan (terlama di status aktif dahulu — karena itu yang paling perlu diperhatikan)
- [ ] Jika **tidak ada pengajuan aktif** (belum pernah buat, atau semua sudah selesai/ditolak): tampilkan state kosong yang ramah dengan CTA utama "Ajukan Surat Sekarang" — ini menggantikan seluruh hero section

**B. Quick Action**

- [ ] Tombol **"Ajukan Surat Baru"** tersedia tapi **tidak mendominasi** halaman — diletakkan di bawah hero section atau di area header, bukan sebagai elemen paling besar di halaman
- [ ] Tombol ini tetap mudah ditemukan, tapi hierarki visual-nya di bawah status pengajuan aktif

**C. Riwayat & Notifikasi (Sekunder)**

- [ ] Di bawah hero section, tampilkan **3 pengajuan terbaru** (semua status termasuk selesai dan ditolak) dengan kolom: jenis surat, nomor_pengajuan, status, tanggal. Link "Lihat Semua Riwayat"
- [ ] Di bawah riwayat, tampilkan **3 notifikasi terbaru** (belum dibaca diutamakan). Link "Lihat Semua Notifikasi"
- [ ] Notifikasi belum dibaca diberi dot indikator merah

**D. Umum**

- [ ] Jika warga memiliki notifikasi yang belum dibaca, tampilkan **banner kecil** di bagian atas: "Anda memiliki X notifikasi baru" dengan link ke panel notifikasi — agar mereka langsung tahu ada update tanpa harus mencari icon bell
- [ ] Halaman hanya dapat diakses oleh role `warga`

**UI Notes:**

- Hero section menggunakan warna background berbeda per status untuk kesan visual yang langsung (tidak hanya badge):
  - `diajukan` → background amber sangat muda, border kiri amber
  - `diproses` → background biru sangat muda, border kiri biru
  - `siap_diambil` → background hijau sangat muda, border kiri hijau — ini yang paling "celebratory"
- Tanggal pengambilan pada status `siap_diambil` ditampilkan dalam font lebih besar, dicetak tebal
- Elapsed time ("Sudah X hari") ditampilkan dalam teks kecil muted di bawah status badge — jika > 7 hari dan masih `diajukan`, gunakan warna amber untuk elapsed time (sinyal bahwa warga bisa menghubungi kantor jika lama)

---

### US-8.3: Rename Terminologi "Verifikasi Pengajuan" → "Daftar Pengajuan Surat"

**As an** admin/petugas desa
**I want** the sidebar menu and page heading for the submission review page to say "Daftar Pengajuan Surat"
**So that** the label accurately describes the content (a list of submissions) rather than implying it's a form/action page

**Acceptance Criteria:**

- [ ] Label item menu sidebar berubah dari "Verifikasi Pengajuan" → **"Daftar Pengajuan Surat"**
- [ ] Heading `<h1>` / judul halaman `/admin/verifikasi` (atau route equivalennya) berubah menjadi **"Daftar Pengajuan Surat"**
- [ ] Breadcrumb (jika ada) juga diperbarui
- [ ] Tidak ada perubahan fungsional — hanya perubahan teks label/heading
- [ ] Tidak ada perubahan URL/route yang sudah ada; jika route lama perlu dipertahankan untuk backward compatibility, tidak masalah (ini bukan perubahan URL)

---

### US-8.4: Ubah Alur Setujui → Status Langsung "Diproses" + Notifikasi Warga

**As an** admin/petugas desa
**I want** clicking "Setujui" on a submission to immediately move it to status "Diproses" and notify the warga
**So that** the intermediate "Disetujui" state is removed from the visible flow — one click = one meaningful outcome

**Acceptance Criteria:**

- [ ] Aksi "Setujui" pada halaman Detail Pengajuan (`/admin/verifikasi/{id}`) mengubah status pengajuan menjadi **`diproses`** secara langsung (bukan `disetujui` dahulu)
- [ ] Aksi ini memicu:
  1. Perubahan `status = diproses` pada record `pengajuan_surat`
  2. Pencatatan di `log_verifikasi` dengan `aksi = 'setujui'` (tidak berubah dari sebelumnya)
  3. Generate PDF surat keterangan + nomor surat otomatis (Phase 07 US-7.2 & US-7.3)
  4. **Notifikasi in-app ke warga** dengan pesan: "Pengajuan [jenis_surat] Anda (#[nomor_pengajuan]) sedang diproses. Surat Anda sedang disiapkan."
- [ ] Status `disetujui` tidak lagi muncul sebagai status tampilan warga pada alur pengajuan baru
- [ ] Aksi "Tolak" tidak berubah dari Phase 04/07: status → `ditolak`, wajib isi catatan_admin, notifikasi ke warga
- [ ] Setelah aksi "Setujui", pengajuan hilang dari daftar "Daftar Pengajuan Surat" (yang hanya menampilkan `diajukan`) dan muncul di "Surat Diproses" (status `diproses`)
- [ ] State `disetujui` tetap ada di enum/column di database untuk backward compatibility data historis, namun tidak digunakan pada alur baru
- [ ] Tes yang sebelumnya mengunci perilaku `diajukan → disetujui` (Phase 07 US-7.1) harus diperbarui sesuai alur baru: `diajukan → diproses`

**Catatan teknis:**

- Ini mengubah perilaku yang didefinisikan di Phase 07 US-7.1 ("sistem otomatis lanjut `diproses` setelah `disetujui`"). Di Phase 08, kedua langkah tersebut digabung menjadi satu transaksi atomic dalam method `setujui()` di Livewire component.
- Notifikasi untuk `disetujui` (Phase 05 US-5.1 menyebut "disetujui/ditolak") digeser menjadi notifikasi untuk `diproses`. Update pesan notifikasi sesuai status baru.

---

### US-8.5: Menu & Halaman Daftar "Surat Diproses" (Admin)

**As an** admin/petugas desa
**I want** a dedicated menu page that lists only submissions currently in "diproses" status
**So that** I can focus on completing the in-progress letters separately from reviewing new ones — without being overwhelmed by mixing the two tasks in one view

**Acceptance Criteria:**

- [ ] Item menu baru **"Surat Diproses"** muncul di sidebar admin, di bawah "Daftar Pengajuan Surat"
- [ ] Halaman menampilkan tabel dengan kolom: nomor_pengajuan, nama warga, jenis surat, tanggal pengajuan, nomor surat (dari `surat_terbit`), tanggal surat digenerate, tombol "Lihat Detail"
- [ ] Tabel hanya menampilkan pengajuan dengan status = `diproses`
- [ ] Pagination tersedia
- [ ] Jika tidak ada surat yang sedang diproses, tampilkan state kosong yang ramah: "Tidak ada surat yang sedang diproses saat ini."
- [ ] Route: `/admin/surat-diproses` (atau sesuai konvensi route yang sudah ada di proyek)
- [ ] Halaman hanya dapat diakses oleh role `admin`

---

### US-8.6: Detail "Surat Diproses" — Tanggal Pengambilan & Siap Diambil (Blokir Tanggal Lampau)

**As an** admin/petugas desa
**I want** to set a valid (future/today) pickup date on the Surat Diproses detail page and mark it as ready for pickup
**So that** warga get a realistic pickup date and the system prevents data errors from past-date entries

**Acceptance Criteria:**

- [ ] Halaman detail surat diproses (`/admin/surat-diproses/{id}`) menampilkan:
  - Data pengajuan lengkap (nama warga, NIK, jenis surat, nomor surat, keperluan)
  - Pratinjau/unduh PDF surat yang sudah digenerate
  - Form **tanggal pengambilan** dengan date picker
  - Tombol **"Siap Diambil"** (aktif hanya setelah tanggal valid diisi)
- [ ] Date picker **memblokir tanggal yang sudah lampau** — tanggal minimal yang dapat dipilih adalah hari ini (H+0) atau hari ke depan
  - Implementasi: validasi di sisi server (Laravel validation `after_or_equal:today`) DAN di sisi client (atribut `min` pada input date = tanggal hari ini dalam format `YYYY-MM-DD`)
- [ ] Validasi jam kerja dipertahankan dari Phase 07 US-7.5:
  - Senin–Kamis: 08.00–16.00 WIB
  - Jumat: 08.00–16.30 WIB
  - Sabtu, Minggu, dan hari libur nasional: tidak dapat dipilih (atau tampil peringatan)
- [ ] Setelah tanggal diisi valid, tombol "Siap Diambil" menjadi aktif (tidak disabled)
- [ ] Klik "Siap Diambil" memicu:
  1. Status `diproses` → `siap_diambil`
  2. Simpan `tanggal_pengambilan` dan `jam_kerja_label` pada `surat_terbit`
  3. Simpan `siap_diambil_at` timestamp pada `surat_terbit` (kolom baru — lihat Data Model)
  4. **Notifikasi in-app ke warga** dengan pesan: "Surat [jenis_surat] Anda (#[nomor_pengajuan]) sudah siap diambil pada [tanggal_pengambilan] ([jam_kerja_label])."
- [ ] Setelah aksi "Siap Diambil", pengajuan hilang dari daftar "Surat Diproses" dan muncul di rekap pengajuan dengan status `siap_diambil`
- [ ] Jika pengajuan sudah berstatus `siap_diambil` atau `selesai`, form tanggal dan tombol "Siap Diambil" tidak tampil (diganti informasi status terkini)

**Data Model (penambahan):**

```
surat_terbit (penambahan kolom baru)
  + siap_diambil_at (timestamp, nullable)   ← baru: dicatat saat admin klik "Siap Diambil"
```

> Kolom ini diperlukan untuk membangun timeline proses di US-8.7. Tanpa `siap_diambil_at`, tidak ada sumber waktu yang akurat untuk titik transisi `diproses → siap_diambil`.

---

### US-8.7: Timeline Proses di Detail Rekap Pengajuan (Admin)

**As an** admin/petugas desa
**I want** to click on any row in the Rekap Pengajuan table and see a full chronological process timeline for that submission
**So that** I have a complete audit trail — like a courier tracking page — showing every step from submission to completion

**Acceptance Criteria:**

- [ ] Pada halaman rekap pengajuan (`/admin/rekap`), setiap baris memiliki tombol **"Lihat Detail"** (atau klik baris) yang membuka halaman detail rekap
- [ ] Route halaman detail rekap: `/admin/rekap/{id}`
- [ ] Halaman detail rekap menampilkan **dua bagian utama**:
  1. **Ringkasan Pengajuan** — data lengkap pengajuan (nama, NIK, jenis surat, nomor pengajuan, nomor surat resmi jika ada, status terakhir)
  2. **Timeline Proses** — urutan kronologis semua tahap yang sudah dilalui

- [ ] Timeline menampilkan poin-poin berikut (yang sudah terjadi saja; poin masa depan tidak ditampilkan):

  | No | Poin Timeline | Data Sumber | Label |
  |----|--------------|-------------|-------|
  | 1 | Pengajuan Dibuat | `pengajuan_surat.created_at` | "Pengajuan diterima oleh sistem" |
  | 2 | Pengajuan Disetujui & Surat Diproses | `log_verifikasi.created_at` (aksi=setujui) + `surat_terbit.tanggal_terbit` | "Disetujui oleh [nama admin] — surat #[nomor_surat] digenerate" |
  | 2b | Pengajuan Ditolak *(jika berlaku)* | `log_verifikasi.created_at` (aksi=tolak) | "Ditolak oleh [nama admin] — Alasan: [keterangan]" |
  | 3 | Siap Diambil | `surat_terbit.siap_diambil_at` | "Dokumen siap diambil oleh [nama admin yang set] — Tanggal: [tanggal_pengambilan] ([jam_kerja_label])" |
  | 4 | Selesai (QR Scan) | `surat_terbit.qr_digunakan_at` + `qr_digunakan_oleh` | "Dokumen telah diambil — QR dipindai, dicatat oleh [nama admin]" |

- [ ] Setiap poin timeline menampilkan: **ikon status**, **label aksi**, **waktu (tanggal + jam WIB)**, **nama aktor** (admin atau sistem)
- [ ] Poin yang belum terjadi tidak ditampilkan (bukan di-grey-out — benar-benar tidak ada di timeline)
- [ ] Jika pengajuan berstatus `ditolak`, timeline berhenti di poin "Ditolak" dan tidak menampilkan poin Siap Diambil/Selesai
- [ ] Tombol "Unduh PDF Surat" tersedia pada halaman detail jika `surat_terbit` ada (status `diproses`/`siap_diambil`/`selesai`)
- [ ] Tombol "Kembali ke Rekap" tersedia untuk navigasi balik

**UI Notes:**

- Desain timeline vertikal (seperti tracking kurir): garis vertikal penghubung antar titik, ikon lingkaran di setiap titik, ikon warna berbeda per jenis aksi (dibuat, disetujui, ditolak, siap diambil, selesai)
- Waktu ditampilkan dalam timezone Asia/Jakarta (WIB): format "DD MMMM YYYY, HH:mm WIB"
- Tampilan responsif (mobile-friendly)

---

## Data Model Summary

### Tabel yang Dimodifikasi

```
surat_terbit (tambah kolom)
  + siap_diambil_at  (timestamp, nullable)
    — dicatat saat admin klik "Siap Diambil" (US-8.6)
    — digunakan untuk timeline di US-8.7
```

### Migration yang Diperlukan

```sql
-- Tambah kolom siap_diambil_at ke tabel surat_terbit
ALTER TABLE surat_terbit ADD COLUMN siap_diambil_at TIMESTAMP NULL AFTER tanggal_pengambilan;
```

### Tidak Ada Tabel Baru

Semua data timeline dapat direkonstruksi dari tabel yang sudah ada:
- `pengajuan_surat` (created_at, status, jenis surat, nomor_pengajuan)
- `log_verifikasi` (created_at, admin_id, aksi, keterangan)
- `surat_terbit` (tanggal_terbit, tanggal_pengambilan, siap_diambil_at, qr_digunakan_at, qr_digunakan_oleh, diterbitkan_oleh)
- `users` (nama admin — di-join dari foreign key)

---

## Sprint Backlog Priority

| # | Story | Story Points | Priority |
|---|-------|-------------|----------|
| 1 | US-8.3 Rename Terminologi | 1 | Must (quick win) |
| 2 | US-8.4 Alur Setujui → Diproses + Notifikasi | 3 | Must |
| 3 | US-8.5 Menu & Halaman Surat Diproses | 3 | Must |
| 4 | US-8.6 Detail Surat Diproses + Tanggal Pengambilan (blokir lampau) | 5 | Must |
| 5 | US-8.1 Dashboard Admin | 5 | Must |
| 6 | US-8.2 Dashboard Warga | 4 | Must |
| 7 | US-8.7 Timeline Rekap Pengajuan | 5 | Should |

**Total Story Points: 26**

**Urutan implementasi yang disarankan:**
1. US-8.3 dulu (rename, tidak ada risiko) → dapat dilakukan dalam hitungan menit
2. US-8.4 (behavioral change setujui → diproses) → menjadi fondasi bagi US-8.5 dan US-8.6
3. US-8.5 + US-8.6 bersamaan (menu baru + detailnya)
4. US-8.1 + US-8.2 bersamaan (dashboard, bergantung pada data yang sudah ada)
5. US-8.7 terakhir (paling kompleks secara UI)

---

## Risks

| Risk | Mitigation |
|------|-----------|
| US-8.4 mengubah perilaku Phase 07 yang sudah berjalan, bisa merusak tes & alur notifikasi Phase 05 | Update tes Phase 07 US-7.1 agar sesuai alur baru; pastikan Phase 05 US-5.1 tetap memicu notifikasi (untuk status `diproses`, bukan `disetujui`) |
| Data historis dengan status `disetujui` tidak kompatibel dengan UI baru | Enum `disetujui` dipertahankan di database; UI hanya perlu menangani tampilan status ini dengan label yang masuk akal (misal: tampilkan "Diproses" jika status = `disetujui` untuk backward compat) |
| Date picker client-side dapat di-bypass jika validasi server-side tidak ada | Wajib validasi Laravel `after_or_equal:today` di Livewire method, tidak hanya atribut `min` HTML |
| Aging calculation di dashboard lambat jika banyak record (query DATEDIFF pada setiap row) | Hitung aging sekali per query; indeks kolom `status` dan `created_at`; gunakan satu query aggregate untuk semua kartu |
| `siap_diambil_at` kosong untuk data lama (sebelum Phase 08) | Dalam logika timeline, jika `siap_diambil_at` null tapi status sudah `siap_diambil`/`selesai`, gunakan `surat_terbit.updated_at` sebagai fallback (dengan catatan bahwa nilainya estimasi) |
| Kartu dashboard berwarna merah terus jika ada satu item lama yang dibiarkan | Threshold aging dijadikan konstanta yang dapat dikonfigurasi, bukan magic number tersebar di kode |
| Timeline US-8.7 lambat jika banyak join query tanpa eager loading | Eager load semua relasi yang diperlukan (`logVerifikasi.admin`, `suratTerbit.qrDigunakanOleh`, `suratTerbit.diterbitkanOleh`) dalam satu query saat halaman dimuat |
| Dashboard admin tidak update real-time jika admin membiarkan browser terbuka lama | Cukup full-page reload saat navigasi kembali ke dashboard — tidak perlu WebSocket/polling untuk skala penelitian ini |

---

## Cross-Reference

- **Phase 04 (selesai)** — US-8.3 mengubah label UI yang berasal dari Phase 04; tidak ada perubahan data model
- **Phase 05 (selesai)** — US-8.4 mengubah trigger notifikasi dari `disetujui` → `diproses`; update pesan notifikasi di Phase 05 US-5.1
- **Phase 07 (selesai)** — US-8.4 meng-override alur US-7.1 (tidak ada intermediate `disetujui`); US-8.6 merelokasi fungsionalitas US-7.5 ke halaman baru "Surat Diproses"; US-8.7 menampilkan data dari `surat_terbit` (Phase 07 data model)
- **Phase 06 (selesai)** — US-8.7 menambah halaman detail `/admin/rekap/{id}` sebagai ekstensi dari rekap Phase 06; tidak mengubah tabel rekap utama
