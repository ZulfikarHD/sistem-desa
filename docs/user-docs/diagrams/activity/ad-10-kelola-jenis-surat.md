# AD-10: Kelola Master Jenis Surat

## Informasi Diagram

| Atribut | Nilai |
|---------|-------|
| **Kode** | AD-10 |
| **Nama Proses** | Kelola Master Jenis Surat |
| **Aktor** | Admin / Petugas Desa |
| **Use Case Terkait** | UC-16 |
| **Panduan Pengguna** | [Kelola Jenis Surat](../../guides/admin/04-jenis-surat.md) |

## Deskripsi

Proses ini menggambarkan alur aktivitas admin dalam mengelola data master jenis surat keterangan. Admin dapat menambah jenis surat baru (beserta baris persyaratan terstruktur: unggah / bawa kantor / info), mengubah data yang ada, mengarsipkan (soft delete), memulihkan dari arsip, atau menghapus permanen. Data ini digunakan sebagai pilihan warga saat mengajukan surat.

**Prasyarat:** Admin sudah login dan mengakses menu Jenis Surat.

**Hasil:** Data master jenis surat terperbarui sesuai aksi yang dilakukan.

## Diagram Aktivitas

```mermaid
flowchart TD
    Start([Mulai]) --> A[Login sebagai admin]
    A --> B[Buka menu Jenis Surat dari sidebar]
    B --> C[Tampil halaman daftar jenis surat aktif]
    C --> D{Pilih aksi}

    %% TAMBAH
    D -->|"Tambah"| E[Klik Tambah Jenis Surat]
    E --> F["Isi form:\n- Nama Surat (wajib)\n- Deskripsi (opsional)\n- Baris persyaratan (≥1):\n  nama + cara memenuhi\n  (+ wajib/opsional jika unggah)"]
    F --> G[Klik Simpan]
    G --> H{Validasi}
    H -->|"Nama sudah digunakan"| I1[Tampilkan pesan error duplikat]
    I1 --> F
    H -->|"Nama syarat kosong / tanpa baris"| I2[Tampilkan pesan error]
    I2 --> F
    H -->|"Valid"| J[Jenis surat + baris persyaratan tersimpan]
    J --> End([Selesai])

    %% UBAH
    D -->|"Ubah"| K[Klik Ubah pada baris yang dipilih]
    K --> L[Edit data yang diperlukan]
    L --> M[Klik Simpan]
    M --> N{Validasi}
    N -->|"Tidak valid"| O[Tampilkan pesan error]
    O --> L
    N -->|"Valid"| P[Data tersimpan]
    P --> End

    %% ARSIPKAN
    D -->|"Arsipkan"| Q[Klik Arsipkan pada baris aktif]
    Q --> R[Sistem tampilkan konfirmasi]
    R --> S{Konfirmasi?}
    S -->|"Batal"| C
    S -->|"Ya"| T[Jenis surat dipindah ke arsip\ntidak muncul di form warga]
    T --> End

    %% PULIHKAN
    D -->|"Pulihkan dari arsip"| U[Aktifkan sakelar Tampilkan arsip]
    U --> V[Klik Pulihkan pada baris arsip]
    V --> W[Jenis surat kembali ke daftar aktif]
    W --> End

    %% HAPUS PERMANEN
    D -->|"Hapus permanen"| X[Aktifkan sakelar Tampilkan arsip]
    X --> Y[Klik Hapus Permanen pada baris arsip]
    Y --> Z[Sistem tampilkan konfirmasi hapus permanen]
    Z --> AA{Konfirmasi?}
    AA -->|"Batal"| C
    AA -->|"Ya"| AB{Ada pengajuan terkait\ndi database?}
    AB -->|"Ada"| AC[Sistem cegah penghapusan\ntampilkan pesan error]
    AC --> End
    AB -->|"Tidak ada"| AD[Data dihapus permanen dari database]
    AD --> End
```

## Penjelasan Alur

| Langkah | Aktivitas | Keterangan |
|---------|-----------|------------|
| Tambah | Buat jenis surat baru | Nama wajib unik; persyaratan dokumen wajib diisi |
| Ubah | Edit data yang ada | Nama tetap harus unik |
| Arsipkan | Soft delete | Data tersimpan di arsip, tidak muncul di form warga |
| Pulihkan | Kembalikan dari arsip | Jenis surat kembali aktif dan bisa dipilih warga |
| Hapus permanen | Hard delete | Hanya dari arsip; gagal jika ada pengajuan terkait |

## Kondisi Alternatif (Error)

| Kondisi | Penyebab | Tindakan Sistem |
|---------|----------|-----------------|
| Nama duplikat | Nama sudah dipakai jenis surat lain | Pesan error duplikat |
| Persyaratan kosong | Field persyaratan tidak diisi | Pesan error validasi |
| Hapus permanen gagal | Ada pengajuan yang mengacu jenis surat ini | Sistem mencegah penghapusan |
