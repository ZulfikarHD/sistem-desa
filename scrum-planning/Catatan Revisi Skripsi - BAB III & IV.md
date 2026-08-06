# Catatan Revisi Skripsi — BAB III & BAB IV

Dokumen ini merangkum bagian mana di skripsi (BAB III Metode Penelitian, BAB IV Hasil dan Pembahasan) yang perlu ditambahkan atau direvisi agar selaras dengan 4 fitur baru yang teridentifikasi dari analisis gap (Phase 07, dan penambahan story di Phase 01–04). Nomor tabel/gambar mengikuti penomoran pada draf saat ini — sesuaikan ulang jika ada penyisipan tabel/gambar baru di tengah (renumbering).

---

## 1. Penerbitan Surat Keterangan (Gap Utama — Phase 07)

Ini yang paling perlu diperhatikan karena menyangkut kesesuaian antara **3.2 Objek Penelitian** ("...hingga penerbitan surat oleh pihak berwenang di desa") dengan rancangan yang ada.

| Bagian | Yang perlu ditambahkan |
|---|---|
| **BAB I** (rumusan masalah/tujuan) | Cek ulang apakah rumusan masalah & tujuan penelitian menyebut "penerbitan surat" secara eksplisit. Jika ya, BAB III wajib menjawabnya — saat ini belum. Jika belum disebut, pertimbangkan menambahkannya agar konsisten dengan 3.2. |
| **3.5.1 Tabel 3.3** (Analisis Kelemahan & Solusi) | Tambah baris baru: *Kelemahan* — "Belum ada mekanisme penerbitan surat resmi dalam format digital setelah pengajuan disetujui, warga masih perlu ke kantor desa mengambil fisik surat." / *Solusi* — "Sistem menghasilkan dokumen surat keterangan (PDF) secara otomatis setelah status disetujui, lengkap dengan nomor surat resmi, dapat diunduh warga." |
| **3.5.2.3 Kebutuhan Informasi** | Tambah poin baru: "Dokumen surat keterangan resmi dalam format PDF yang dapat diunduh warga setelah pengajuan disetujui." |
| **3.6.2 Tabel 3.8 (Use Case)** | Tambah dua use case: "Unduh Surat Keterangan" (Aktor: Warga) dan "Terbitkan Surat" (Aktor: Sistem, trigger otomatis saat admin menyetujui). |
| **3.6.2 Activity Diagram** | Perpanjang Activity Diagram Verifikasi (Gambar 3.5) — setelah admin menyetujui, tambahkan langkah "sistem generate PDF surat & nomor surat" sebelum end state. Atau buat diagram baru "Activity Diagram Penerbitan Surat". |
| **3.6.3 ERD & Tabel Struktur** | Tambah entitas `surat_terbit` pada ERD (Gambar 3.6), dan tabel struktur baru: **Tabel 3.15 Struktur Tabel surat_terbit**. |
| **3.6.4 Rancangan Antarmuka Warga** | Tambah item: "Tombol/Halaman Unduh Surat, muncul pada baris pengajuan berstatus disetujui di halaman Status & Riwayat Pengajuan." |
| **BAB IV 4.1.1** | Tambah subbagian implementasi: tampilan tombol/halaman unduh surat + contoh hasil PDF surat yang digenerate (Gambar 4.x baru). |
| **Tabel 4.1 (Black Box Testing)** | Tambah baris skenario: "Warga mengunduh surat setelah status disetujui" → Hasil yang diharapkan: "File PDF surat berhasil diunduh dengan nomor surat dan data pemohon yang sesuai." |
| **BAB V 5.2 Saran** | Poin saran e-signature yang sudah ada (poin 2) tetap relevan — jelaskan bahwa penerbitan PDF pada penelitian ini belum menyertakan tanda tangan elektronik resmi, sehingga saran tersebut masih berlaku sebagai pengembangan lanjutan. |

---

## 2. Ajukan Ulang Setelah Ditolak (Phase 03, US-3.4)

| Bagian | Yang perlu ditambahkan |
|---|---|
| **3.5.1 Tabel 3.3** | Tambah baris: *Kelemahan* — "Warga yang pengajuannya ditolak harus mengisi ulang seluruh formulir dari awal." / *Solusi* — "Sistem menyediakan fitur ajukan ulang yang menyalin data pengajuan sebelumnya untuk diperbaiki warga." |
| **3.6.2 Tabel 3.8 (Use Case)** | Tambah use case: "Ajukan Ulang Surat" (Aktor: Warga). |
| **3.6.2 Activity Diagram Verifikasi (Gambar 3.5)** | Tambahkan cabang setelah status "ditolak": warga dapat memilih ajukan ulang, kembali ke alur pengajuan dengan data pra-terisi. |
| **3.6.4 Rancangan Antarmuka Warga** | Tambah catatan pada "Halaman Status dan Riwayat Pengajuan": sertakan tombol "Ajukan Ulang" pada baris berstatus ditolak. |
| **Tabel 4.1 (Black Box Testing)** | Tambah skenario: "Warga mengajukan ulang surat yang sebelumnya ditolak" → Hasil diharapkan: "Form terisi otomatis dari data sebelumnya, pengajuan baru tersimpan dengan nomor_pengajuan baru." |

