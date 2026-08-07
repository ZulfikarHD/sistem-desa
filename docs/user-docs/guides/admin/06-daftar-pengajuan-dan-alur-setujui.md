# Daftar Pengajuan Surat & Alur Setujui - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 6 dari 17 — Memahami perubahan nama menu dan alur setujui langsung diproses.

## Apa itu fitur ini?

Untuk **admin/petugas desa**, ada dua hal yang perlu diketahui:

1. Menu daftar pengajuan sekarang bernama **Daftar Pengajuan Surat** (bukan "Verifikasi Pengajuan").
2. Tombol **Setujui** langsung memproses surat: status menjadi **Diproses**, PDF digenerate, dan warga mendapat **satu** notifikasi.

## Cara Menggunakan

### Membuka Daftar Pengajuan Surat

1. Masuk sebagai **admin/petugas desa**.
2. Di menu samping, klik **Daftar Pengajuan Surat**.
3. Halaman menampilkan pengajuan berstatus **Diajukan** (antrean yang perlu diperiksa).
4. Gunakan **Filter Status** bila ingin melihat status lain. Data lama berstatus Disetujui muncul sebagai **Disetujui (historis)**.

> 💡 **Tips:** Alamat halaman tetap `/admin/verifikasi` — hanya nama menu dan judul yang berubah.

### Menyetujui Pengajuan

1. Buka detail pengajuan yang berstatus **Diajukan**.
2. Periksa data dan dokumen, lalu klik **Setujui**.
3. Sistem langsung:
   - Mengubah status menjadi **Diproses**
   - Mencatat log persetujuan
   - Membuat PDF surat
   - Mengirim notifikasi ke warga bahwa surat sedang diproses
4. Pengajuan hilang dari daftar filter **Diajukan**.

### Menolak Pengajuan

Tidak berubah: wajib isi alasan, status menjadi **Ditolak**, warga dinotifikasi.

## FAQ

**Q: Mengapa saya tidak melihat status Disetujui setelah menekan Setujui?**
A: Alur baru langsung ke **Diproses**. Status Disetujui hanya tersimpan pada data lama.

**Q: Berapa notifikasi yang diterima warga saat disetujui?**
A: Satu notifikasi — bahwa pengajuan sedang diproses dan surat sedang disiapkan.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Tombol Setujui tidak muncul | Pengajuan sudah bukan status Diajukan |
| Warga tidak dapat notifikasi | Pastikan aksi Setujui berhasil (status Diproses); cek panel notifikasi warga |
| Label menu masih lama | Refresh halaman / hard refresh browser |
