# AD-06: Proses Surat & Penetapan Jadwal Pengambilan

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | AD-06 |
| **Nama Proses** | Proses Surat dan Penetapan Jadwal Pengambilan |
| **Aktor** | Admin / Petugas Desa |
| **Use Case Terkait** | UC-18, UC-19, UC-24 |
| **Panduan Pengguna** | [Surat Diproses](../../guides/admin/09-surat-diproses.md) · [Dokumen Siap Diambil](../../guides/admin/10-dokumen-siap-diambil.md) |

## Deskripsi

Proses ini menggambarkan alur aktivitas setelah admin menyetujui pengajuan (surat berstatus **Diproses**). Admin membuka menu Surat Diproses, menetapkan tanggal pengambilan sesuai hari kerja, lalu sistem mengubah status menjadi **Siap Diambil** dan mengirim notifikasi ke warga beserta jadwal.

**Prasyarat:** Pengajuan sudah berstatus **Diproses** (PDF sudah digenerate setelah admin menyetujui).

**Hasil:** Status pengajuan berubah menjadi **Siap Diambil**; warga mendapat notifikasi jadwal pengambilan.

## Diagram Aktivitas

```mermaid
flowchart TD
    Start([Mulai]) --> A[Login sebagai admin]
    A --> B[Buka menu Surat Diproses dari sidebar]
    B --> C[Sistem tampilkan daftar surat\nberstatus Diproses]
    C --> D[Klik Lihat Detail pada baris surat]
    D --> E[Sistem tampilkan halaman detail\ndengan data warga dan PDF surat]
    E --> F[Periksa data warga dan PDF surat]
    F --> G[Klik field Tanggal Pengambilan]
    G --> H[Pilih tanggal dari kalender]
    H --> I{Validasi tanggal}
    I -->|"Tanggal sudah lampau"| J1[Tampilkan pesan error\ntanggal tidak valid]
    J1 --> G
    I -->|"Hari Sabtu atau Minggu"| J2[Tampilkan pesan error\nbukan hari kerja]
    J2 --> G
    I -->|"Hari libur nasional"| J3[Tampilkan pesan error\nhari libur]
    J3 --> G
    I -->|"Tanggal valid\nhari kerja Senin-Jumat"| K[Sistem tampilkan label Jam Kerja otomatis:\nSenin-Kamis 08.00-16.00 atau Jumat 08.00-16.30]
    K --> L[Klik tombol Siap Diambil]
    L --> M[Sistem simpan tanggal_pengambilan\ndan siap_diambil_at]
    M --> N[Sistem ubah status → Siap Diambil]
    N --> O[Sistem kirim notifikasi ke warga:\nJenis surat + nomor + tanggal + jam kerja]
    O --> P[Surat hilang dari daftar Surat Diproses]
    P --> End([Selesai])
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| 1 | Buka Surat Diproses | Admin membuka menu khusus untuk surat yang sudah disetujui |
| 2 | Lihat detail | Admin memeriksa data dan dapat pratinjau PDF |
| 3 | Pilih tanggal | Admin memilih tanggal pengambilan dari kalender |
| 4 | Validasi tanggal | Sistem menolak tanggal lampau, weekend, dan libur nasional |
| 5 | Jam kerja otomatis | Label jam kerja muncul otomatis berdasarkan hari yang dipilih |
| 6 | Klik Siap Diambil | Admin mengkonfirmasi penetapan jadwal |
| 7 | Simpan & notifikasi | Sistem menyimpan jadwal dan mengirim notifikasi ke warga |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Tanggal lampau | Tanggal sebelum hari ini | Pesan error, tombol Siap Diambil tidak aktif |
| Hari Sabtu/Minggu | Weekend bukan hari kerja | Pesan error |
| Libur nasional | Tanggal masuk daftar libur konfigurasi | Pesan error |
