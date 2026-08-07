# AD-05: Verifikasi Pengajuan oleh Admin

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | AD-05 |
| **Nama Proses** | Verifikasi Pengajuan oleh Admin |
| **Aktor** | Admin / Petugas Desa |
| **Use Case Terkait** | UC-17, UC-22, UC-23, UC-24 |
| **Panduan Pengguna** | [Verifikasi Pengajuan](../../guides/admin/04-verifikasi-pengajuan.md) · [Generate Surat PDF](../../guides/admin/06-generate-surat-pdf.md) |

## Deskripsi

Proses ini menggambarkan alur aktivitas saat admin/petugas desa memeriksa pengajuan surat yang masuk dan mengambil keputusan setujui atau tolak. Jika disetujui, sistem secara otomatis membuat PDF surat, nomor surat resmi, QR code, dan mengirim notifikasi ke warga. Jika ditolak, warga mendapat notifikasi beserta alasan.

**Prasyarat:** Terdapat pengajuan dengan status **Diajukan** yang menunggu verifikasi.

**Hasil:** Status pengajuan berubah menjadi **Diproses** (jika disetujui) atau **Ditolak** (jika ditolak).

## Diagram Aktivitas

```mermaid
flowchart TD
    Start([Mulai]) --> A[Login sebagai admin]
    A --> B[Buka menu Daftar Pengajuan Surat]
    B --> C[Sistem tampilkan daftar pengajuan\nberstatus Diajukan]
    C --> D[Klik baris pengajuan yang akan diperiksa]
    D --> E[Sistem tampilkan halaman detail pengajuan\nstatus tidak berubah otomatis]
    E --> F[Periksa data warga:\nnama, NIK, jenis surat, keperluan]
    F --> G{Dokumen persyaratan\ndiunggah?}
    G -->|"Ada dokumen"| H[Pratinjau atau unduh dokumen KTP/KK]
    H --> I{Dokumen lengkap\ndan dapat dibaca?}
    G -->|"Tidak ada dokumen"| I
    I -->|"Tidak lengkap / tidak valid"| J[Klik tombol Tolak]
    J --> K[Isi Alasan Penolakan — wajib diisi]
    K --> L{Alasan diisi?}
    L -->|"Kosong"| M[Sistem tampilkan pesan error\nalasan wajib diisi]
    M --> K
    L -->|"Terisi"| N[Klik Tolak Pengajuan]
    N --> O[Sistem ubah status → Ditolak]
    O --> P[Sistem kirim notifikasi ke warga:\nPengajuan Ditolak + alasan]
    P --> End([Selesai])

    I -->|"Lengkap dan valid"| Q[Klik tombol Setujui]
    Q --> R[Sistem ubah status → Diproses\nlangsung satu langkah]
    R --> S[Sistem generate PDF surat\nmenggunakan DomPDF]
    S --> T["Sistem buat nomor surat resmi\nformat: 470/{urut}/DS-WDN/{romawi}/{tahun}"]
    T --> U[Sistem sisipkan QR code sekali pakai\nke dalam PDF]
    U --> V[Sistem kirim satu notifikasi ke warga:\nSurat Sedang Diproses]
    V --> End
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| 1 | Buka daftar pengajuan | Admin membuka menu Daftar Pengajuan Surat |
| 2 | Pilih pengajuan | Klik baris untuk membuka detail; status tidak berubah saat dibuka |
| 3 | Periksa data | Admin membaca data warga, jenis surat, keperluan |
| 4 | Periksa dokumen | Pratinjau atau unduh KTP/KK jika ada |
| 5a (Setujui) | Klik Setujui | Status langsung → Diproses dalam satu langkah |
| 5a | Generate PDF | Sistem buat PDF otomatis menggunakan DomPDF |
| 5a | Nomor resmi | Sistem generate nomor surat berurutan |
| 5a | QR code | Sistem sisipkan QR sekali pakai ke PDF |
| 5a | Notifikasi | Satu notifikasi dikirim ke warga |
| 5b (Tolak) | Klik Tolak | Admin wajib mengisi alasan penolakan |
| 5b | Notifikasi | Notifikasi + alasan dikirim ke warga |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Alasan tolak kosong | Admin tidak mengisi field alasan | Pesan error, form tidak dapat dikirim |
| Gagal generate PDF | Error penyimpanan atau konfigurasi | Log error sistem; admin perlu mengecek server |
