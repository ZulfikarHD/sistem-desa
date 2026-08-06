# Scan QR Pengambilan - Panduan Pengguna

## Apa itu Scan QR Pengambilan?

Setiap surat yang sudah diterbitkan punya **kode QR sekali pakai**. Saat warga datang mengambil surat, petugas desa memindai QR itu. Setelah scan berhasil:

- Status pengajuan menjadi **Selesai**
- QR **tidak bisa dipakai lagi** (meski admin lain yang memindai)
- Warga mendapat notifikasi bahwa pengajuan sudah selesai

QR **tidak kedaluwarsa berdasarkan waktu** — tetap berlaku sampai berhasil dipindai sekali (dan hanya jika status sudah **Siap Diambil**).

## Cara Menggunakan

### Untuk Admin — Memindai QR saat pengambilan

1. Pastikan pengajuan sudah berstatus **Siap Diambil** (tanggal pengambilan sudah diatur)
2. Buka menu **Scan QR Pengambilan** di sidebar admin
3. Pilih salah satu cara:
   - **Kamera:** klik **Mulai Kamera**, izinkan akses kamera, arahkan ke QR pada surat
   - **Manual:** tempel token QR ke kotak **Token QR**, lalu klik **Proses Scan**
4. Jika berhasil, muncul pesan bahwa pengambilan tercatat dan QR tidak valid lagi

> 💡 **Tips:** Jika browser tidak mendukung kamera atau izin kamera ditolak, gunakan input token manual.

### Untuk Admin — Scan ulang

1. Jika QR sudah pernah berhasil dipindai, scan berikutnya **selalu ditolak**
2. Pesan yang muncul: **QR sudah digunakan / tidak valid**
3. Tidak ada pengecualian antar-admin — semua petugas mendapat penolakan yang sama

### Untuk Admin — Token salah atau belum siap diambil

1. Token yang tidak dikenal → ditolak
2. QR masih valid tetapi status belum **Siap Diambil** → ditolak (belum siap diambil)
3. Unduh/cetak ulang PDF **tidak** membuat QR baru dan **tidak** mengembalikan QR ke status valid

### Untuk Warga

1. Setelah pengambilan berhasil, Anda mendapat notifikasi bahwa pengajuan **selesai**
2. QR pada surat tidak bisa dipakai untuk pengambilan kedua
3. File PDF tetap bisa disimpan; itu tidak mengaktifkan ulang QR

## FAQ

**Q: Bisakah QR dipindai berkali-kali?**  
A: Tidak. Setelah sukses sekali, QR permanen tidak valid.

**Q: Apakah QR kadaluarsa dalam beberapa hari?**  
A: Tidak. Selama status masih Siap Diambil dan belum pernah discan, QR tetap bisa dipakai.

**Q: Admin lain bisa scan QR yang sudah dipakai?**  
A: Tidak. Semua admin akan ditolak dengan pesan yang sama.

**Q: Di mana QR-nya?**  
A: Tercetak pada PDF surat yang digenerate setelah pengajuan disetujui.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Kamera tidak jalan | Izinkan akses kamera, atau gunakan input token manual |
| Pesan "belum siap diambil" | Pastikan admin sudah menandai dokumen siap diambil (status Siap Diambil) |
| Pesan "QR sudah digunakan" | Pengambilan sudah tercatat; tidak perlu scan ulang |
| Pesan "tidak dikenal" | Periksa token/QR dari PDF yang benar untuk pengajuan tersebut |
