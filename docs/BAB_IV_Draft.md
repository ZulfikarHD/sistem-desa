**BAB IV**

**IMPLEMENTASI DAN PENGUJIAN**

---

**4.1 Implementasi Sistem**

Bab ini menguraikan hasil implementasi Sistem Informasi Pelayanan Surat Keterangan Berbasis Web pada Kantor Desa Widodaren berdasarkan rancangan yang telah diuraikan pada Bab III. Implementasi dilaksanakan mengikuti tahapan pengkodean pada metode Prototyping sebagaimana dirincikan pada Tabel 3.2, yaitu melalui sembilan fase pengembangan (Phase 01 s.d. Phase 09) yang masing-masing menghasilkan prototipe fungsional yang dapat diuji dan dievaluasi.

---

**_4.1.1 Lingkungan Implementasi_**

Implementasi dan pengujian sistem dilaksanakan pada lingkungan pengembangan lokal menggunakan Laragon pada sistem operasi Windows. Hal ini selaras dengan keterangan pada Tabel 3.7 (Bab III) yang menyatakan bahwa untuk kebutuhan penelitian, sistem dapat dijalankan pada lingkungan pengembangan lokal tanpa mengeluarkan biaya *hosting*. Jika sistem akan digunakan secara *online* di lingkungan produksi, biaya tambahan berupa *hosting* dan domain akan berlaku sebagaimana telah dicantumkan pada Tabel 3.7.

Spesifikasi perangkat yang digunakan selama pengembangan dan pengujian disajikan pada Tabel 4.1, sedangkan konfigurasi *stack* teknologi yang berhasil diimplementasikan disajikan pada Tabel 4.2.

**Tabel 4.1 Spesifikasi Perangkat Pengembangan**

| **Komponen** | **Spesifikasi yang Digunakan** |
| ------------ | ------------------------------ |
| Sistem Operasi | Windows 10/11 (64-bit) |
| Prosesor | Setara Intel Core i5 atau lebih tinggi |
| RAM | 8 GB atau lebih |
| Penyimpanan | SSD, kapasitas cukup untuk instalasi Laragon, PHP, Node.js, dan data aplikasi |
| *Web Server* Lokal | Laragon *Full* (menyertakan PHP 8.3, MariaDB, Nginx, Node.js) |
| *Code Editor* | Visual Studio Code |
| Peramban Pengujian | Google Chrome (versi terkini), mendukung HTML5, JavaScript, dan akses kamera |

**Tabel 4.2 Konfigurasi *Stack* Teknologi yang Diimplementasikan**

| **Lapisan** | **Teknologi** | **Versi** | **Keterangan** |
| ----------- | ------------- | --------- | -------------- |
| *Backend Framework* | Laravel | ^13.17 | *Routing*, *Eloquent ORM*, validasi, *middleware*, migrasi basis data |
| PHP | PHP | ^8.3 | Bahasa pemrograman *server-side* |
| *Reactive UI* | Livewire | ^4.1 | Komponen UI reaktif berbasis PHP (1 *route* = 1 *component*) |
| *UI Component Library* | Flux UI | ^2.13.1 | Pustaka komponen antarmuka resmi Livewire |
| *Styling* | Tailwind CSS | v4 | *Utility-first* CSS untuk antarmuka responsif |
| Autentikasi | Laravel Fortify | ^1.37.2 | Login, registrasi, *reset password*, 2FA, *passkey* |
| Pembuatan PDF | DomPDF (barryvdh/laravel-dompdf) | ^3.1 | Generasi dokumen PDF dari *template* Blade |
| *QR Code* | BaconQrCode | (transitif via DomPDF) | Generasi gambar *QR code* yang disisipkan ke PDF |
| Basis Data (pengembangan) | SQLite | bawaan PHP | Basis data lokal; tidak memerlukan server terpisah |
| Basis Data (produksi) | MySQL/MariaDB | (kompatibel) | Basis data relasional untuk lingkungan produksi |
| Kontrol Versi | Git + GitHub | — | Pengelolaan versi kode program |

**Cara Menjalankan Aplikasi**

Untuk menjalankan aplikasi pada lingkungan pengembangan lokal dengan Laragon, urutan perintah yang dieksekusi melalui terminal Laragon adalah sebagai berikut.

```bash
# 1. Instal dependensi PHP dan Node.js
composer install && npm install

# 2. Siapkan konfigurasi lingkungan dan kunci aplikasi
cp .env.example .env && php artisan key:generate

# 3. Jalankan migrasi basis data dan isi data awal (seeder)
php artisan migrate --seed

# 4. Bangun aset frontend
npm run build

# 5. Jalankan server pengembangan (atau gunakan fitur site Laragon)
php artisan serve
```

Setelah perintah di atas dijalankan, sistem dapat diakses melalui peramban pada alamat `http://localhost:8000`, atau melalui *virtual host* Laragon yang dikonfigurasi secara otomatis. Akun uji coba yang tersedia setelah menjalankan *seeder* disajikan pada Tabel 4.3 berikut.

**Tabel 4.3 Akun Uji Coba *Default* (hasil `php artisan db:seed`)**

| **Email** | **Kata Sandi** | **Role** | **Keterangan** |
| --------- | -------------- | -------- | -------------- |
| `admin@desa.test` | `password` | admin | Akun admin/petugas desa baku |
| `warga@desa.test` | `password` | warga | Akun warga baku |
| *(5 akun acak)* | `password` | warga | Akun warga tambahan dari *factory* |

---

**_4.1.2 Implementasi Basis Data_**

Implementasi basis data dilaksanakan menggunakan fitur migrasi (*migrations*) Laravel, yang memungkinkan pembuatan dan pengelolaan skema basis data secara terprogram dan dapat diulang di lingkungan mana pun cukup dengan perintah `php artisan migrate`. Pendekatan ini memastikan konsistensi skema antara lingkungan pengembangan dan produksi.

Sebagai hasil implementasi, sistem berhasil merealisasikan seluruh rancangan basis data yang diuraikan pada Subbab 3.6.3, ditambah satu tabel pendukung autentikasi *passkey*. Daftar seluruh tabel yang diimplementasikan disajikan pada Tabel 4.4 berikut.

**Tabel 4.4 Daftar Tabel Basis Data Hasil Implementasi**

| **No** | **Nama Tabel** | **Kategori** | **Keterangan** |
| ------ | -------------- | ------------ | -------------- |
| 1 | `users` | Inti | Akun seluruh pengguna sistem (warga dan admin) |
| 2 | `jenis_surat` | Inti | Master data jenis surat keterangan (*SoftDeletes*) |
| 3 | `jenis_surat_persyaratan` | Inti | Baris persyaratan terstruktur per jenis surat (Phase 09) |
| 4 | `pengajuan_surat` | Inti | Data setiap pengajuan surat oleh warga |
| 5 | `dokumen_persyaratan` | Inti | File KTP/KK/dokumen yang diunggah warga per pengajuan |
| 6 | `log_verifikasi` | Inti | *Audit trail* setiap keputusan verifikasi admin |
| 7 | `notifikasi` | Inti | Notifikasi *in-app* untuk warga (perubahan status) |
| 8 | `surat_terbit` | Inti | Data PDF Bukti Pengambilan + nomor surat + *QR code* |
| 9 | `passkeys` | Pendukung | Kredensial *WebAuthn* untuk login *passkey* (Fortify) |
| 10–14 | *(tabel framework)* | *Framework* | `cache`, `sessions`, `jobs`, `password_reset_tokens`, dll. |

Tabel `jenis_surat_persyaratan` (nomor 3 pada Tabel 4.4) merupakan penambahan yang dihasilkan pada fase pengembangan terakhir (Phase 09 / US-9.1). Tabel ini menyimpan setiap baris persyaratan secara terstruktur dengan atribut `nama`, `cara_pemenuhan` (`unggah` / `bawa_kantor` / `info`), `is_wajib`, dan `urutan`, sehingga admin dapat mendefinisikan persyaratan dengan lebih presisi dibandingkan hanya menuliskan teks bebas.

Perlu dicatat bahwa kolom `persyaratan_dokumen` (teks) pada tabel `jenis_surat` tetap dipertahankan sebagai ringkasan yang digenerate otomatis untuk keperluan pencarian dan kompatibilitas tampilan mundur, namun bukan lagi sumber kebenaran aturan unggah — peran tersebut telah sepenuhnya diambil alih oleh tabel `jenis_surat_persyaratan`.

_Sisipkan Gambar 4.1: Cuplikan tabel `jenis_surat_persyaratan` pada alat perambah basis data (Laragon Database Manager atau DB Browser for SQLite)._

---

**_4.1.3 Implementasi Antarmuka Halaman Publik / Tamu_**

Halaman-halaman pada kelompok ini dapat diakses oleh siapa saja tanpa memerlukan akun atau sesi login, sebagaimana dirancang pada UC-01, UC-02, UC-03, dan UC-04 (Tabel 3.8 Bab III).

**_1. Halaman Beranda (`/`)_**

Halaman beranda merupakan titik masuk utama sistem. Halaman ini menampilkan informasi singkat tentang layanan surat keterangan Desa Widodaren, tombol navigasi menuju halaman login dan registrasi, serta tautan langsung ke halaman persyaratan dokumen. Desain visual menggunakan tema warna hijau hutan (*forest-green*) dan kuning *saffron* yang mencerminkan identitas desa, dengan tipografi *Fraunces* (display) dan *Instrument Sans*.

