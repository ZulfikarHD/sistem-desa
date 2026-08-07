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

Proses ini menggambarkan alur aktivitas saat warga mengunduh atau mencetak file PDF surat keterangan yang sudah diterbitkan oleh admin. Tombol unduh hanya tersedia jika status pengajuan sudah **Diproses**, **Siap Diambil**, atau **Selesai**. Unduh ulang tidak menghasilkan file baru — sistem menyajikan file yang sudah ada.

**Prasyarat:** Pengajuan berstatus Diproses / Siap Diambil / Selesai dan file PDF sudah tersimpan di server.

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

    J --> O[Sistem sajikan file PDF yang sudah ada\ntanpa generate ulang]
    O --> P[Browser mengunduh file PDF]
    P --> End

    N --> Q[Sistem buka PDF di tab baru browser]
    Q --> R[Warga pilih menu Cetak / Print di browser]
    R --> End
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| 1 | Login | Warga harus terautentikasi |
| 2 | Temukan pengajuan | Dari dashboard atau riwayat pengajuan |
| 3 | Cek status | Tombol unduh hanya muncul untuk status tertentu |
| 4 | Pilih aksi | Unduh langsung atau buka detail dulu |
| 5 | Unduh | Sistem menyajikan file yang sudah ada (bukan generate ulang) |
| 6 (opsional) | Cetak | PDF terbuka di tab baru, siap dicetak dari browser |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Status Diajukan/Ditolak | PDF belum digenerate | Tombol unduh tidak ditampilkan |
| File tidak ditemukan di server | Error penyimpanan | Pesan error; warga perlu menghubungi admin |