---

## 3. Akses Publik ke Persyaratan Dokumen (Phase 02, US-2.3)

| Bagian | Yang perlu ditambahkan |
|---|---|
| **3.5.2.4 Kebutuhan Pengguna** | Tambah catatan bahwa halaman persyaratan dokumen dapat diakses oleh pengunjung publik (belum login), berbeda dari halaman lain yang memerlukan autentikasi. |
| **3.6.1 Arsitektur Sistem** | Pada penjelasan Middleware, tambahkan catatan pengecualian: route informasi persyaratan dokumen dikecualikan dari middleware autentikasi. |
| **Tabel 4.1 (Black Box Testing)** | Tambah skenario: "Pengunjung tanpa akun mengakses halaman persyaratan dokumen" → Hasil diharapkan: "Halaman dapat diakses tanpa login, menampilkan seluruh jenis surat dan persyaratannya." |

---

## 4. Lupa Password (Phase 01, US-1.5)

| Bagian | Yang perlu ditambahkan |
|---|---|
| **3.6.2 Tabel 3.8 (Use Case)** | Tambah use case: "Reset Password (Lupa Password)" (Aktor: Warga, Admin). |
| **3.6.4 Rancangan Antarmuka Warga** | Tambah item: "Halaman Lupa Password / Reset Password." |
| **BAB IV 4.1.1** | Tambah subbagian implementasi singkat untuk halaman ini. |
| **Tabel 4.1 (Black Box Testing)** | Tambah skenario: "Pengguna melakukan reset password melalui email" → Hasil diharapkan: "Link reset terkirim, pengguna dapat login dengan password baru setelah reset." |

---

## 5. Status "Diproses" (Phase 04, US-4.4)

Catatan: kolom `status` pada `pengajuan_surat` (Tabel 3.11) sudah mencantumkan enum `diproses`, tapi tidak ada use case/activity diagram yang pernah men-trigger-nya — draf implementasi saat ini melompat langsung dari `diajukan` ke `disetujui`/`ditolak`.

| Bagian | Yang perlu ditambahkan |
|---|---|
| **3.6.2 Activity Diagram Verifikasi (Gambar 3.5)** | Perjelas langkah: begitu admin membuka detail pengajuan, status otomatis berubah menjadi "diproses" sebelum admin memutuskan setuju/tolak. |
| **Tabel 4.1 (Black Box Testing)** | Tambah skenario: "Admin membuka detail pengajuan berstatus diajukan" → Hasil diharapkan: "Status berubah otomatis menjadi diproses." |

*(Alternatif jika tidak ingin menambah kompleksitas: hapus nilai enum `diproses` dari Tabel 3.11 dan sebutkan di BAB V sebagai saran pengembangan. Tapi karena perubahan ini kecil dan langsung mendukung tujuan transparansi status, folding ke Phase 04 lebih disarankan.)*

---

## Ringkasan Prioritas Revisi

| Prioritas | Item | Alasan |
|---|---|---|
| **Tinggi** | Penerbitan Surat Keterangan | Berkaitan langsung dengan klaim cakupan penelitian di 3.2; tanpa ini, objek penelitian belum sepenuhnya terjawab |
| **Sedang** | Ajukan Ulang Setelah Ditolak | Berkaitan langsung dengan rumusan masalah "kunjungan berulang" dari PIECES Economics/Efficiency |
| **Sedang** | Status "Diproses" | Kecil, tapi mengisi celah data model yang sudah ada namun tidak dipakai |
| **Rendah** | Akses Publik Persyaratan Dokumen | Perbaikan UX, tidak mengubah rumusan masalah inti |
| **Rendah** | Lupa Password | Kelengkapan standar, bukan bagian dari rumusan masalah PIECES |

Saran: prioritaskan revisi **Penerbitan Surat** dan **Ajukan Ulang** dulu jika waktu terbatas menjelang sidang, karena keduanya paling terkait langsung dengan rumusan masalah dan objek penelitian yang sudah dituliskan di BAB I dan 3.2.