_Sisipkan Gambar 4.2: Halaman Beranda (`/`)._

**_2. Halaman Persyaratan Dokumen Publik (`/persyaratan-dokumen`)_**

Halaman ini merealisasikan UC-02 dan merupakan salah satu fitur kunci yang menjawab masalah pertama pada analisis PIECES (Information): warga dapat mengetahui persyaratan setiap jenis surat sebelum mendaftar akun ataupun datang ke kantor desa. Halaman menampilkan kartu-kartu jenis surat aktif beserta daftar persyaratan terstruktur yang dilengkapi *badge* cara pemenuhan: **Wajib diunggah**, **Boleh dikosongkan**, **Bawa ke kantor**, atau **Informasi**. Tersedia pula kolom pencarian *live* berdasarkan nama jenis surat.

_Sisipkan Gambar 4.3: Halaman Persyaratan Dokumen Publik (`/persyaratan-dokumen`) — tampilan kartu jenis surat beserta *badge* persyaratan terstruktur._

**_3. Halaman Registrasi (`/register`)_**

Halaman registrasi merealisasikan UC-03. Warga mengisi formulir dengan data: NIK (16 digit), nama lengkap, nomor telepon, alamat, surel (*email*), kata sandi, dan konfirmasi kata sandi. Sistem melakukan validasi otomatis terhadap format NIK (harus 16 digit angka), keunikan NIK dan surel, serta kesesuaian kata sandi. Jika data valid, akun baru tersimpan dengan peran (*role*) `warga` secara otomatis.

_Sisipkan Gambar 4.4: Halaman Registrasi (`/register`) — formulir pendaftaran akun warga._

**_4. Halaman Login (`/login`)_**

Halaman login merealisasikan UC-04. Pengguna memasukkan surel dan kata sandi; sistem mengautentikasi melalui Laravel Fortify lalu mengarahkan secara otomatis ke *dashboard* yang sesuai dengan peran — `/dashboard` untuk warga dan `/admin/dashboard` untuk admin. Fitur "Ingat saya" tersedia untuk mempertahankan sesi lebih lama. Sistem menerapkan *throttle* (pembatasan percobaan login) untuk mencegah serangan *brute force*, serta menampilkan pesan kesalahan yang tidak mengungkapkan *field* mana yang salah demi keamanan (*security through obscurity*).

_Sisipkan Gambar 4.5: Halaman Login (`/login`) — tampilan split: panel brand + formulir._

**_5. Halaman Reset Password_**

Halaman *reset password* merealisasikan UC-07. Alur terdiri dari dua tahap: (a) warga memasukkan surel untuk menerima tautan *reset* melalui surel, dan (b) setelah mengklik tautan, warga mengisi dan mengonfirmasi kata sandi baru. Tautan *reset* berlaku selama 60 menit; setelah melampaui batas waktu tersebut, sistem menampilkan halaman kesalahan dan warga diminta meminta tautan baru.

_Sisipkan Gambar 4.6: Halaman Reset Password — tahap pertama (permintaan tautan) dan tahap kedua (formulir kata sandi baru)._

---

**_4.1.4 Implementasi Antarmuka Warga_**

Halaman-halaman pada kelompok ini hanya dapat diakses oleh pengguna yang sudah login dengan peran `warga`, dilindungi oleh *middleware* `auth`, `verified`, dan `role:warga`.

**_1. Dashboard Warga — Hero Status (`/dashboard`)_**

*Dashboard* warga merealisasikan UC-11 dan dirancang dengan pendekatan *status-first*: pertanyaan utama yang dijawab halaman ini adalah "Sudah sampai mana surat saya?". Halaman menampilkan kartu *hero* berwarna untuk setiap pengajuan aktif (yang belum berstatus `selesai` atau `ditolak`), memuat informasi: jenis surat, *badge* status yang besar dan jelas, penjelasan status dalam bahasa yang mudah dipahami warga, jumlah hari sudah berada dalam status tersebut, dan alur progres bertahap (Diajukan → Diproses → Siap Diambil). Saat pengajuan berstatus `siap_diambil`, jadwal pengambilan beserta label jam kerja yang ditetapkan admin ditampilkan secara mencolok. Saat berstatus `diproses` atau `siap_diambil`, tombol unduh Bukti Pengambilan juga tersedia. Bila tidak ada pengajuan aktif, halaman menampilkan ajakan untuk mengajukan surat baru.

_Sisipkan Gambar 4.7: Dashboard Warga (`/dashboard`) — kartu hero status aktif dengan progres alur, badge status, dan jadwal pengambilan._

**_2. Manajemen Profil (`/settings/profile` dan `/settings/security`)_**

Fitur ini merealisasikan UC-06. Pengguna (baik warga maupun admin) dapat mengakses halaman **Pengaturan** dari menu akun di *sidebar* untuk melihat dan memperbarui data kontak. Halaman **Profil** memungkinkan perubahan nama, nomor telepon, alamat, dan surel; sedangkan NIK dan peran (*role*) hanya ditampilkan dalam mode *read-only* karena tidak dapat diubah sendiri oleh pengguna. Data yang benar sangat penting karena nama dan alamat warga akan tercetak pada dokumen Bukti Pengambilan Berkas yang diterbitkan sistem.

Halaman **Keamanan** (`/settings/security`) memungkinkan pengguna mengganti kata sandi dengan terlebih dahulu mengisi kata sandi saat ini sebagai konfirmasi identitas. Halaman ini juga menyediakan antarmuka untuk pengaturan autentikasi dua faktor (2FA) dan *passkey* yang disediakan oleh Laravel Fortify, meskipun penggunaan fitur tersebut bersifat opsional dalam konteks penelitian ini.

_Sisipkan Gambar 4.8: Halaman Manajemen Profil (`/settings/profile`) — tampilan data pengguna dengan NIK dan role yang tidak dapat diubah._

**_3. Halaman Persyaratan Dokumen Warga (`/persyaratan-dokumen`)_**

Halaman ini merealisasikan UC-08. Warga yang sudah login mengakses rute yang sama dengan halaman persyaratan publik (`/persyaratan-dokumen`), namun sistem mengenali status login dan menampilkan halaman menggunakan tata letak aplikasi lengkap dengan *sidebar* navigasi, alih-alih tata letak publik tanpa autentikasi. Fungsionalitas identik dengan versi publik: menampilkan kartu jenis surat aktif beserta daftar persyaratan terstruktur ber-*badge*, dilengkapi kotak pencarian *live*. Warga dapat menggunakan halaman ini sebagai referensi sebelum mengisi formulir pengajuan.

_Sisipkan Gambar 4.9: Halaman Persyaratan Dokumen Warga (`/persyaratan-dokumen`) — tampilan setelah login menggunakan tata letak aplikasi dengan sidebar navigasi._

**_4. Halaman Form Pengajuan Surat, Unggah Dokumen, dan Validasi Kelengkapan (`/pengajuan-surat`)_**

Halaman ini merealisasikan UC-09, UC-10 (unggah dokumen), serta mekanisme validasi kelengkapan pengajuan. Alur pengisian formulir adalah sebagai berikut.

Warga memilih jenis surat dari *dropdown*; setelah dipilih, sistem secara reaktif memuat daftar persyaratan terstruktur dari tabel `jenis_surat_persyaratan` dan menampilkan setiap baris dengan *badge* cara pemenuhan. Untuk persyaratan bertipe `unggah`, kolom unggah *file* muncul secara dinamis dengan label sesuai nama persyaratan (misalnya "Fotokopi KTP Pemohon", "Slip Gaji"). Format berkas yang diterima adalah JPG, PNG, dan PDF dengan ukuran maksimal 2 MB per berkas. Pratinjau berkas ditampilkan setelah pengunggahan berhasil; warga dapat menghapus dan mengunggah ulang sebelum mengirim. Persyaratan bertipe `bawa_kantor` hanya ditampilkan sebagai informasi tanpa kolom unggah — berkas tersebut cukup disiapkan secara fisik untuk dibawa saat pengambilan. Warga juga mengisi kolom keperluan surat secara bebas.

Saat tombol "Kirim Pengajuan" ditekan, sistem melakukan validasi kelengkapan: hanya persyaratan unggah yang bersifat wajib (`is_wajib = true`) yang memblokir pengiriman jika tidak diisi. Persyaratan unggah opsional (`is_wajib = false`) tidak menahan pengiriman. Pesan kesalahan ditampilkan per *field* dengan menyebutkan nama persyaratan yang belum dipenuhi. Jika semua valid, pengajuan tersimpan dengan status `diajukan` dan nomor pengajuan unik format `PJ-YYYYMMDD-####` diterbitkan secara otomatis.

_Sisipkan Gambar 4.10: Halaman Form Pengajuan Surat (`/pengajuan-surat`) — dropdown jenis surat, daftar badge persyaratan terstruktur, slot unggah file dinamis, dan kolom keperluan._

**_5. Halaman Riwayat Pengajuan (`/riwayat-pengajuan`)_**

Halaman riwayat merealisasikan UC-12. Warga dapat melihat seluruh riwayat pengajuannya dalam bentuk tabel yang dapat difilter berdasarkan status. Setiap baris memuat: nomor pengajuan, jenis surat, tanggal pengajuan, status terkini, dan tautan ke halaman detail. Pada baris berstatus `ditolak`, tersedia tombol "Ajukan Ulang" yang mengaktifkan fitur UC-14.

