# Verifikasi / Daftar Pengajuan Surat - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 5 dari 17 — Cara memeriksa, menyetujui, atau menolak pengajuan warga.

## Apa itu Daftar Pengajuan Surat?

Fitur ini membantu **admin/petugas desa** memeriksa pengajuan surat keterangan yang sudah dikirim warga. Petugas melihat daftar pengajuan yang menunggu pemeriksaan, membuka detail, mempratinjau dokumen KTP/KK, lalu **menyetujui** atau **menolak** pengajuan.

Menu di sidebar bernama **Daftar Pengajuan Surat**.

## Cara Menggunakan

### Melihat Daftar Pengajuan

1. Masuk ke sistem sebagai **admin/petugas desa**.
2. Di menu samping, klik **Daftar Pengajuan Surat**.
3. Halaman menampilkan pengajuan dengan status **Diajukan** secara default.
4. Tabel menampilkan nomor, nama warga, jenis surat, dan tanggal pengajuan.
5. Gunakan **Filter Status** untuk status lain.

> 💡 **Tips:** Fokuskan pemeriksaan pada filter **Diajukan** — itulah antrean yang perlu ditindaklanjuti.

### Membuka Detail Pengajuan

1. Dari daftar, **klik baris** pengajuan yang ingin diperiksa.
2. Halaman detail menampilkan data warga, jenis surat, keperluan, dan dokumen persyaratan.

### Memeriksa Dokumen

1. Di bagian **Dokumen Persyaratan**, pratinjau gambar atau PDF ditampilkan langsung.
2. Jika pratinjau tidak tersedia, klik **Unduh Dokumen**.

### Menyetujui Pengajuan

1. Klik **Setujui**.
2. Sistem langsung mengubah status menjadi **Diproses** (tanpa status Disetujui di tengah).
3. PDF surat digenerate otomatis; warga menerima **satu** notifikasi bahwa surat sedang diproses.
4. Pengajuan hilang dari daftar filter **Diajukan**.

### Menolak Pengajuan

1. Klik **Tolak**.
2. Isi **Alasan Penolakan** (wajib).
3. Klik **Tolak Pengajuan**.
4. Status menjadi **Ditolak**.
5. Warga dapat melihat alasan di riwayat pengajuan.

> 💡 **Tips:** Tulis alasan yang jelas agar warga dapat memperbaiki dokumen atau mengajukan ulang.

## FAQ

**Q: Mengapa daftar saya kosong?**
A: Belum ada pengajuan **Diajukan**, atau filter status sedang diubah.

**Q: Setelah setujui, kenapa langsung Diproses?**
A: Alur baru menggabungkan persetujuan dan mulai proses surat dalam satu langkah.

**Q: Apakah saya bisa menolak tanpa alasan?**
A: Tidak. Alasan penolakan wajib diisi.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tombol Setujui hilang | Pengajuan sudah diproses/ditolak |
| Tidak menemukan menu Verifikasi Pengajuan | Gunakan **Daftar Pengajuan Surat** |
