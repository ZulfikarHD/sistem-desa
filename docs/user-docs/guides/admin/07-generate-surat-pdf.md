# Generate Bukti Pengambilan PDF - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 7 dari 17 — PDF bukti pengambilan dibuat otomatis saat Anda menyetujui pengajuan.
> **Prasyarat:** [Pengaturan Desa](03-pengaturan-desa.md) sudah diisi agar kop PDF benar.

## Apa itu Generate PDF?

Setelah admin **menyetujui** pengajuan, sistem otomatis membuat **Bukti Pengambilan Berkas** (PDF) berisi kop desa, nomor referensi, data pemohon, jenis surat, dan kode QR. Ini **bukan** surat keterangan resmi — warga menggunakannya saat mengambil berkas di kantor.

Jika pengajuan **ditolak**, PDF **tidak** dibuat.

## Cara Menggunakan

### Lewat Persetujuan

1. Buka **Daftar Pengajuan Surat**.
2. Buka detail pengajuan **Diajukan**.
3. Periksa data dan dokumen.
4. Klik **Setujui**.
5. Status menjadi **Diproses** — PDF bukti + QR dibuat otomatis (jadwal masih “Belum ditetapkan”).
6. Setelah Anda menandai **Siap Diambil**, PDF diperbarui dengan tanggal/jam, dan warga baru bisa mengunduh.

### Menolak tanpa Generate

1. Klik **Tolak** dan isi alasan.
2. Status **Ditolak** — tidak ada PDF/QR.

## FAQ

**Q: Apakah warga bisa unduh segera setelah setujui?**
A: Tidak. Unduh bukti hanya setelah status **Siap Diambil**.

**Q: Apa isi kode QR?**
A: Token pengambilan sekali pakai. Setelah discan petugas, QR tidak berlaku lagi.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Pratinjau PDF kosong | Refresh; pastikan setujui berhasil |
| Kop masih “Contoh” | Ubah di **Pengaturan Desa** |