_Sisipkan Gambar 4.11: Halaman Riwayat Pengajuan (`/riwayat-pengajuan`) — tabel riwayat dengan filter status dan tombol Ajukan Ulang._

**_6. Halaman Detail Pengajuan Warga (`/pengajuan-surat/detail/{id}`)_**

Halaman ini merealisasikan UC-12 dan UC-13. Warga dapat melihat detail lengkap satu pengajuan: nomor pengajuan, jenis surat, keperluan, status terkini, catatan admin (jika pengajuan ditolak), dan riwayat notifikasi yang berkaitan. Akses ke halaman ini dibatasi oleh kepemilikan — warga hanya dapat melihat detail pengajuan miliknya sendiri; akses ke pengajuan milik warga lain akan ditolak oleh sistem. Jika pengajuan berstatus `siap_diambil` atau `selesai`, tombol "Unduh Bukti Pengambilan" dan "Cetak Bukti Pengambilan" tersedia di halaman ini.

_Sisipkan Gambar 4.12: Halaman Detail Pengajuan Warga (`/pengajuan-surat/detail/{id}`) — informasi lengkap pengajuan dan riwayat notifikasi._

**_7. Panel Notifikasi In-App_**

Panel notifikasi merealisasikan UC-12. Ikon lonceng pada *header* navigasi menampilkan *badge* jumlah notifikasi yang belum dibaca. Mengklik ikon membuka panel yang memuat daftar notifikasi terbaru beserta pesan dan waktu penerimaan. Mengklik salah satu notifikasi menandai notifikasi tersebut sebagai "dibaca" dan mengarahkan warga ke halaman detail pengajuan yang bersangkutan. Notifikasi dikirim secara otomatis oleh sistem setiap kali status pengajuan berubah (setujui → `diproses`, tolak → `ditolak`, tetapkan jadwal → `siap_diambil`, *scan* QR → `selesai`).

_Sisipkan Gambar 4.13: Panel Notifikasi In-App — ikon lonceng dengan badge jumlah belum dibaca dan daftar notifikasi._

**_8. Fitur Ajukan Ulang (`/pengajuan-surat/ajukan-ulang/{id}`)_**

Fitur ini merealisasikan UC-14. Warga yang pengajuannya ditolak dapat mengajukan ulang tanpa harus mengisi formulir dari awal. Saat tombol "Ajukan Ulang" diklik dari halaman Riwayat Pengajuan, sistem menampilkan formulir pengajuan baru yang telah dipra-isi (*pre-fill*) dengan jenis surat dan keperluan yang sama, disertai kotak peringatan yang menampilkan alasan penolakan dari admin dan nomor pengajuan sebelumnya. Warga cukup mengunggah ulang dokumen yang diperbaiki dan mengirimkan pengajuan baru. Nomor pengajuan baru akan diterbitkan secara otomatis; pengajuan lama tetap tersimpan di riwayat sebagai rekam jejak.

_Sisipkan Gambar 4.14: Halaman Ajukan Ulang (`/pengajuan-surat/ajukan-ulang/{id}`) — formulir pra-isi dengan kotak peringatan alasan penolakan._

**_9. Unduh / Cetak Bukti Pengambilan_**

Fitur ini merealisasikan UC-13 dan UC-22 (sebagian). Setelah admin menyetujui pengajuan (status → `diproses`) dan menetapkan jadwal pengambilan (status → `siap_diambil`), warga dapat mengunduh **Bukti Pengambilan Berkas** dalam format PDF melalui tombol "Unduh Bukti Pengambilan" atau "Cetak Bukti Pengambilan" yang tersedia di *Dashboard* Warga maupun halaman Detail Pengajuan.

Perlu diperjelas bahwa dokumen PDF yang dihasilkan adalah **Bukti Pengambilan Berkas** — bukan surat keterangan resmi itu sendiri. Dokumen ini berfungsi sebagai tanda pengambilan digital yang berisi: identitas pemohon (nama, NIK), jenis surat yang diajukan, nomor surat resmi, tanggal pengambilan beserta label jam kerja, dan *QR code* sekali pakai yang dipindai petugas saat warga datang mengambil surat fisik. Surat keterangan resmi (Surat Keterangan Domisili, Kelahiran/Kematian, atau Tidak Mampu) tetap disiapkan secara fisik di kantor desa dan diserahkan kepada warga setelah pemindaian QR berhasil dilakukan.

_Sisipkan Gambar 4.15: Contoh Bukti Pengambilan Berkas (PDF) yang dihasilkan sistem — memuat data pemohon, nomor surat resmi, jadwal pengambilan, dan QR code._

---

**_4.1.5 Implementasi Antarmuka Admin / Petugas Desa_**

Halaman-halaman pada kelompok ini hanya dapat diakses oleh pengguna yang sudah login dengan peran `admin`, dilindungi oleh *middleware* `auth`, `verified`, dan `role:admin`. Semua rute pada kelompok ini menggunakan prefiks `/admin`.

**_1. Dashboard Admin — Kartu Aging & Antrean Mendesak (`/admin/dashboard`)_**

*Dashboard* admin merealisasikan UC-15. Tampilan dirancang untuk menjawab pertanyaan operasional: "Apakah ada pengajuan yang sudah terlalu lama tidak ditangani?". Halaman memuat:

- **Empat kartu status** dengan indikator warna dinamis: "Menunggu Verifikasi" (`diajukan`), "Sedang Diproses" (`diproses`), "Siap Diambil" (`siap_diambil`), dan "Selesai Bulan Ini" (`selesai`). Setiap kartu menampilkan jumlah pengajuan dan sub-label tambahan (misal: "3 tertunda > 3 hari"). Warna kartu berubah dari netral → amber (peringatan) → merah (mendesak) bergantung pada jumlah hari pengajuan berada di status tersebut, menggunakan ambang batas yang ditetapkan pada konstanta komponen.
- **Antrean mendesak** (maksimum 5 baris): daftar pengajuan yang sudah paling lama menunggu tindak lanjut, dilengkapi pintasan "Tangani" menuju halaman verifikasi atau detail terkait.
- **Tabel pengajuan aktif** (maksimum 7 baris): semua pengajuan yang belum terminal (`selesai`/`ditolak`), diurutkan dari yang terlama di statusnya.

_Sisipkan Gambar 4.16: Dashboard Admin (`/admin/dashboard`) — empat kartu aging berindikator warna, antrean mendesak, dan tabel pengajuan aktif._

**_2. Pengaturan Desa (`/admin/pengaturan-desa`)_**

Halaman pengaturan desa memuat formulir identitas resmi Kantor Desa Widodaren yang digunakan sebagai kop pada dokumen Bukti Pengambilan Berkas PDF. Data yang dapat diisi meliputi nama desa, nama kabupaten/kecamatan, kode desa (digunakan pada format nomor surat), dan nama pejabat penandatangan. Admin wajib mengisi halaman ini terlebih dahulu sebelum sistem dapat menerbitkan PDF yang valid.

_Sisipkan Gambar 4.17: Halaman Pengaturan Desa (`/admin/pengaturan-desa`) — formulir identitas desa untuk kop PDF._

**_3. Halaman Daftar Jenis Surat (`/admin/jenis-surat`)_**

Halaman ini merealisasikan UC-16. Menampilkan daftar jenis surat yang aktif disertai fitur pencarian *live*. Admin dapat melakukan tindakan: **Tambah** (menuju halaman form tambah), **Ubah** (menuju halaman form ubah), dan **Arsipkan** (*soft delete*). Tersedia pula tombol "Tampilkan Arsip" untuk membuka daftar jenis surat yang sudah diarsipkan, di mana admin dapat melakukan **Pulihkan** (mengembalikan ke daftar aktif) atau **Hapus Permanen** (*hard delete*; gagal jika masih ada pengajuan yang mengacu jenis surat tersebut).

_Sisipkan Gambar 4.18: Halaman Daftar Jenis Surat (`/admin/jenis-surat`) — daftar aktif dengan aksi Tambah/Ubah/Arsipkan dan panel arsip._

**_4. Halaman Form Jenis Surat — Tambah dan Ubah_**

Halaman ini merealisasikan US-9.1 dan US-9.2 (Phase 09). Admin mengisi nama surat (wajib, unik) dan deskripsi (opsional), kemudian menambahkan baris-baris persyaratan terstruktur menggunakan editor yang memungkinkan penambahan, penghapusan, dan pengurutan ulang baris. Setiap baris memiliki:

- **Nama syarat**: teks bebas (misal: "Fotokopi KTP Pemohon", "Surat Pengantar RT/RW").
- **Cara memenuhi**: *dropdown* dengan tiga pilihan — "Unggah di aplikasi", "Bawa ke kantor desa", atau "Tidak perlu file (informasi)".
- **Wajib / Boleh dikosongkan**: muncul jika cara memenuhi = "Unggah di aplikasi".

Halaman ini juga menampilkan **pratinjau *badge*** secara *live* yang menunjukkan tampilan yang sama seperti yang akan dilihat warga di halaman persyaratan dokumen. Tersedia pula templat cepat "KTP + KK + Pengantar RT" yang dapat diterapkan dengan satu klik untuk mempercepat pengisian jenis surat umum.

_Sisipkan Gambar 4.19: Halaman Form Jenis Surat — editor baris persyaratan terstruktur dengan pratinjau badge dan template cepat._

