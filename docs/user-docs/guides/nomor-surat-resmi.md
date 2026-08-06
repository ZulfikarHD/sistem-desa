# Nomor Surat Resmi - Panduan Pengguna

## Apa itu Nomor Surat Resmi?

Setiap surat keterangan yang diterbitkan sistem mendapat **nomor surat resmi** sesuai tata naskah administrasi desa. Nomor ini berbeda dari nomor pengajuan yang warga terima saat mengirim formulir.

Contoh format:

`470/12/DS-WDN/VIII/2026`

Artinya: kode klasifikasi / nomor urut tahun itu / kode desa / bulan (Romawi) / tahun.

## Cara Menggunakan

### Untuk Admin — Nomor otomatis saat menyetujui

1. Buka **Verifikasi Pengajuan**
2. Buka detail pengajuan berstatus **Diajukan**
3. Klik **Setujui**
4. Sistem otomatis:
   - Mengubah status menjadi **Diproses**
   - Membuat PDF surat
   - Memberi **nomor surat resmi** berurutan untuk tahun berjalan
5. Nomor tercetak di bagian atas isi surat PDF (baris "Nomor: ...")

> 💡 **Tips:** Anda tidak perlu mengisi nomor secara manual. Sistem yang mengatur urutannya.

### Untuk Admin — Penolakan tidak memakai nomor

1. Jika pengajuan **ditolak**, nomor surat resmi **tidak** dibuat
2. Nomor pengajuan (`PJ-...`) tetap ada di riwayat, tetapi itu bukan nomor surat resmi

### Untuk Warga

1. Setelah pengajuan disetujui, surat PDF sudah berisi nomor resmi
2. Unduh surat (fitur unduh warga) akan memakai nomor yang sama — nomor tidak berubah saat diunduh ulang
3. Rekap nomor di halaman admin rekap akan tersedia pada tahap berikutnya

## FAQ

**Q: Apa bedanya nomor pengajuan dan nomor surat?**  
A: Nomor pengajuan (`PJ-YYYYMMDD-####`) dibuat saat warga mengajukan. Nomor surat resmi (`470/...`) dibuat saat admin menyetujui dan surat PDF diterbitkan.

**Q: Apakah nomor bisa sama untuk dua surat?**  
A: Tidak. Setiap nomor unik. Dalam satu tahun kalender, angka urut naik berurutan (1, 2, 3, ...). Di tahun baru, urutan dimulai lagi dari 1.

**Q: Bisakah admin mengubah nomor manual?**  
A: Tidak. Nomor digenerate otomatis oleh sistem agar tetap berurutan dan unik.

**Q: Di mana saya melihat nomor di surat?**  
A: Pada file PDF, di bawah judul jenis surat, ada baris **Nomor:** diikuti nomor lengkap.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Surat sudah Diproses tapi nomor aneh/kosong | Hubungi admin sistem untuk memeriksa penerbitan surat dan konfigurasi desa |
| Dua surat tampak nomor sama | Laporkan ke admin — sistem seharusnya menolak duplikat; cek data `surat_terbit` |
| Format kode desa ingin diganti | Minta admin server mengubah konfigurasi `DESA_KODE` / `DESA_KODE_KLASIFIKASI` |
