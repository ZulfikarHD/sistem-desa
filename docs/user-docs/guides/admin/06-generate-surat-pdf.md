# Generate Surat PDF - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 6 dari 15 — Surat PDF dibuat otomatis saat Anda menyetujui pengajuan.

## Apa itu Generate Surat PDF?

Setelah admin **menyetujui** pengajuan, sistem otomatis menyiapkan **surat keterangan dalam bentuk PDF**. Surat berisi kop desa, nomor resmi, data pemohon, keperluan, tanggal terbit, nama penandatangan, dan kode QR untuk pengambilan nanti.

Jika pengajuan **ditolak**, surat PDF **tidak** dibuat.

## Cara Menggunakan

### Menerbitkan Surat lewat Persetujuan

1. Buka **Daftar Pengajuan Surat**.
2. Buka detail pengajuan berstatus **Diajukan**.
3. Periksa data dan dokumen.
4. Klik **Setujui**.
5. Status menjadi **Diproses** — sistem otomatis membuat PDF surat di belakang layar.

> 💡 **Tips:** Anda tidak perlu menekan tombol terpisah untuk membuat PDF. Persetujuan sudah memicu penerbitan.

### Menolak tanpa Menerbitkan

1. Buka detail pengajuan.
2. Klik **Tolak**.
3. Isi alasan penolakan (wajib).
4. Status menjadi **Ditolak** — **tidak ada** file surat yang dibuat.

## FAQ

**Q: Apakah PDF langsung tersedia setelah setujui?**
A: Ya, setelah status **Diproses** PDF sudah tersedia untuk diunduh warga.

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