**_5. Halaman Daftar Pengajuan Surat (`/admin/verifikasi`)_**

Halaman ini merealisasikan UC-17 (tampilan daftar). Menampilkan tabel semua pengajuan dengan filter status (default: `diajukan`) yang dapat diubah oleh admin. Setiap baris memuat: nomor pengajuan, nama warga, jenis surat, tanggal pengajuan, dan status terkini. Mengklik baris membuka halaman detail verifikasi. Nama halaman dan menu navigasi menyebut "Daftar Pengajuan Surat" (bukan "Verifikasi Pengajuan") untuk mencerminkan penggunaan sehari-hari petugas desa.

_Sisipkan Gambar 4.20: Halaman Daftar Pengajuan Surat (`/admin/verifikasi`) — tabel dengan filter status, default tampilan Diajukan._

**_6. Halaman Detail Verifikasi (`/admin/verifikasi/{id}`)_**

Halaman ini merealisasikan UC-17 (aksi verifikasi). Admin dapat melihat:

- Data lengkap pengajuan: nama warga, NIK, jenis surat, keperluan, nomor pengajuan.
- **Dokumen diunggah secara *online***: pratinjau *inline* atau tautan unduh untuk setiap berkas yang diunggah warga; berkas yang boleh dikosongkan dilengkapi *badge* "Tidak diunggah" jika kosong.
- **Daftar periksa fisik** (US-9.5): daftar persyaratan bertipe `bawa_kantor` yang harus diperiksa saat warga datang ke kantor.
- Tombol **Setujui** dan **Tolak**: hanya aktif jika status pengajuan masih `diajukan`. Mengklik "Tolak" membuka modal yang mewajibkan admin mengisi alasan penolakan.

Saat admin mengklik "Setujui", sistem secara atomik (menggunakan `DB::transaction` + `lockForUpdate`) menjalankan: mengubah status → `diproses`, menerbitkan nomor surat resmi, membuat *QR token*, menghasilkan PDF Bukti Pengambilan, mencatat log verifikasi, dan mengirim notifikasi ke warga — semuanya dalam satu transaksi.

_Sisipkan Gambar 4.21: Halaman Detail Verifikasi (`/admin/verifikasi/{id}`) — data pengajuan, pratinjau dokumen unggahan, daftar periksa fisik, dan tombol Setujui/Tolak._

**_7. Halaman Surat Diproses (`/admin/surat-diproses`)_**

Halaman ini merealisasikan UC-18. Menampilkan daftar semua pengajuan yang berstatus `diproses` atau `siap_diambil`, memuat informasi: nomor surat, nama warga, jenis surat, tanggal terbit PDF, dan status. Admin dapat mengklik baris untuk membuka halaman detail penetapan jadwal.

_Sisipkan Gambar 4.22: Halaman Surat Diproses (`/admin/surat-diproses`) — daftar surat berstatus Diproses dan Siap Diambil._

**_8. Halaman Detail Surat Diproses & Tetapkan Jadwal (`/admin/surat-diproses/{id}`)_**

Halaman ini merealisasikan UC-19 dan UC-18. Admin dapat:

- Melihat data pengajuan dan melakukan pratinjau atau unduh PDF Bukti Pengambilan surat.
- Memilih **tanggal pengambilan** melalui *date picker*; sistem secara otomatis menolak tanggal yang sudah lampau, hari Sabtu/Minggu, dan hari libur nasional yang dikonfigurasi.
- Melihat **pratinjau label jam kerja** secara *live* saat tanggal dipilih: Senin–Kamis 08.00–16.00 WIB atau Jumat 08.00–16.30 WIB.
- Mengklik tombol "Siap Diambil" (hanya aktif jika tanggal valid) untuk mengubah status pengajuan → `siap_diambil`, menyimpan jadwal, dan mengirim notifikasi ke warga yang mencantumkan jenis surat, nomor surat, tanggal, dan jam kerja.

_Sisipkan Gambar 4.23: Halaman Detail Surat Diproses (`/admin/surat-diproses/{id}`) — date picker tanggal pengambilan, pratinjau label jam kerja otomatis, dan tombol Siap Diambil._

**_9. Halaman Scan QR Pengambilan (`/admin/scan-qr-pengambilan`)_**

Halaman ini merealisasikan UC-20. Saat warga datang ke kantor desa untuk mengambil surat dan menunjukkan PDF Bukti Pengambilan dengan *QR code*, admin membuka halaman ini dan memilih salah satu dari dua metode pemindaian:

- **Kamera**: *browser* meminta izin kamera; admin mengarahkan kamera ke QR pada layar atau cetakan warga.
- ***Input* token manual**: admin menempel atau mengetikkan token QR dari PDF.

Setelah token tervalidasi, sistem memeriksa bahwa: (a) token dikenali, (b) status pengajuan `siap_diambil`, dan (c) QR belum pernah dipakai sebelumnya (`qr_status = valid`). Jika ketiga syarat terpenuhi, sistem menandai QR sebagai `invalid` (permanen), mencatat waktu dan admin yang melakukan *scan*, mengubah status pengajuan → `selesai`, dan mengirim notifikasi ke warga. Setelah itu, admin menyerahkan surat fisik kepada warga. Setiap kondisi kesalahan (token tidak dikenal, status belum siap, QR sudah dipakai) menampilkan pesan kesalahan yang spesifik.

_Sisipkan Gambar 4.24: Halaman Scan QR Pengambilan (`/admin/scan-qr-pengambilan`) — antarmuka kamera dan input token manual, beserta tampilan konfirmasi berhasil._

**_10. Halaman Rekap Pengajuan (`/admin/rekap-pengajuan`)_**

Halaman ini merealisasikan UC-21. Menampilkan ringkasan jumlah pengajuan per status di bagian atas (dihitung tanpa mempertimbangkan filter status agar selalu menampilkan total keseluruhan), diikuti tabel rekap yang dapat difilter secara multi-kriteria: jenis surat, status, tanggal dari, dan tanggal sampai. Filter divalidasi — tanggal sampai tidak boleh lebih awal dari tanggal dari. Tombol "Ekspor CSV" menghasilkan berkas CSV dengan pengkodean UTF-8 BOM untuk memastikan kompatibilitas dengan Microsoft Excel (karakter khusus Bahasa Indonesia terbaca dengan benar). Nama berkas mencantumkan *timestamp* untuk kemudahan pengarsipan.

_Sisipkan Gambar 4.25: Halaman Rekap Pengajuan (`/admin/rekap-pengajuan`) — ringkasan per status, filter multi-kriteria, dan tombol Ekspor CSV._

**_11. Halaman Detail Rekap & Timeline Proses (`/admin/rekap-pengajuan/{id}`)_**

Halaman ini merealisasikan UC-21 (detail) dan menjadi fitur lanjutan untuk keperluan audit. Admin dapat melihat kronologi lengkap proses satu pengajuan dari awal hingga akhir dalam format *timeline*, memuat poin-poin berikut.

1. **Pengajuan Dibuat** — waktu diambil dari `pengajuan_surat.created_at`.
2. **Disetujui & Diproses** (jika berlaku) — waktu dari `log_verifikasi.created_at`, disertai nama admin yang menyetujui dan nomor surat resmi.
3. **Siap Diambil** (jika berlaku) — waktu dari `surat_terbit.siap_diambil_at`, disertai jadwal dan label jam kerja.
4. **Selesai** (jika berlaku) — waktu dari `surat_terbit.qr_digunakan_at`, disertai nama admin yang melakukan *scan*.
5. Atau: **Ditolak** — waktu dari `log_verifikasi.created_at`, disertai nama admin dan alasan penolakan.

Seluruh waktu ditampilkan dalam zona waktu WIB (Asia/Jakarta). Tombol unduh PDF tersedia jika berkas masih ada di penyimpanan.

_Sisipkan Gambar 4.26: Halaman Detail Rekap & Timeline Proses (`/admin/rekap-pengajuan/{id}`) — tampilan kronologi lengkap proses pengajuan dengan nama aktor dan waktu WIB._

**_12. Manajemen Profil Admin (`/settings/profile` dan `/settings/security`)_**

Fitur ini merealisasikan UC-06 untuk pengguna berperan admin. Fungsionalitas identik dengan Manajemen Profil Warga (Subbab 4.1.4 nomor 2): admin dapat memperbarui nama, nomor telepon, alamat, dan surel melalui halaman **Profil**, serta mengganti kata sandi melalui halaman **Keamanan**. NIK dan peran (*role*) hanya ditampilkan dalam mode *read-only*. Halaman pengaturan ini bersifat bersama (*shared*) antara warga dan admin — tidak ada perbedaan antarmuka berdasarkan peran, karena fungsinya memang sama.

_Sisipkan Gambar 4.27: Halaman Manajemen Profil Admin (`/settings/profile`) — identik dengan halaman profil warga, dengan role Admin yang tampil dalam mode read-only._

---

**_4.1.6 Implementasi Fitur Otomatis Sistem_**

Bagian ini menguraikan fitur-fitur yang berjalan secara otomatis tanpa interaksi langsung dari pengguna, sebagaimana dirancang pada UC-22, UC-23, dan UC-24 (Tabel 3.8 Bab III).

**_1. Generasi PDF Bukti Pengambilan Berkas_**

