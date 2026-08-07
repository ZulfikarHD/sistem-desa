# AD-07: Pengambilan Surat dengan Scan QR

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | AD-07 |
| **Nama Proses** | Pengambilan Surat dengan Scan QR |
| **Aktor** | Admin / Petugas Desa |
| **Use Case Terkait** | UC-20 |
| **Panduan Pengguna** | [Scan QR Pengambilan](../../guides/admin/11-qr-sekali-pakai.md) |

## Deskripsi

Proses ini menggambarkan alur aktivitas saat warga datang ke kantor desa untuk mengambil surat yang sudah siap. Admin memindai QR code sekali pakai pada surat PDF milik warga. Setelah scan berhasil, status pengajuan berubah menjadi **Selesai** dan QR tidak bisa digunakan lagi.

**Prasyarat:** Pengajuan berstatus **Siap Diambil** dan warga membawa surat PDF dengan QR code yang masih valid.

**Hasil:** Status pengajuan berubah menjadi **Selesai**; QR code dinonaktifkan; warga mendapat notifikasi.

## Diagram Aktivitas

```mermaid
flowchart TD
    Start([Mulai]) --> A[Warga datang ke kantor desa\ndengan menunjukkan QR di PDF surat]
    A --> B[Admin buka menu Scan QR Pengambilan]
    B --> C{Pilih metode scan}
    C -->|"Kamera"| D[Klik Mulai Kamera]
    D --> E[Browser minta izin kamera]
    E --> F{Izin diberikan?}
    F -->|"Ditolak"| G[Gunakan input token manual]
    F -->|"Diberikan"| H[Arahkan kamera ke QR pada surat]
    H --> I[Sistem baca token dari QR]
    C -->|"Manual"| G
    G --> J[Tempel token QR ke kotak input]
    J --> K[Klik Proses Scan]
    I --> L{Validasi token}
    K --> L
    L -->|"Token tidak dikenal"| M1[Tampilkan pesan error:\nQR tidak valid]
    M1 --> End([Selesai])
    L -->|"Status belum Siap Diambil"| M2[Tampilkan pesan error:\nBelum siap diambil]
    M2 --> End
    L -->|"QR sudah pernah digunakan"| M3[Tampilkan pesan error:\nQR sudah digunakan]
    M3 --> End
    L -->|"Token valid dan belum dipakai"| N[Sistem tandai QR sudah digunakan\nqr_status = invalid]
    N --> O[Sistem catat timestamp qr_digunakan_at\ndan qr_digunakan_oleh admin]
    O --> P[Sistem ubah status pengajuan → Selesai]
    P --> Q[Tampilkan konfirmasi pengambilan berhasil]
    Q --> R[Admin serahkan surat fisik ke warga]
    R --> End
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| 1 | Warga datang | Warga menunjukkan PDF surat dengan QR code |
| 2 | Buka menu scan | Admin membuka halaman Scan QR Pengambilan |
| 3 | Pilih metode | Kamera (jika didukung browser) atau input token manual |
| 4 | Validasi token | Sistem cek token: dikenal, status valid, belum pernah dipakai |
| 5 | Tandai QR | Token dinonaktifkan, tidak bisa dipakai lagi |
| 6 | Ubah status | Pengajuan → Selesai |
| 7 | Konfirmasi | Layar menampilkan pesan berhasil |
| 8 | Serahkan surat | Admin menyerahkan surat fisik ke warga |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Token tidak dikenal | QR dari surat yang berbeda atau rusak | Pesan error: QR tidak valid |
| Status bukan Siap Diambil | Surat belum ditandai siap diambil | Pesan error: belum siap diambil |
| QR sudah dipakai | Sudah pernah di-scan sebelumnya | Pesan error: QR sudah digunakan |
| Kamera tidak bisa jalan | Browser tidak support atau izin ditolak | Gunakan input token manual |
