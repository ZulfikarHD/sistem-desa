# AD-04: Pengajuan Surat Keterangan

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | AD-04 |
| **Nama Proses** | Pengajuan Surat Keterangan |
| **Aktor** | Warga |
| **Use Case Terkait** | UC-09, UC-10 |
| **Panduan Pengguna** | [Pengajuan Surat](../../guides/warga/05-pengajuan-surat-form.md) · [Unggah Dokumen](../../guides/warga/06-pengajuan-surat-dokumen.md) · [Validasi Kelengkapan](../../guides/warga/07-pengajuan-surat-kelengkapan.md) |

## Deskripsi

Proses ini menggambarkan alur aktivitas saat warga yang sudah login mengajukan permohonan surat keterangan secara online. Proses mencakup pemilihan jenis surat, pengunggahan dokumen persyaratan (KTP/KK), pengisian keperluan, validasi kelengkapan oleh sistem, hingga nomor pengajuan diterbitkan.

**Prasyarat:** Warga sudah login dan admin telah mengisi master data jenis surat.

**Hasil:** Pengajuan tersimpan di sistem dengan nomor pengajuan unik (format: `PJ-YYYYMMDD-####`).

## Diagram Aktivitas

```mermaid
flowchart TD
    Start([Mulai]) --> A[Login sebagai warga]
    A --> B[Buka menu Pengajuan Surat dari sidebar]
    B --> C[Pilih Jenis Surat dari dropdown]
    C --> D{Jenis surat memerlukan\ndokumen persyaratan?}

    D -->|"Ya — KTP dan/atau KK diperlukan"| E[Area unggah dokumen muncul]
    E --> F[Klik Pilih file untuk KTP]
    F --> G{Validasi file KTP}
    G -->|"Format tidak valid\nbukan JPG/PNG/PDF"| H1[Tampilkan pesan error format]
    H1 --> F
    G -->|"Ukuran lebih dari 2 MB"| H2[Tampilkan pesan error ukuran]
    H2 --> F
    G -->|"File valid"| I[Pratinjau KTP ditampilkan]
    I --> J{KK juga diperlukan?}
    J -->|"Ya"| K[Klik Pilih file untuk KK]
    K --> L{Validasi file KK}
    L -->|"Tidak valid"| M[Tampilkan pesan error]
    M --> K
    L -->|"Valid"| N[Pratinjau KK ditampilkan]
    J -->|"Tidak"| O

    D -->|"Tidak — tidak ada dokumen wajib"| O
    N --> O[Isi kolom Keperluan]

    O --> P[Klik Kirim Pengajuan]
    P --> Q{Validasi kelengkapan\noleh sistem}
    Q -->|"Jenis surat belum dipilih"| R1[Tampilkan pesan error jenis surat]
    R1 --> C
    Q -->|"Keperluan belum diisi"| R2[Tampilkan pesan error keperluan]
    R2 --> O
    Q -->|"Dokumen wajib belum diunggah"| R3[Tampilkan pesan error dokumen]
    R3 --> F
    Q -->|"Semua lengkap"| S[Sistem simpan pengajuan]
    S --> T["Sistem generate nomor pengajuan\nformat: PJ-YYYYMMDD-####"]
    T --> U[Tampilkan halaman konfirmasi\ndengan nomor pengajuan]
    U --> V{Ajukan surat lain?}
    V -->|"Ya"| C
    V -->|"Tidak"| End([Selesai])
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| 1 | Login | Warga harus sudah terautentikasi |
| 2 | Pilih jenis surat | Dropdown berisi jenis surat aktif dari master data |
| 3 | Unggah dokumen | Muncul hanya jika jenis surat memerlukan KTP/KK |
| 4 | Validasi file | Sistem cek format (JPG/PNG/PDF) dan ukuran (maks. 2 MB) |
| 5 | Isi keperluan | Deskripsi tujuan pengajuan surat |
| 6 | Validasi kelengkapan | Sistem cek semua field wajib terisi |
| 7 | Simpan & nomor | Pengajuan tersimpan, nomor unik digenerate |
| 8 | Konfirmasi | Halaman konfirmasi dengan nomor pengajuan ditampilkan |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Format file tidak valid | File bukan JPG/PNG/PDF | Pesan error pada field unggah |
| Ukuran file > 2 MB | File terlalu besar | Pesan error pada field unggah |
| Jenis surat tidak dipilih | Field kosong | Pesan error validasi |
| Keperluan tidak diisi | Field kosong | Pesan error validasi |
| Dokumen wajib belum diunggah | File belum dipilih | Pesan error validasi |