Saat admin mengklik "Setujui" pada halaman Detail Verifikasi, sistem secara otomatis menghasilkan dokumen PDF Bukti Pengambilan Berkas menggunakan pustaka **DomPDF (barryvdh/laravel-dompdf v3.1)**. PDF dirender dari *template* Blade (`resources/views/pdf/surat/bukti-pengambilan.blade.php`) yang mengambil data identitas desa dari tabel `pengaturan_desa` (diisi admin melalui halaman Pengaturan Desa). Dokumen ini memuat: kop surat desa, nomor surat resmi, data pemohon (nama, NIK, alamat, jenis surat, keperluan, tanggal terbit), dan *QR code* sekali pakai. PDF kemudian disimpan ke *disk* `local` (privat, tidak dapat diakses langsung melalui URL publik) dan *path*-nya dicatat pada tabel `surat_terbit`. Ketika jadwal pengambilan ditetapkan admin (status → `siap_diambil`), PDF di-*generate* ulang dengan menambahkan informasi tanggal dan jam kerja, menggunakan *QR token* yang sama tanpa menerbitkan *token* baru.

Pemilihan DomPDF didasarkan pada kemampuannya merender HTML/CSS menjadi PDF langsung dari *template* Blade yang sudah digunakan untuk tampilan antarmuka, sehingga konsistensi desain terjaga dan tidak memerlukan *library* terpisah.

**_2. Penerbitan Nomor Surat Resmi Otomatis_**

Setiap pengajuan yang disetujui memperoleh nomor surat resmi dengan format administrasi desa: `470/{urut}/DS-WDN/{romawi}/{tahun}`, di mana:

- `470` adalah kode klasifikasi administrasi kependudukan desa,
- `{urut}` adalah nomor urut berurutan dimulai dari 1 setiap awal tahun baru,
- `DS-WDN` adalah kode singkatan Desa Widodaren (dikonfigurasi pada Pengaturan Desa),
- `{romawi}` adalah bulan terbit dalam angka Romawi,
- `{tahun}` adalah tahun empat digit.

Contoh: `470/23/DS-WDN/VIII/2026` berarti surat ke-23 yang diterbitkan pada bulan Agustus 2026.

Untuk memastikan nomor bersifat unik dan berurutan tanpa kondisi balapan (*race condition*) saat beberapa admin menyetujui pengajuan bersamaan, penerbitan nomor dilakukan di dalam blok `Cache::lock` + `DB::transaction` + `lockForUpdate`. Nomor surat tercatat pada kolom `nomor_surat` di tabel `surat_terbit` dengan batasan UNIQUE.

**_3. QR Code Sekali Pakai_**

Bersamaan dengan pembuatan PDF, sistem menghasilkan *token* QR berupa *string* acak sepanjang 64 karakter (*opaque*) menggunakan `Str::random(64)`. *Token* ini bersifat **sekali pakai** dengan mekanisme perlindungan berikut.

- *Token* disimpan pada kolom `qr_token` di tabel `surat_terbit` dengan nilai awal `qr_status = valid`.
- *QR code* digenerate menggunakan **BaconQrCode** (dependensi transitif DomPDF) dan disisipkan ke dalam PDF.
- Saat admin memindai QR, sistem melakukan `UPDATE ... WHERE qr_status = 'valid'` secara kondisional — jika `qr_status` sudah `invalid`, pembaruan tidak berdampak dan sistem menolak *scan* tersebut.
- Setelah *scan* pertama berhasil, `qr_status` diubah menjadi `invalid` secara permanen, waktu *scan* (`qr_digunakan_at`) dan identitas admin pemindai (`qr_digunakan_oleh`) dicatat. Pemindaian berikutnya oleh admin mana pun akan selalu ditolak.

Pendekatan ini memastikan setiap proses pengambilan surat tercatat secara digital dan tidak dapat dimanipulasi.

**_4. Notifikasi In-App Otomatis_**

Sistem mengirimkan notifikasi *in-app* secara otomatis ke warga setiap kali status pengajuan berubah. Notifikasi disimpan pada tabel `notifikasi` secara langsung (*insert* sinkron dalam satu transaksi, bukan melalui antrean *queue*). Peristiwa yang memicu notifikasi beserta pesan yang dikirimkan adalah sebagai berikut.

| **Pemicu** | **Pesan Notifikasi** |
| ---------- | -------------------- |
| Admin menyetujui pengajuan (→ `diproses`) | "Pengajuan [jenis surat] Anda sedang diproses. Nomor surat: [nomor surat]." |
| Admin menolak pengajuan (→ `ditolak`) | "Pengajuan [jenis surat] Anda ditolak. Alasan: [catatan admin]." |
| Admin menetapkan jadwal (→ `siap_diambil`) | "Surat [jenis surat] Anda siap diambil pada [tanggal], [jam kerja]." |
| Admin *scan* QR (→ `selesai`) | "Pengambilan surat [jenis surat] Anda telah dikonfirmasi. Terima kasih." |

Notifikasi ditampilkan pada panel lonceng di *header* dan dapat diakses dari halaman riwayat pengajuan. Status baca setiap notifikasi disimpan pada kolom `status_baca` (`belum` / `dibaca`).

---

**4.2 Pengujian Sistem**

**_4.2.1 Rencana Pengujian_**

Pengujian sistem dilaksanakan menggunakan metode **Black Box Testing**, yaitu metode pengujian fungsional yang menguji perilaku antarmuka sistem dari sudut pandang pengguna tanpa memeriksa struktur kode program secara internal. Setiap skenario pengujian diturunkan dari *use case* yang didefinisikan pada Tabel 3.8 (Bab III), mulai UC-01 hingga UC-24, serta sejumlah kasus batas (*edge case*) dan kondisi kesalahan yang relevan.

Pengujian dilaksanakan pada lingkungan pengembangan lokal menggunakan data uji dari *seeder* yang telah disiapkan (Tabel 4.3) serta data yang dibuat secara manual selama sesi pengujian. Skenario pengujian dikelompokkan menjadi sembilan kelompok berdasarkan modul fungsional untuk memudahkan penelusuran dan pelaporan hasil.

---

**_4.2.2 Skenario dan Hasil Pengujian Black Box Testing_**

**Kelompok A — Autentikasi & Manajemen Profil (UC-01 s.d. UC-07)**

**Tabel 4.5 Hasil Pengujian Black Box — Kelompok A: Autentikasi & Manajemen Profil**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| A-01 | Mengakses halaman beranda tanpa login | — | Halaman beranda tampil, tersedia tombol Masuk dan Daftar | Sesuai harapan |
| A-02 | Registrasi akun warga dengan data lengkap dan valid | NIK valid 16 digit, email unik, password cocok | Akun tersimpan dengan role `warga`; diarahkan ke halaman login dengan pesan sukses | Sesuai harapan |
| A-03 | Registrasi dengan NIK kurang dari 16 digit | NIK: `1234` | Sistem menampilkan pesan validasi "NIK harus 16 digit" | Sesuai harapan |
| A-04 | Registrasi dengan NIK yang sudah terdaftar | NIK duplikat | Sistem menampilkan pesan error NIK duplikat | Sesuai harapan |
| A-05 | Registrasi dengan email yang sudah terdaftar | Email duplikat | Sistem menampilkan pesan error email duplikat | Sesuai harapan |
| A-06 | Registrasi dengan konfirmasi password tidak cocok | Password dan konfirmasi berbeda | Sistem menampilkan pesan validasi konfirmasi password | Sesuai harapan |
| A-07 | Login dengan email dan password benar sebagai warga | Email + password valid, role warga | Berhasil masuk, diarahkan ke `/dashboard` | Sesuai harapan |
| A-08 | Login dengan email dan password benar sebagai admin | Email + password valid, role admin | Berhasil masuk, diarahkan ke `/admin/dashboard` | Sesuai harapan |
| A-09 | Login dengan password salah | Password tidak sesuai | Pesan kesalahan umum (tidak menyebut *field* mana yang salah) | Sesuai harapan |
| A-10 | Terlalu banyak percobaan login gagal | Lebih dari 5 percobaan berturut-turut | Sistem membatasi login sementara (*throttle*) | Sesuai harapan |
| A-11 | Reset password dengan email terdaftar | Email valid | Email tautan *reset* terkirim; pengguna dapat menetapkan password baru | Sesuai harapan |
| A-12 | Mengklik tautan *reset* yang sudah kedaluarsa (> 60 menit) | Tautan lama | Halaman menampilkan error "tautan tidak valid"; pengguna diminta meminta tautan baru | Sesuai harapan |
| A-13 | Memperbarui profil (nama, nomor telepon, alamat) | Data baru valid | Profil tersimpan; perubahan tampil di halaman profil | Sesuai harapan |
| A-14 | Mencoba mengubah NIK melalui halaman profil | NIK diubah | NIK tidak dapat diubah; *field* bersifat *read-only* | Sesuai harapan |
| A-15 | Logout | Klik tombol Keluar | Sesi diakhiri; pengguna diarahkan ke halaman beranda | Sesuai harapan |

---

**Kelompok B — Jenis Surat & Persyaratan Terstruktur (UC-16)**

