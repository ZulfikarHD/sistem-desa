# Surat Diproses & Siap Diambil - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 8 dari 15 — Kelola surat yang sedang diproses dan tandai siap diambil.

## Apa itu Surat Diproses?

Setelah petugas **menyetujui** pengajuan, surat masuk status **Diproses** (PDF sudah dibuat). Menu **Surat Diproses** menampilkan daftar surat yang sedang disiapkan, terpisah dari daftar yang masih menunggu verifikasi.

## Cara Menggunakan

### Melihat daftar surat diproses

1. Masuk sebagai **admin**.
2. Di sidebar, klik **Surat Diproses** (di bawah **Daftar Pengajuan Surat**).
3. Tabel menampilkan nomor pengajuan, nama warga, jenis surat, tanggal pengajuan, nomor surat, dan tanggal surat digenerate.
4. Jika kosong, muncul pesan: *Tidak ada surat yang sedang diproses saat ini.*

### Menandai surat siap diambil

1. Klik **Lihat Detail** pada baris surat.
2. Periksa data warga dan pratinjau PDF (atau unduh PDF).
3. Isi **Tanggal Pengambilan** — hanya hari ini atau ke depan; Sabtu/Minggu dan libur nasional tidak boleh.
4. Pastikan label **Jam Kerja** muncul (Senin–Kamis atau Jumat).
5. Klik **Siap Diambil**.
6. Warga mendapat notifikasi berisi tanggal dan jam kerja; surat hilang dari daftar Surat Diproses.

> 💡 **Tips:** Tanggal lampau tidak bisa dipilih di kalender. Jika tetap dipaksa lewat cara lain, sistem menolak di server.

### Setelah siap diambil

- Status menjadi **Siap Diambil**
- Petugas dapat memindai QR saat warga datang (menu **Scan QR Pengambilan**)
- Warga melihat tanggal/jam di riwayat dan notifikasi

## FAQ

**Q: Kenapa surat tidak muncul di Daftar Pengajuan Surat setelah disetujui?**
A: Karena sudah berstatus Diproses — cek menu **Surat Diproses**.

**Q: Tombol Siap Diambil abu-abu?**
A: Pilih tanggal hari kerja yang valid terlebih dahulu.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tanggal Sabtu/Minggu | Pilih Senin–Jumat |
| Tanggal libur nasional | Pilih hari kerja lain |
| PDF tidak muncul | Pastikan setujui berhasil generate surat; hubungi admin teknis |
| Form tanggal tidak ada | Status sudah Siap Diambil/Selesai — lihat info status terkini |
