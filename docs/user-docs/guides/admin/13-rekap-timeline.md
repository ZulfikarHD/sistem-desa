# Detail Rekap & Timeline Proses Pengajuan - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 13 dari 17 — Cara membaca riwayat lengkap proses tiap pengajuan.

## Apa itu Detail Rekap Timeline?

Halaman **Detail Rekap Pengajuan** (`/admin/rekap/{id}`) menampilkan dua bagian untuk setiap pengajuan:

1. **Ringkasan Pengajuan** — data lengkap: nama warga, NIK, jenis surat, nomor pengajuan, nomor surat resmi, dan status terakhir
2. **Timeline Proses** — urutan kronologis seperti tracking kurir: setiap tahap yang sudah dilalui ditampilkan dengan waktu, ikon warna berbeda, dan nama petugas yang menangani

Timeline hanya menampilkan tahap yang **sudah terjadi**. Tahap masa depan tidak ditampilkan sama sekali.

## Cara Menggunakan

### Membuka Detail Rekap

1. Masuk sebagai **admin/petugas desa**.
2. Di menu samping, klik **Rekap Pengajuan**.
3. Pada baris pengajuan yang ingin dilihat, klik tombol **Lihat Detail**.
4. Halaman detail rekap terbuka di `/admin/rekap/{id}`.

### Membaca Ringkasan Pengajuan

Di bagian atas halaman, Anda dapat melihat:

| Field | Deskripsi |
|-------|-----------|
| Nama Warga | Nama lengkap pemohon |
| NIK | Nomor Induk Kependudukan pemohon |
| Jenis Surat | Nama jenis surat yang diajukan |
| Nomor Pengajuan | Nomor unik format `PJ-YYYYMMDD-####` |
| Nomor Surat Resmi | Format `470/{urut}/DS-WDN/{romawi}/{tahun}` — hanya muncul jika sudah disetujui |
| Status Terakhir | Badge status: Diajukan / Diproses / Siap Diambil / Selesai / Ditolak |

### Membaca Timeline Proses

Setiap poin timeline menampilkan:
- **Ikon lingkaran berwarna** — warna berbeda per jenis aksi
- **Label aksi** — deskripsi apa yang terjadi dan siapa yang melakukan
- **Waktu** — format `DD MMMM YYYY, HH:mm WIB` (timezone Asia/Jakarta)
- **Nama aktor** — nama admin yang melakukan aksi, atau "Sistem"

**Ikon dan warna per tahap:**

| Tahap | Warna | Ikon | Contoh Label |
|-------|-------|------|--------------|
| Pengajuan Dibuat | Abu-abu | Dokumen | "Pengajuan diterima oleh sistem" |
| Disetujui & Diproses | Biru | Centang | "Disetujui oleh Budi — surat #470/1/DS-WDN/VIII/2026 digenerate" |
| Ditolak | Merah | X | "Ditolak oleh Budi — Alasan: Dokumen KTP buram" |
| Siap Diambil | Hijau | Kalender | "Dokumen siap diambil oleh Budi — Tanggal: 10 Agustus 2026 (Senin–Kamis 08.00–16.00)" |
| Selesai (QR Scan) | Abu-abu | QR | "Dokumen telah diambil — QR dipindai, dicatat oleh Budi" |

> 💡 **Tips:** Jika pengajuan **ditolak**, timeline berhenti di poin Ditolak — tidak ada poin Siap Diambil atau Selesai setelahnya.

### Mengunduh PDF Surat dari Detail

1. Jika surat sudah digenerate (status Diproses / Siap Diambil / Selesai), tombol **Unduh PDF Surat** tersedia di pojok kanan atas.
2. Klik tombol tersebut untuk mengunduh PDF.

### Kembali ke Rekap

1. Klik tombol **Kembali ke Rekap** (ikon panah kiri) di pojok kanan atas.
2. Anda kembali ke halaman Rekap Pengajuan dengan filter yang sebelumnya diatur.

## Catatan Teknis untuk Data Lama

Jika pengajuan dibuat sebelum pembaruan sistem Phase 08, kolom `siap_diambil_at` mungkin kosong. Dalam kondisi ini, waktu pada poin "Siap Diambil" ditampilkan dengan catatan **(waktu estimasi — data lama tanpa siap_diambil_at)**. Ini normal dan bukan error.

## FAQ

**Q: Mengapa hanya beberapa poin yang muncul di timeline?**
A: Timeline hanya menampilkan tahap yang sudah terjadi. Pengajuan yang baru diajukan hanya menampilkan satu poin (Pengajuan Dibuat).

**Q: Mengapa poin "Siap Diambil" tidak ada padahal status sudah Siap Diambil?**
A: Pada data lama, mungkin ada lag — poin tetap ditampilkan menggunakan waktu estimasi jika `siap_diambil_at` kosong.

**Q: Apakah warga bisa melihat halaman ini?**
A: Tidak. Halaman ini khusus admin. Warga melihat status pengajuan mereka melalui halaman Riwayat Pengajuan.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Halaman 404 saat buka `/admin/rekap/{id}` | ID pengajuan tidak ditemukan atau Anda tidak memiliki akses admin |
| Timeline kosong | Tidak ada log proses; periksa data di tabel `log_verifikasi` |
| Tombol Unduh PDF tidak muncul | Status pengajuan masih Diajukan/Ditolak, atau surat belum pernah diterbitkan (belum ada data surat terbit) |
| Waktu bertanda "estimasi" | Data lama tanpa `siap_diambil_at`; nilai estimasi dari `surat_terbit.updated_at` |