**Tabel 4.6 Hasil Pengujian Black Box — Kelompok B: Jenis Surat & Persyaratan Terstruktur**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| B-01 | Admin menambah jenis surat baru dengan ≥1 baris persyaratan | Nama unik + 2 baris (1 unggah wajib, 1 bawa kantor) | Jenis surat + baris persyaratan tersimpan; tampil di daftar | Sesuai harapan |
| B-02 | Tambah jenis surat dengan nama duplikat | Nama yang sudah ada | Pesan error "nama sudah digunakan" | Sesuai harapan |
| B-03 | Tambah jenis surat tanpa baris persyaratan | Tidak ada baris | Pesan error "minimal satu persyaratan harus ditambahkan" | Sesuai harapan |
| B-04 | Admin mengubah baris persyaratan (cara memenuhi dari unggah → bawa kantor) | Edit baris yang ada | Perubahan tersimpan; ringkasan `persyaratan_dokumen` di-*generate* ulang | Sesuai harapan |
| B-05 | Admin mengarsipkan jenis surat aktif | Klik Arsipkan | Jenis surat tidak muncul di daftar aktif dan tidak bisa dipilih warga; tersimpan di arsip | Sesuai harapan |
| B-06 | Admin memulihkan jenis surat dari arsip | Klik Pulihkan | Jenis surat kembali aktif dan bisa dipilih warga | Sesuai harapan |
| B-07 | Admin menghapus permanen jenis surat yang tidak punya pengajuan | Klik Hapus Permanen di arsip | Jenis surat dan seluruh baris persyaratannya terhapus permanen | Sesuai harapan |
| B-08 | Admin menghapus permanen jenis surat yang masih punya pengajuan terkait | Klik Hapus Permanen | Sistem menolak; menampilkan pesan error "ada pengajuan yang mengacu jenis surat ini" | Sesuai harapan |

---

**Kelompok C — Pengajuan Surat, Unggah Dokumen, dan Validasi Kelengkapan (UC-09, UC-10)**

**Tabel 4.7 Hasil Pengujian Black Box — Kelompok C: Pengajuan Surat, Unggah Dokumen & Validasi**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| C-01 | Warga memilih jenis surat pada form pengajuan | Pilih jenis surat dari *dropdown* | Daftar persyaratan terstruktur muncul dengan *badge* cara pemenuhan; *slot* unggah muncul hanya untuk persyaratan bertipe "unggah" | Sesuai harapan |
| C-02 | Pengajuan lengkap dengan semua dokumen wajib terisi | Semua syarat unggah wajib terisi + keperluan diisi | Data pengajuan tersimpan dengan status `diajukan`; nomor pengajuan format `PJ-YYYYMMDD-####` diterbitkan; konfirmasi tampil | Sesuai harapan |
| C-03 | Pengajuan dengan dokumen wajib tidak diunggah | Syarat unggah wajib dibiarkan kosong | Sistem menampilkan pesan "Dokumen [nama syarat] wajib diunggah" dan tidak menyimpan pengajuan | Sesuai harapan |
| C-04 | Pengajuan dengan syarat unggah opsional yang dibiarkan kosong | Syarat `is_wajib = false` tidak diisi | Pengajuan berhasil disimpan; tidak ada pesan error untuk syarat opsional | Sesuai harapan |
| C-05 | Unggah berkas dengan format tidak didukung (misal `.exe`) | Berkas `.exe` | Sistem menolak berkas; menampilkan pesan error format tidak valid | Sesuai harapan |
| C-06 | Unggah berkas dengan ukuran melebihi 2 MB | Berkas > 2 MB | Sistem menolak berkas; menampilkan pesan error ukuran melebihi batas | Sesuai harapan |
| C-07 | Mengganti jenis surat setelah berkas sudah diunggah | Ubah *dropdown* jenis surat | Berkas yang sudah diunggah dihapus otomatis; pengguna harus mengunggah ulang sesuai persyaratan jenis surat baru | Sesuai harapan |
| C-08 | Pengajuan tanpa mengisi keperluan surat | *Field* keperluan kosong | Sistem menampilkan pesan validasi "keperluan wajib diisi" | Sesuai harapan |
| C-09 | Pengunjung (tanpa login) mencoba mengakses form pengajuan | Akses langsung ke `/pengajuan-surat` | Sistem mengalihkan ke halaman login | Sesuai harapan |

---

**Kelompok D — Verifikasi Pengajuan oleh Admin (UC-17)**

**Tabel 4.8 Hasil Pengujian Black Box — Kelompok D: Verifikasi Pengajuan**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| D-01 | Admin menyetujui pengajuan berstatus `diajukan` | Klik Setujui | Status berubah → `diproses`; PDF Bukti Pengambilan di-*generate*; nomor surat resmi diterbitkan; *QR token* dibuat; log verifikasi dicatat; notifikasi terkirim ke warga | Sesuai harapan |
| D-02 | Admin menolak pengajuan dengan mengisi alasan | Klik Tolak + isi alasan | Status berubah → `ditolak`; alasan tersimpan pada `catatan_admin`; log verifikasi dicatat; notifikasi berisi alasan terkirim ke warga | Sesuai harapan |
| D-03 | Admin mencoba menolak tanpa mengisi alasan | Klik Tolak, alasan dikosongkan | Sistem menampilkan pesan error "alasan penolakan wajib diisi"; pengajuan tidak berubah | Sesuai harapan |
| D-04 | Admin membuka detail pengajuan yang sudah berstatus `diproses` | Buka halaman detail | Tombol Setujui dan Tolak tidak aktif (`canVerify()` = *false*) | Sesuai harapan |
| D-05 | Admin melihat pratinjau *inline* dokumen yang diunggah warga | Klik ikon pratinjau | Berkas terbuka secara *inline* (gambar) atau diunduh (PDF); hanya dapat diakses admin via *route* aman | Sesuai harapan |
| D-06 | Warga mencoba mengakses *route* unduh dokumen admin | Akses `/admin/verifikasi/dokumen/{id}` sebagai warga | Sistem mengembalikan HTTP 403 | Sesuai harapan |

---

**Kelompok E — Proses Surat, Jadwal Pengambilan & Scan QR (UC-18, UC-19, UC-20)**

**Tabel 4.9 Hasil Pengujian Black Box — Kelompok E: Proses Surat, Jadwal Pengambilan & Scan QR**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| E-01 | Admin menetapkan tanggal pengambilan hari kerja (Senin–Kamis) | Pilih tanggal Senin | Label jam kerja "Senin–Kamis 08.00–16.00 WIB" muncul; tombol Siap Diambil aktif | Sesuai harapan |
| E-02 | Admin menetapkan tanggal pengambilan hari Jumat | Pilih tanggal Jumat | Label jam kerja "Jumat 08.00–16.30 WIB" muncul | Sesuai harapan |
| E-03 | Admin memilih tanggal Sabtu atau Minggu | Pilih Sabtu/Minggu | Sistem menampilkan pesan error "bukan hari kerja"; tombol Siap Diambil tidak aktif | Sesuai harapan |
| E-04 | Admin memilih tanggal yang sudah lampau | Tanggal kemarin | Sistem menampilkan pesan error "tanggal tidak valid" | Sesuai harapan |
| E-05 | Admin memilih tanggal hari libur nasional | Tanggal libur yang terdaftar | Sistem menampilkan pesan error "hari libur nasional" | Sesuai harapan |
| E-06 | Admin mengklik Siap Diambil dengan tanggal valid | Tanggal hari kerja valid | Status berubah → `siap_diambil`; jadwal tersimpan; notifikasi (jenis surat, nomor, tanggal, jam kerja) terkirim ke warga; PDF di-*generate* ulang dengan jadwal | Sesuai harapan |
| E-07 | Admin *scan* QR valid milik surat berstatus `siap_diambil` | *Token* QR surat yang siap diambil | Status → `selesai`; `qr_status` = `invalid`; waktu *scan* dan admin dicatat; notifikasi selesai terkirim | Sesuai harapan |
| E-08 | Admin *scan* QR yang sudah pernah dipakai (status `invalid`) | *Token* yang sudah di-*scan* | Sistem menolak; menampilkan pesan "QR sudah digunakan / tidak valid" | Sesuai harapan |
| E-09 | Admin *scan* QR tidak dikenal (*token* sembarang) | *Token* acak tidak ada di *database* | Sistem menolak; menampilkan pesan "QR tidak valid" | Sesuai harapan |
| E-10 | Admin *scan* QR surat yang belum berstatus `siap_diambil` | *Token* surat berstatus `diproses` | Sistem menolak; menampilkan pesan "Surat belum siap diambil" | Sesuai harapan |

---

**Kelompok F — Dashboard (UC-11, UC-15)**

**Tabel 4.10 Hasil Pengujian Black Box — Kelompok F: Dashboard**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| F-01 | Warga dengan pengajuan aktif membuka *dashboard* | Login warga + ada pengajuan aktif | Kartu *hero* status tampil dengan *badge* status yang sesuai, progres alur, dan penjelasan status | Sesuai harapan |
| F-02 | Warga tanpa pengajuan aktif membuka *dashboard* | Login warga + tidak ada pengajuan aktif | Halaman menampilkan *empty state* dengan tombol "Ajukan Surat Sekarang" | Sesuai harapan |
| F-03 | Admin membuka *dashboard* dengan pengajuan `diajukan` yang sudah > 7 hari | Ada pengajuan lampau > 7 hari | Kartu "Menunggu Verifikasi" berwarna merah (mendesak); muncul di antrean mendesak | Sesuai harapan |
| F-04 | Admin membuka *dashboard* dengan semua pengajuan aktif = 0 | Tidak ada pengajuan aktif | Semua kartu menampilkan angka 0 dengan warna netral; tidak ada antrean mendesak | Sesuai harapan |

