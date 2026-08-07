# AD-08: Unduh / Cetak Surat oleh Warga

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | AD-08 |
| **Nama Proses** | Unduh dan Cetak Surat oleh Warga |
| **Aktor** | Warga |
| **Use Case Terkait** | UC-13 |
| **Panduan Pengguna** | [Unduh/Cetak Surat](../../guides/warga/10-unduh-surat-warga.md) |

## Deskripsi

Proses ini menggambarkan alur aktivitas saat warga mengunduh atau mencetak file PDF surat keterangan yang sudah diterbitkan oleh admin. Tombol unduh hanya tersedia jika status pengajuan sudah **Diproses**, **Siap Diambil**, atau **Selesai**. Unduh ulang tidak membuat QR baru — sistem menyajikan file tersimpan, atau membuat ulang file dari data yang sama jika file di server hilang.

**Prasyarat:** Pengajuan berstatus Diproses / Siap Diambil / Selesai dan baris `surat_terbit` sudah ada.

**Hasil:** File PDF surat terunduh ke perangkat warga atau terbuka di browser untuk dicetak.

## Diagram Aktivitas

```mermaid
flowchart TD
    Start([Mulai]) --> A[Login sebagai warga]
    A --> B{Akses dari mana?}
    B -->|"Dashboard Warga"| C[Lihat kartu status pengajuan aktif]
    B -->|"Riwayat Pengajuan"| D[Buka halaman Riwayat Pengajuan]
    D --> E[Temukan baris pengajuan yang diinginkan]

    C --> F{Status pengajuan}
    E --> F

    F -->|"Diajukan atau Ditolak"| G[Tombol Unduh tidak tersedia\nbukan status yang memiliki PDF]
    G --> End([Selesai])

    F -->|"Diproses / Siap Diambil / Selesai"| H[Tombol Unduh Surat tersedia]
    H --> I{Pilih aksi}

    I -->|"Unduh langsung dari daftar"| J[Klik Unduh Surat]
    I -->|"Buka detail dulu"| K[Klik tombol Detail]
    K --> L[Sistem tampilkan halaman detail\ntermasuk jadwal pengambilan jika ada]
    L --> M{Pilih dari detail}
    M -->|"Unduh"| J
    M -->|"Cetak"| N[Klik Cetak Surat]

    J --> O{File PDF ada di server?}
    O -->|Ya| P[Sajikan file PDF tersimpan]
    O -->|Tidak| Q[Generate ulang PDF\ndengan nomor & QR yang sama]
    Q --> R[Simpan ulang ke server]
    R --> P
    P --> S[Browser mengunduh file PDF]
    S --> End

    N --> T{File PDF ada di server?}
    T -->|Ya| U[Buka PDF di tab baru]
    T -->|Tidak| V[Generate ulang PDF\ndengan nomor & QR yang sama]
    V --> W[Simpan ulang ke server]
    W --> U
    U --> X[Warga pilih menu Cetak / Print di browser]
    X --> End
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| 1 | Login | Warga harus terautentikasi |
| 2 | Temukan pengajuan | Dari dashboard atau riwayat pengajuan |
| 3 | Cek status | Tombol unduh hanya muncul untuk status tertentu |
| 4 | Pilih aksi | Unduh langsung atau buka detail dulu |
| 5 | Unduh / Cetak | File tersimpan disajikan; jika hilang, digenerate ulang tanpa mengubah QR |
| 6 (opsional) | Cetak | PDF terbuka di tab baru, siap dicetak dari browser |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Status Diajukan/Ditolak | PDF belum digenerate | Tombol unduh tidak ditampilkan |
| File hilang di server | Disk/storage hilang atau data demo lama | Sistem regenerate dari data `surat_terbit` (QR tetap sama) |
| Data surat_terbit hilang | Belum pernah diterbitkan | 404 / unduh gagal; hubungi admin |
