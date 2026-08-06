# Generate Surat PDF - Panduan Pengguna

## Apa itu Generate Surat PDF?

Setelah admin **menyetujui** pengajuan, sistem otomatis menyiapkan **surat keterangan dalam bentuk PDF**. Surat berisi kop desa, nomor resmi, data pemohon, keperluan, tanggal terbit, nama penandatangan, dan kode QR untuk pengambilan nanti.

Jika pengajuan **ditolak**, surat PDF **tidak** dibuat.

## Cara Menggunakan

### Untuk Admin — Menerbitkan Surat lewat Persetujuan

1. Buka **Verifikasi Pengajuan**
2. Buka detail pengajuan berstatus **Diajukan**
3. Periksa data dan dokumen
4. Klik **Setujui**
5. Status menjadi **Diproses** — sistem otomatis membuat PDF surat di belakang layar

> 💡 **Tips:** Anda tidak perlu menekan tombol terpisah untuk membuat PDF. Persetujuan sudah memicu penerbitan.

### Untuk Admin — Menolak tanpa Menerbitkan

1. Buka detail pengajuan
2. Klik **Tolak**
3. Isi alasan penolakan (wajib)
4. Status menjadi **Ditolak** — **tidak ada** file surat yang dibuat

### Untuk Warga

1. Setelah pengajuan disetujui, status di riwayat menjadi **Diproses**
2. Notifikasi in-app memberi tahu bahwa pengajuan disetujui dan sedang diproses
3. Unduh PDF untuk warga akan tersedia pada tahap berikutnya (fitur unduh surat)

## FAQ

**Q: Apakah saya bisa mengunduh surat segera setelah disetujui?**  
A: File PDF sudah digenerate saat status Diproses. Tombol unduh untuk warga akan ditambahkan di fitur berikutnya.

**Q: Apa isi kode QR di surat?**  
A: Kode QR dipakai saat pengambilan surat di kantor desa. Setelah discan sekali, QR tidak bisa dipakai ulang.

**Q: Jika ditolak, apakah tetap ada surat?**  
A: Tidak. Penolakan tidak menghasilkan PDF maupun QR.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Status sudah Diproses tapi surat belum ada | Hubungi admin/teknisi untuk cek penyimpanan file dan log aplikasi |
| Data di surat salah (nama/alamat) | Perbarui profil warga sebelum mengajukan ulang, atau hubungi admin |
| Penandatangan/kop desa perlu diubah | Admin sistem mengubah konfigurasi desa di lingkungan server |