---

**Kelompok G — Notifikasi & Riwayat Pengajuan (UC-12, UC-13)**

**Tabel 4.11 Hasil Pengujian Black Box — Kelompok G: Notifikasi & Riwayat Pengajuan**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| G-01 | Warga membuka panel notifikasi setelah admin mengubah status | Admin setujui pengajuan | Panel notifikasi menampilkan pesan baru; *badge* jumlah belum dibaca bertambah | Sesuai harapan |
| G-02 | Warga mengklik notifikasi | Klik item notifikasi | Notifikasi ditandai "dibaca"; warga diarahkan ke halaman detail pengajuan yang bersangkutan | Sesuai harapan |
| G-03 | Warga melihat riwayat pengajuan dengan filter status `ditolak` | Pilih filter Ditolak | Tabel hanya menampilkan pengajuan berstatus ditolak; tombol "Ajukan Ulang" tampil pada baris yang relevan | Sesuai harapan |

---

**Kelompok H — Rekap Pengajuan & Ekspor CSV (UC-21)**

**Tabel 4.12 Hasil Pengujian Black Box — Kelompok H: Rekap Pengajuan & Ekspor CSV**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| H-01 | Admin melihat rekap tanpa filter | — | Semua pengajuan tampil di tabel; ringkasan per status tampil di atas | Sesuai harapan |
| H-02 | Admin mengfilter rekap berdasarkan jenis surat dan status | Pilih jenis surat + status tertentu | Tabel hanya menampilkan data sesuai filter; ringkasan tetap menampilkan total keseluruhan | Sesuai harapan |
| H-03 | Admin menetapkan rentang tanggal tidak valid (tanggal sampai lebih awal dari tanggal dari) | Tanggal dari: 01-08-2026, Tanggal sampai: 31-07-2026 | Sistem menampilkan pesan error "rentang tanggal tidak valid" | Sesuai harapan |
| H-04 | Admin mengekspor rekap ke CSV | Klik Ekspor CSV | Berkas CSV terunduh dengan nama `rekap-pengajuan-YYYYMMDD-HHMMSS.csv`; pengkodean UTF-8 BOM; terbuka dengan benar di Microsoft Excel | Sesuai harapan |
| H-05 | Admin mengklik Lihat Detail pada baris rekap | Klik Lihat Detail | Halaman *timeline* proses membuka kronologi lengkap pengajuan tersebut | Sesuai harapan |

---

**Kelompok I — Fitur Lanjutan: Ajukan Ulang & Kontrol Akses**

**Tabel 4.13 Hasil Pengujian Black Box — Kelompok I: Fitur Lanjutan & Kontrol Akses**

| **No** | **Skenario Pengujian** | **Masukan** | **Hasil yang Diharapkan** | **Hasil Pengujian** |
| ------ | ---------------------- | ----------- | ------------------------- | ------------------- |
| I-01 | Warga mengajukan ulang surat yang ditolak | Klik Ajukan Ulang pada pengajuan ditolak | Form terbuka dengan jenis surat dan keperluan pra-terisi; kotak peringatan berisi alasan penolakan admin; pengajuan baru tersimpan dengan nomor baru | Sesuai harapan |
| I-02 | Warga mencoba mengakses halaman detail milik warga lain | Akses URL `/pengajuan-surat/detail/{id_warga_lain}` | Sistem mengembalikan HTTP 403 atau 404 | Sesuai harapan |
| I-03 | Warga mencoba mengakses halaman admin (`/admin/dashboard`) | Akses langsung sebagai warga | Sistem mengembalikan HTTP 403 | Sesuai harapan |
| I-04 | Akses publik ke halaman persyaratan dokumen tanpa login | Akses `/persyaratan-dokumen` tanpa sesi | Halaman tampil lengkap; tidak ada pengalihan ke halaman login | Sesuai harapan |
| I-05 | Warga mengunduh Bukti Pengambilan setelah status `siap_diambil` | Klik Unduh Bukti Pengambilan | Berkas PDF berhasil diunduh; memuat nomor surat, data pemohon, jadwal, dan *QR code* | Sesuai harapan |
| I-06 | Warga mencoba mengunduh surat saat status masih `diajukan` | Akses *route* `/pengajuan-surat/{id}/unduh-surat` | Sistem menolak; kondisi `dapatUnduhSurat()` = *false*; tidak ada berkas yang disajikan | Sesuai harapan |

---

**Ringkasan Hasil Pengujian**

**Tabel 4.14 Ringkasan Hasil Pengujian Black Box Testing**

| **Kelompok** | **Jumlah Skenario** | **Sesuai Harapan** | **Tidak Sesuai** |
| ------------ | ------------------- | ------------------ | ---------------- |
| A — Autentikasi & Manajemen Profil | 15 | 15 | 0 |
| B — Jenis Surat & Persyaratan | 8 | 8 | 0 |
| C — Pengajuan, Unggah & Validasi | 9 | 9 | 0 |
| D — Verifikasi Pengajuan | 6 | 6 | 0 |
| E — Proses Surat, Jadwal & QR | 10 | 10 | 0 |
| F — Dashboard | 4 | 4 | 0 |
| G — Notifikasi & Riwayat | 3 | 3 | 0 |
| H — Rekap & Ekspor CSV | 5 | 5 | 0 |
| I — Fitur Lanjutan & Kontrol Akses | 6 | 6 | 0 |
| **Total** | **66** | **66** | **0** |

Berdasarkan hasil pengujian pada Tabel 4.5 sampai dengan Tabel 4.13 yang dirangkum pada Tabel 4.14, seluruh 66 skenario pengujian yang mencakup fitur utama dan kasus batas sistem menunjukkan hasil yang **sesuai harapan**. Dengan demikian, secara fungsional sistem yang dibangun telah berjalan sesuai dengan spesifikasi kebutuhan yang dirancang pada Bab III.

---

**4.3 Pembahasan**

**_4.3.1 Kesesuaian Implementasi dengan Rancangan Bab III_**

Implementasi sistem yang telah dilaksanakan secara keseluruhan selaras dengan rancangan yang diuraikan pada Bab III. Berikut ini adalah pembahasan perbandingan antara rancangan (desain) dan implementasi aktual pada aspek-aspek utama.

**a. Kelengkapan _Use Case_**

Seluruh 24 *use case* yang didefinisikan pada Tabel 3.8 (UC-01 s.d. UC-24) berhasil diimplementasikan. Setiap *use case* memiliki setidaknya satu Livewire *component* yang bersesuaian — sebagaimana tertuang dalam peta komponen pada dokumen arsitektur sistem, terdapat 13 Livewire *component* yang melayani seluruh *use case* tersebut. Cakupan setiap *use case* terverifikasi melalui skenario pengujian Black Box pada Subbab 4.2.2.

**b. Arsitektur Sistem**

Implementasi mengikuti arsitektur yang dirancang pada Subbab 3.6.1 secara konsisten: setiap *route* URL dipetakan tepat ke satu kelas Livewire *full-page component* yang menggabungkan seluruh logika dan tampilan dalam satu unit (1 *route* = 1 *component*). Tidak ada kelas *Service*, *Repository*, maupun *Form Request* terpisah yang dibuat — seluruh logika ditulis langsung di dalam *method* komponen sesuai konvensi arsitektur *flat* yang ditetapkan.

**c. Basis Data**

Implementasi basis data merealisasikan 8 tabel inti yang dirancang pada Subbab 3.6.3 (Gambar 3.20) ditambah satu tabel pendukung (`passkeys`). Tabel `jenis_surat_persyaratan` yang sudah dimasukkan dalam rancangan ERD Bab III Revisi diimplementasikan pada fase terakhir pengembangan (Phase 09 / US-9.1), sehingga sesuai dengan rancangan akhir. Kolom `siap_diambil_at` juga ditambahkan pada tabel `surat_terbit` untuk memungkinkan fitur *timeline* proses yang lebih presisi.

**d. Tentang PDF yang Dihasilkan**

Perlu diklarifikasi mengenai dokumen PDF yang dihasilkan sistem. Rancangan awal pada Bab III (Tabel 3.3) menyebutkan bahwa sistem menerbitkan "PDF surat keterangan bernomor resmi". Pada implementasi akhir, dokumen PDF yang di-*generate* adalah **Bukti Pengambilan Berkas** — dokumen yang berisi data pemohon, nomor surat resmi, jadwal pengambilan, dan *QR code* sekali pakai, namun bukan surat keterangan resmi itu sendiri. Hal ini mencerminkan praktik administrasi desa yang sebenarnya: surat keterangan resmi (yang memerlukan tanda tangan dan stempel basah dari Kepala Desa atau perangkat desa yang berwenang) tetap disiapkan secara fisik di kantor dan diserahkan kepada warga saat pengambilan. Bukti Pengambilan Berkas berfungsi sebagai dokumen digital penghubung antara proses pengajuan *online* dan pengambilan fisik, sekaligus memungkinkan pencatatan digital yang akurat melalui mekanisme pemindaian QR.

---

**_4.3.2 Pembahasan terhadap Rumusan Masalah dan Tujuan Penelitian_**

Berdasarkan hasil implementasi yang telah diuraikan pada Subbab 4.1 dan hasil pengujian pada Subbab 4.2, sistem yang dibangun dapat dianalisis keterkaitannya dengan permasalahan yang diidentifikasi pada Bab I dan rancangan solusi yang dirumuskan pada Bab III.

