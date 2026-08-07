# Migrasi Alur Status Surat - Panduan Pengguna (Admin)

> **Kelompok Pengguna:** Admin / Petugas Desa
> **Urutan:** 12 dari 15 — Referensi arti tiap status dan alur perubahan status surat.

## Apa itu Migrasi Alur Status?

Sistem memakai status pengajuan berikut:

| Status | Arti |
|--------|------|
| Diajukan | Menunggu pemeriksaan admin |
| Diproses | Surat sedang disiapkan (PDF sudah digenerate setelah Setujui) |
| Siap Diambil | Surat siap diambil di kantor |
| Selesai | Sudah diambil (QR sudah digunakan) |
| Ditolak | Pemeriksaan gagal — alur berhenti |
| Disetujui (historis) | Hanya data lama; di tampilan badge ditulis **Diproses** |

## Cara Menggunakan

### Memeriksa Pengajuan

1. Buka **Daftar Pengajuan Surat**.
2. Filter default menampilkan status **Diajukan**.
3. Klik baris pengajuan untuk melihat detail dan dokumen.
4. Status tetap **Diajukan** saat halaman detail dibuka (tidak berubah otomatis).
5. Pilih **Setujui** atau **Tolak**:
   - **Setujui**: langsung masuk **Diproses** (+ PDF + notifikasi warga)
   - **Tolak**: wajib isi alasan; status menjadi **Ditolak**

> 💡 **Tips:** Tombol Setujui/Tolak hanya muncul jika status masih Diajukan.

### Rekap Status

1. Buka **Rekap Pengajuan**.
2. Ringkasan menampilkan jumlah per status, termasuk **Siap Diambil** dan **Selesai**.
3. Filter status mendukung semua status, termasuk data **Disetujui (historis)**.

## FAQ

**Q: Mengapa tidak ada status Disetujui di alur baru?**
A: Persetujuan dan proses surat digabung — setelah Setujui langsung **Diproses**.

**Q: Apakah data lama Disetujui hilang?**
A: Tidak. Nilai di database tetap ada; di layar biasanya tampil sebagai **Diproses**.

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Filter Disetujui tidak ada | Gunakan **Disetujui (historis)** di filter rekap |
| Status tidak berubah setelah setujui | Cek log — mungkin ada error saat generate PDF |
