# Migrasi Alur Status Surat - Panduan Pengguna

## Apa itu Migrasi Alur Status?

Sistem mengubah arti status pengajuan agar lebih jelas untuk warga dan admin:

| Status | Arti |
|--------|------|
| Diajukan | Menunggu verifikasi admin |
| Disetujui | Data lolos verifikasi (lalu otomatis diproses) |
| Diproses | Surat sedang disiapkan (PDF) |
| Siap Diambil | Surat siap diambil di kantor |
| Selesai | Sudah diambil (QR sudah digunakan) |
| Ditolak | Verifikasi gagal — alur berhenti |

## Cara Menggunakan

### Untuk Admin — Memverifikasi Pengajuan

1. Buka **Verifikasi Pengajuan**
2. Filter default menampilkan status **Diajukan**
3. Klik baris pengajuan untuk melihat detail dan dokumen
4. Status tetap **Diajukan** saat halaman detail dibuka (tidak berubah otomatis)
5. Pilih **Setujui** atau **Tolak**
   - **Setujui**: pengajuan disetujui lalu otomatis masuk **Diproses**
   - **Tolak**: wajib isi alasan; status menjadi **Ditolak**

> 💡 **Tips:** Tombol Setujui/Tolak hanya muncul jika status masih Diajukan.

### Untuk Warga — Melihat Status

1. Buka **Status & Riwayat Pengajuan**
2. Gunakan filter status (termasuk Siap Diambil dan Selesai)
3. Notifikasi muncul saat admin menyetujui/menolak, bukan saat admin sekadar membuka detail

### Untuk Admin — Rekap

1. Buka **Rekap Pengajuan**
2. Ringkasan menampilkan jumlah per status, termasuk **Siap Diambil** dan **Selesai**
3. Filter status mendukung semua status baru

## FAQ

**Q: Mengapa status tidak jadi Diproses saat saya buka detail?**  
A: Itu sengaja. Diproses sekarang berarti surat sedang disiapkan setelah disetujui, bukan “sedang dibaca admin.”

**Q: Setelah saya setujui, kenapa status akhirnya Diproses bukan Disetujui?**  
A: Sistem mencatat Disetujui lalu otomatis lanjut Diproses untuk persiapan surat.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tombol Setujui/Tolak hilang | Pastikan status masih Diajukan |
| Filter Siap Diambil kosong | Status itu baru terisi setelah fitur penjadwalan pengambilan (fase berikutnya) |