**Tabel 4.15 Keterkaitan Hasil Implementasi dengan Rumusan Masalah**

| **No** | **Kelemahan Sistem Berjalan** *(Tabel 3.3 Bab III)* | **Solusi yang Diimplementasikan** | **Fitur Terkait** |
| ------ | ---------------------------------------------------- | --------------------------------- | ----------------- |
| 1 | Warga tidak mengetahui persyaratan dokumen sebelum datang ke kantor | Halaman persyaratan dokumen publik (tanpa login) dengan *badge* terstruktur per jenis surat | UC-02, halaman `/persyaratan-dokumen` |
| 2 | Pencatatan pengajuan masih menggunakan buku register | Pencatatan digital pada basis data; rekap dengan filter multi-kriteria dan ekspor CSV | UC-21, halaman `/admin/rekap-pengajuan` |
| 3 | Warga tidak memperoleh informasi mengenai status pengajuan | *Dashboard* warga *status-first* + notifikasi *in-app* otomatis setiap perubahan status | UC-11, UC-12 |
| 4 | Proses verifikasi manual meningkatkan beban kerja petugas | Halaman verifikasi digital dengan pratinjau dokumen *inline* + tombol Setujui/Tolak | UC-17, halaman `/admin/verifikasi/{id}` |
| 5 | Tidak ada mekanisme konfirmasi pengambilan yang dapat diaudit | *QR code* sekali pakai pada PDF; sistem mencatat *scan* dengan waktu dan identitas admin | UC-20, halaman `/admin/scan-qr-pengambilan` |

Berdasarkan Tabel 4.15, seluruh permasalahan yang diidentifikasi melalui analisis PIECES pada Subbab 3.5.1 telah dijawab oleh fitur yang berhasil diimplementasikan dan telah diuji dengan hasil sesuai harapan. Secara lebih rinci, ketercapaian tujuan penelitian dapat diuraikan sebagai berikut.

**1.** Informasi persyaratan dokumen — Halaman persyaratan dokumen publik yang dapat diakses tanpa login (UC-02) menjawab permasalahan warga yang harus datang ke kantor desa hanya untuk mengetahui persyaratan. Dengan tersedianya *badge* terstruktur (Wajib diunggah / Boleh dikosongkan / Bawa ke kantor / Informasi), warga dapat menyiapkan berkas yang tepat sebelum berangkat, sehingga kunjungan berulang akibat berkas tidak lengkap diharapkan berkurang.

**2.** Digitalisasi pencatatan — Seluruh data pengajuan tersimpan pada basis data relasional yang dapat diakses kapan saja, menggantikan pencatatan buku register yang rentan hilang dan sulit direkapitulasi. Fitur rekap dengan filter multi-kriteria dan ekspor CSV (UC-21) memungkinkan petugas menghasilkan laporan berkala tanpa rekap manual.

**3.** Transparansi status pengajuan — Sistem menyediakan *dashboard* warga dengan kartu *hero* status yang memberikan informasi terkini dalam bahasa yang mudah dipahami, dilengkapi notifikasi *in-app* otomatis setiap kali status berubah. Warga tidak perlu lagi datang ke kantor atau menelepon untuk mengetahui perkembangan pengajuannya.

**4.** Efisiensi verifikasi — Halaman verifikasi digital (UC-17) memungkinkan petugas memeriksa dokumen pengajuan dari komputer tanpa harus menyortir berkas fisik. Daftar periksa fisik (US-9.5) membantu petugas mengingat dokumen apa yang harus diperiksa saat warga datang ke kantor.

**5.** Audit konfirmasi pengambilan — Mekanisme *QR code* sekali pakai memastikan setiap pengambilan surat tercatat secara digital dengan waktu dan identitas admin, sehingga tidak dapat diklaim lebih dari satu kali.

---

**_4.3.3 Kendala dan Solusi selama Pengembangan_**

Selama sembilan fase pengembangan, terdapat beberapa kendala teknis dan desain yang ditemui beserta solusi yang diterapkan. Uraian ini dimaksudkan sebagai bahan evaluasi dan pembelajaran untuk pengembangan sistem sejenis di kemudian hari.

**a. Kondisi Balapan pada Penerbitan Nomor Surat (_Race Condition_)**

Kendala: Penerbitan nomor surat yang berurutan (`470/{urut}/...`) berpotensi menghasilkan nomor duplikat apabila dua admin menyetujui pengajuan yang berbeda dalam waktu yang hampir bersamaan — keduanya dapat membaca jumlah surat terbit saat itu dan menghasilkan nomor yang sama sebelum salah satunya sempat tersimpan.

Solusi: Seluruh proses persetujuan (mengubah status → `diproses`, menerbitkan nomor surat, menyimpan *QR token*, menghasilkan PDF) dibungkus dalam satu blok `Cache::lock` + `DB::transaction` + `lockForUpdate` secara atomik. Pendekatan ini memastikan hanya satu proses yang dapat berjalan pada satu waktu, sehingga nomor surat selalu unik dan berurutan meskipun ada akses bersamaan.

**b. Pemindaian QR dari Dua Admin Secara Bersamaan**

Kendala: Jika dua admin (misalnya di dua komputer berbeda) memindai *QR code* yang sama dalam waktu hampir bersamaan, keduanya berpotensi membaca `qr_status = valid` dan menganggap *scan* berhasil.

Solusi: Pembaruan status QR dilakukan menggunakan *conditional UPDATE*: `UPDATE surat_terbit SET qr_status = 'invalid' WHERE qr_token = ? AND qr_status = 'valid'`. Karena kondisi `AND qr_status = 'valid'` memastikan hanya satu transaksi yang berhasil memperbarui (transaksi kedua tidak menemukan baris yang memenuhi kondisi), hanya pemindaian pertama yang diterima. Pemindaian berikutnya ditolak secara otomatis.

**c. Evolusi Sistem Persyaratan Dokumen (Phase 09)**

Kendala: Pada implementasi awal (Phase 02–03), persyaratan dokumen disimpan sebagai teks bebas di kolom `persyaratan_dokumen`, dan form pengajuan mendeteksi *slot* unggah berdasarkan deteksi kata kunci "KTP"/"KK" di teks tersebut. Pendekatan ini rapuh — kesalahan ejaan dapat menyembunyikan *slot* unggah, dan tidak ada cara untuk membedakan syarat yang perlu dibawa ke kantor dengan syarat yang perlu diunggah.

Solusi: Pada Phase 09 (US-9.1–9.3), diimplementasikan tabel `jenis_surat_persyaratan` yang menyimpan setiap baris persyaratan secara terstruktur dengan atribut `cara_pemenuhan` (eksplisit: `unggah` / `bawa_kantor` / `info`) dan `is_wajib`. Deteksi kata kunci dihapus dan digantikan sepenuhnya oleh aturan dari tabel ini. Data persyaratan lama di-migrasi secara otomatis melalui skrip migrasi satu kali.

**d. Validasi Tanggal Pengambilan**

Kendala: *Date picker* bawaan HTML tidak memiliki mekanisme bawaan untuk memblokir hari Sabtu, Minggu, dan hari libur nasional. Tanpa validasi tambahan, admin dapat memilih tanggal yang tidak valid sebagai jadwal pengambilan.

Solusi: Validasi tanggal dilakukan sepenuhnya di sisi *server* (PHP) pada *method* `isTanggalPengambilanSiap()` di komponen `DetailSuratDiproses`. Sistem memeriksa apakah tanggal yang dipilih bukan hari lampau, bukan Sabtu/Minggu, dan bukan hari libur nasional berdasarkan daftar yang dikonfigurasi. Pesan kesalahan spesifik ditampilkan untuk setiap kondisi yang tidak valid.

**e. Penyimpanan Berkas Privat**

Kendala: Berkas KTP/KK yang diunggah warga mengandung data pribadi sensitif. Menyimpan berkas pada folder `public` Laravel akan membuatnya dapat diakses langsung melalui URL publik oleh siapa saja yang mengetahui *path*-nya.

Solusi: Seluruh berkas unggahan disimpan pada *disk* `local` Laravel (folder `storage/app/private`), yang tidak dapat diakses secara langsung melalui URL. Akses berkas hanya dimungkinkan melalui *route* yang dilindungi *middleware* `role:admin`, sehingga hanya admin yang terautentikasi yang dapat melihat atau mengunduh berkas dokumen warga. Hal yang sama berlaku untuk berkas PDF Bukti Pengambilan.

**f. Penyederhanaan Alur Status: dari Dua Langkah menjadi Satu Langkah**

Kendala: Pada implementasi Phase 07, alur persetujuan terdiri dari dua langkah berurutan: `diajukan` → `disetujui` (oleh admin) → `diproses` (otomatis setelah `disetujui`). Status antara `disetujui` tidak menambah nilai informasi bagi warga maupun admin karena transisi `disetujui` → `diproses` terjadi seketika secara otomatis.

Solusi: Pada Phase 08 (US-8.4), alur disederhanakan menjadi satu langkah: klik "Setujui" langsung menghasilkan status `diproses` tanpa melewati `disetujui`. Status `disetujui` tetap dipertahankan dalam *enum* basis data untuk menjaga integritas data historis Phase 07, namun tidak lagi digunakan dalam alur pengajuan baru. Penyederhanaan ini membuat antarmuka lebih intuitif dan mengurangi kebingungan bagi warga.
