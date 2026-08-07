**BAB III**

**METODE PENELITIAN**

**3.1 Tinjauan Umum**

Kantor Desa Widodaren merupakan salah satu instansi pemerintahan tingkat desa yang menyelenggarakan pelayanan administratif kepada warga, salah satunya berupa penerbitan surat keterangan yang meliputi Surat Keterangan Domisili, Surat Keterangan Kelahiran/Kematian, dan Surat Keterangan Tidak Mampu. Volume pengajuan surat keterangan pada Kantor Desa Widodaren tergolong tinggi, yaitu mencapai kurang lebih 100 pengajuan setiap bulan, dengan Surat Keterangan Domisili sebagai jenis surat yang paling banyak diajukan, disusul Surat Keterangan Kelahiran/Kematian dan Surat Keterangan Tidak Mampu.

Berdasarkan hasil observasi dan wawancara yang telah diuraikan pada Bab I, proses pengajuan surat keterangan pada Kantor Desa Widodaren hingga saat ini masih dilaksanakan secara konvensional. Warga diharuskan datang langsung ke kantor desa untuk mengisi formulir secara manual, sementara pencatatan pengajuan masih menggunakan buku register sehingga data pengajuan rentan hilang, sulit direkapitulasi, dan tidak dapat ditelusuri secara cepat apabila dibutuhkan sewaktu-waktu untuk keperluan pelaporan desa.

Permasalahan utama yang melatarbelakangi penelitian ini bukan terletak pada lamanya waktu pemrosesan surat, melainkan pada ketiadaan media yang dapat menginformasikan persyaratan dokumen secara jelas kepada warga sebelum mengajukan, serta belum tersedianya sistem pencatatan digital yang dapat menggantikan pencatatan manual berbasis buku. Oleh karena itu, penelitian ini difokuskan pada perancangan dan pembangunan Sistem Informasi Pelayanan Surat Keterangan berbasis web dengan memanfaatkan framework Laravel sebagai kerangka kerja utama pengembangan perangkat lunak dan metode Prototyping sebagai pendekatan pengembangan sistem.

**3.2 Objek Penelitian**

Objek penelitian ini adalah proses pelayanan administrasi surat keterangan pada Kantor Desa Widodaren, yang meliputi tahapan pengajuan oleh warga, verifikasi kelengkapan dokumen oleh petugas (Kasi Pelayanan), hingga penerbitan dan pengambilan surat oleh warga. Pihak-pihak yang berkepentingan (stakeholder) dalam penelitian ini diuraikan pada Tabel 3.1 berikut.

**Tabel 3.1 Stakeholder Penelitian**

| **Stakeholder**                     | **Peran/Kepentingan**                                                                           |
| ----------------------------------- | ----------------------------------------------------------------------------------------------- |
| Warga Desa Widodaren                | Pengguna akhir yang mengajukan surat keterangan secara digital melalui sistem.                  |
| Petugas/Admin Desa (Kasi Pelayanan) | Mengelola data jenis surat, memverifikasi pengajuan, menerbitkan surat PDF, dan melayani pengambilan dokumen melalui pemindaian QR. |
| Kepala Desa                         | Penanggung jawab pelayanan publik desa.                                                         |
| Masyarakat Umum (Tamu/Publik)       | Dapat mengakses informasi persyaratan dokumen tanpa perlu login sebagai bentuk transparansi layanan. |
| Peneliti/Pengembang                 | Merancang, membangun, dan menguji sistem yang diusulkan.                                        |

**_3.2.1 Sejarah Singkat_**

_Isi dengan sejarah singkat Desa Widodaren (tahun berdiri, asal-usul nama, perkembangan pemerintahan desa) — dapat diperoleh dari profil desa/monografi desa atau wawancara dengan perangkat desa._

**_3.2.2 Visi dan Misi_**

_Isi dengan visi dan misi resmi Desa Widodaren, dapat diperoleh dari dokumen RPJMDes (Rencana Pembangunan Jangka Menengah Desa) atau papan profil di kantor desa._

**_3.2.3 Struktur Organisasi_**

Struktur organisasi Kantor Desa Widodaren terdiri atas Kepala Desa sebagai penanggung jawab pelayanan publik desa, Sekretaris Desa, serta beberapa Kepala Seksi (Kasi) dan Kepala Urusan (Kaur), salah satunya Kasi Pelayanan yang secara langsung menangani proses pengajuan, verifikasi, penerbitan surat, dan pelayanan pengambilan dokumen oleh warga.

_Sisipkan Gambar 3.1 Struktur Organisasi Kantor Desa Widodaren beserta pembagian tugas dan wewenang tiap jabatan yang relevan dengan alur pelayanan surat (Kepala Desa, Sekretaris Desa, Kasi Pelayanan)._

**3.3 Metode Pengumpulan Data**

Data yang digunakan dalam penelitian ini diperoleh melalui empat teknik pengumpulan data, dengan uraian sebagai berikut.

1\. Observasi, yaitu pengamatan secara langsung terhadap alur pelayanan surat keterangan di Kantor Desa Widodaren, mulai dari kedatangan warga, pengisian formulir manual, pemeriksaan kelengkapan dokumen oleh petugas, hingga penerbitan surat, guna memahami kondisi proses bisnis yang sedang berjalan.

2\. Wawancara, yaitu tanya jawab yang dilakukan dengan Kasi Pelayanan Kantor Desa Widodaren untuk menggali kebutuhan fungsional dan non-fungsional sistem, termasuk jumlah rata-rata pengajuan surat per bulan, jenis surat yang paling banyak diajukan, persyaratan dokumen tiap jenis surat, serta kendala yang dihadapi petugas dalam proses pelayanan.

3\. Studi Pustaka, yaitu pengumpulan referensi dari buku, jurnal ilmiah, serta hasil penelitian terdahulu yang relevan dengan topik sistem informasi pelayanan berbasis web, metode Prototyping, framework Laravel, dan perancangan basis data, sebagaimana telah diuraikan pada Bab II.

4\. Studi Dokumentasi, yaitu pengumpulan data pendukung berupa contoh format surat keterangan, buku register pengajuan, serta data jumlah pengajuan surat yang digunakan sebagai bahan analisis kebutuhan sistem.

**3.4 Metode Pengembangan Sistem**

Sistem dikembangkan menggunakan metode Prototyping, yaitu pendekatan pengembangan perangkat lunak yang memungkinkan pengguna terlibat secara aktif melalui siklus evaluasi dan perbaikan rancangan sebelum sistem diimplementasikan secara final. Tahapan metode Prototyping yang diterapkan pada penelitian ini diuraikan pada Tabel 3.2 berikut.

**Tabel 3.2 Tahapan Metode Prototyping pada Penelitian**

| **Tahap**                            | **Aktivitas dalam Penelitian**                                                                                                                                   |
| ------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1\. Pengumpulan Kebutuhan            | Observasi alur layanan surat dan wawancara dengan Kasi Pelayanan Kantor Desa Widodaren untuk menggali kebutuhan fungsional dan non-fungsional sistem.            |
| 2\. Membangun Prototipe              | Merancang tampilan dan alur awal sistem (Use Case Diagram, ERD, arsitektur sistem, rancangan antarmuka) berdasarkan kebutuhan yang telah dikumpulkan.            |
| 3\. Evaluasi Prototipe oleh Pengguna | Prototipe didiskusikan kembali dengan petugas desa untuk memastikan kesesuaian rancangan dengan kebutuhan lapangan.                                              |
| 4\. Perbaikan Prototipe              | Penyesuaian rancangan (Use Case, ERD, tampilan) dilakukan berdasarkan hasil evaluasi; tahap 3–4 dapat berulang hingga rancangan disepakati oleh pengguna.       |
| 5\. Pengkodean Sistem                | Implementasi rancangan final ke dalam kode program menggunakan Laravel 13, Livewire 4, Flux UI, Tailwind CSS v4, dan basis data SQLite.                          |
| 6\. Pengujian Sistem                 | Pengujian dengan metode Black Box Testing terhadap seluruh fitur utama sesuai skenario pengujian sebagaimana disajikan pada Bab IV.                              |
| 7\. Implementasi                     | Penerapan sistem yang telah diuji pada lingkungan Kantor Desa Widodaren, atau simulasi pada lingkungan pengembangan lokal untuk kebutuhan penelitian.            |

Siklus evaluasi dan perbaikan prototipe pada tahap ketiga dan keempat merupakan ciri khas metode ini yang membedakannya dari metode Waterfall. Rancangan sistem tidak diperlakukan sebagai sesuatu yang final sejak awal, melainkan disempurnakan secara bertahap berdasarkan umpan balik langsung dari petugas desa selaku calon pengguna sistem, sehingga hasil akhir sistem lebih sesuai dengan kebutuhan riil di lapangan.

**3.5 Analisis Sistem**

Tahap analisis dilakukan untuk memahami permasalahan pada sistem yang sedang berjalan sekaligus merumuskan kebutuhan sistem baru yang diusulkan. Analisis pada penelitian ini terdiri atas analisis kelemahan sistem, analisis kebutuhan sistem, dan analisis kelayakan sistem.

**_3.5.1 Analisis Kelemahan Sistem_**

Analisis kelemahan sistem yang sedang berjalan di Kantor Desa Widodaren dilakukan menggunakan kerangka berpikir PIECES (Performance, Information, Economics, Control, Efficiency, Service) sebagaimana dikemukakan oleh Jogiyanto, untuk mengidentifikasi permasalahan secara terstruktur pada setiap aspek sistem.

**_1\. Performance (Kinerja)_**

Proses verifikasi dan pencatatan pengajuan surat yang seluruhnya dilakukan secara manual menambah beban kerja petugas, terutama pada tahap pemeriksaan kelengkapan dokumen warga, sehingga kinerja pelayanan bergantung penuh pada kecepatan petugas dalam memeriksa berkas satu per satu tanpa bantuan sistem pencatatan digital.

**_2\. Information (Informasi)_**

Warga belum memperoleh informasi yang jelas mengenai persyaratan dokumen untuk masing-masing jenis surat keterangan sebelum mengajukan, sehingga tidak sedikit pengajuan yang tertunda karena berkas belum lengkap. Selain itu, belum tersedia media yang menyampaikan status pengajuan secara transparan kepada warga, sehingga warga tidak mengetahui perkembangan permohonannya tanpa datang langsung ke kantor desa.

**_3\. Economics (Ekonomi)_**

Kunjungan berulang warga akibat dokumen yang kurang lengkap menimbulkan pemborosan waktu dan biaya, baik bagi warga maupun petugas, yang sebenarnya dapat dihindari apabila persyaratan dokumen sudah diketahui warga sejak awal sebelum berangkat ke kantor desa.

**_4\. Control (Pengendalian)_**

Pencatatan pengajuan surat yang masih menggunakan buku register menyebabkan lemahnya kontrol terhadap data, sehingga berpotensi menimbulkan risiko kehilangan data, kerusakan arsip, serta kesulitan penelusuran kembali saat dibutuhkan untuk pelaporan desa maupun audit internal.

**_5\. Efficiency (Efisiensi)_**

Proses pengajuan yang mengharuskan warga datang langsung ke kantor desa untuk mengisi formulir dan menyerahkan dokumen secara manual dinilai tidak efisien, terlebih jika warga harus kembali lagi karena dokumen belum lengkap, meskipun waktu pemrosesan surat itu sendiri sudah tergolong cepat.

**_6\. Service (Pelayanan)_**

Tidak adanya informasi status pengajuan membuat warga tidak memperoleh kepastian atas proses pengajuannya, yang berpotensi menimbulkan keluhan berulang ke kantor desa dan menurunkan kualitas layanan secara keseluruhan di mata masyarakat.

**_Hasil Analisis Kelemahan Sistem_**

Berdasarkan analisis PIECES di atas, disimpulkan bahwa kelemahan utama sistem yang berjalan bukan terletak pada lamanya waktu pemrosesan surat, melainkan pada: (1) belum adanya media informasi persyaratan dokumen yang jelas bagi warga sebelum mengajukan, (2) belum adanya sistem pencatatan digital yang menggantikan pencatatan manual berbasis buku, dan (3) belum tersedianya media penyampaian status pengajuan kepada warga. Ringkasan kelemahan sistem berjalan beserta solusi yang diusulkan disajikan pada Tabel 3.3 berikut.

**Tabel 3.3 Analisis Kelemahan Sistem dan Solusi yang Diusulkan**

| **Kelemahan Sistem Berjalan**                                                                                       | **Solusi yang Diusulkan**                                                                                                                                      |
| ------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Warga tidak mengetahui persyaratan dokumen sebelum datang ke kantor desa sehingga harus kembali lagi.               | Sistem menyediakan halaman informasi persyaratan dokumen untuk setiap jenis surat yang dapat diakses publik tanpa perlu login.                                 |
| Pencatatan pengajuan surat masih menggunakan buku register sehingga rentan hilang, rusak, dan sulit direkapitulasi. | Sistem menyediakan pencatatan digital yang tersimpan pada basis data, sehingga data lebih aman, mudah direkapitulasi, dan dapat diekspor ke format CSV.        |
| Warga tidak memperoleh informasi mengenai status pengajuan surat yang telah diajukan.                               | Sistem menyediakan fitur notifikasi status pengajuan secara in-app yang dapat dipantau warga melalui dashboard secara real-time.                               |
| Proses verifikasi dan pencatatan yang seluruhnya manual meningkatkan beban kerja petugas dan risiko human error.    | Sistem menyediakan halaman verifikasi bagi Admin untuk memeriksa kelengkapan dokumen secara digital, disertai log audit setiap keputusan verifikasi.           |
| Proses pengambilan dokumen fisik tidak tercatat dan rawan kesalahan.                                                | Sistem menyediakan alur pengambilan dokumen berbasis pemindaian QR Code yang hanya dapat digunakan sekali, sehingga setiap pengambilan tercatat secara digital. |

**_3.5.2 Analisis Kebutuhan Sistem_**

Analisis kebutuhan sistem dilakukan agar sistem baru yang diusulkan dapat direalisasikan sesuai dengan kebutuhan pengguna dan mampu mengatasi kelemahan pada sistem yang sedang berjalan sebagaimana telah diuraikan pada subbab sebelumnya. Kebutuhan sistem diuraikan ke dalam empat aspek, yaitu kebutuhan perangkat keras, perangkat lunak, informasi, dan pengguna.

**_3.5.2.1 Kebutuhan Perangkat Keras_**

Kebutuhan perangkat keras dibedakan menjadi kebutuhan pada saat pengembangan sistem dan kebutuhan pada saat sistem diakses oleh pengguna, sebagaimana disajikan pada Tabel 3.4 berikut.

**Tabel 3.4 Kebutuhan Perangkat Keras**

| **Perangkat**    | **Spesifikasi Minimal**                                                        |
| ---------------- | ------------------------------------------------------------------------------ |
| Processor        | Setara Intel Core i3 atau AMD Ryzen 3                                          |
| RAM              | 4 GB                                                                           |
| Penyimpanan      | 128 GB (SSD/HDD)                                                               |
| Koneksi Internet | Koneksi internet stabil untuk mengakses sistem berbasis web                    |
| Perangkat Akses  | Komputer/laptop atau smartphone dengan peramban (browser) yang mendukung HTML5 |
| Pemindai QR      | Kamera perangkat atau pemindai QR fisik (untuk fitur scan QR pengambilan dokumen oleh Admin) |

**_3.5.2.2 Kebutuhan Perangkat Lunak_**

Kebutuhan perangkat lunak yang digunakan dalam pembangunan sistem diuraikan pada Tabel 3.5 berikut.

**Tabel 3.5 Kebutuhan Perangkat Lunak**

| **Layer**                     | **Teknologi**                           | **Keterangan**                                                                        |
| ----------------------------- | --------------------------------------- | ------------------------------------------------------------------------------------- |
| Backend Framework             | Laravel 13 (PHP 8.5+)                   | Routing, Eloquent ORM, validasi, middleware, migration                                |
| Reactive UI Framework         | Livewire 4                              | Komponen antarmuka reaktif berbasis PHP tanpa penulisan JavaScript manual             |
| UI Component Library          | Flux UI                                 | Kumpulan komponen antarmuka siap pakai yang terintegrasi dengan Livewire              |
| Styling                       | Tailwind CSS v4                         | Utility-first CSS untuk membangun antarmuka responsif secara cepat                    |
| Interaktivitas Ringan         | Alpine.js                               | Digunakan minimal untuk interaksi UI murni (toggle dropdown, konfirmasi modal)        |
| Basis Data                    | SQLite (lingkungan pengembangan)        | Basis data relasional ringan; dapat diganti PostgreSQL/MySQL pada lingkungan produksi |
| Autentikasi                   | Laravel Fortify                         | Backend autentikasi tanpa opini frontend: login, registrasi, reset password, 2FA     |
| Manajemen Berkas              | Laravel Filesystem (local disk)         | Penyimpanan berkas KTP/KK warga dan file PDF surat pada disk lokal (privat)          |
| Notifikasi In-App             | Tabel `notifikasi` (custom)             | Notifikasi status pengajuan disimpan pada tabel database, ditampilkan via Livewire    |
| Pembuatan Dokumen PDF         | DomPDF (barryvdh/laravel-dompdf)        | Generate file PDF surat keterangan dari template Blade saat pengajuan disetujui       |
| Pembuatan QR Code             | BaconQrCode (mikehaertl/phpqrcode)      | Generate QR Code untuk verifikasi pengambilan dokumen fisik                           |
| Kontrol Versi                 | Git dan GitHub                          | Pengelolaan versi kode program                                                        |
| Lingkungan Pengembangan Lokal | Laravel Herd / Laragon / Docker         | Web server, PHP, dan basis data lokal untuk keperluan pengembangan                   |

Pemilihan Laravel sebagai framework utama didasarkan pada beberapa pertimbangan, yaitu: (1) tersedianya Livewire sebagai ekosistem terintegrasi yang memungkinkan pembangunan antarmuka reaktif menggunakan PHP tanpa kompleksitas kerangka kerja frontend terpisah; (2) Eloquent ORM mempermudah implementasi relasi antartabel sesuai rancangan basis data tanpa banyak penulisan kueri SQL secara manual; (3) tersedianya fitur bawaan seperti migration, validation, notification, dan filesystem yang mempercepat proses pengembangan; serta (4) dokumentasi resmi yang lengkap dan komunitas pengguna yang besar sehingga memudahkan proses debugging.

Arsitektur yang diterapkan pada sistem ini menggunakan pola satu komponen Livewire per satu halaman (route), di mana seluruh logika bisnis ditulis langsung di dalam method komponen Livewire tanpa lapisan abstraksi tambahan seperti Service Class atau Repository Pattern. Pendekatan ini dipilih karena lebih mudah dipahami dan dipelihara pada skala penelitian.

**_3.5.2.3 Kebutuhan Informasi_**

Kebutuhan informasi atau keluaran (output) yang disediakan oleh sistem yang diusulkan meliputi:

- Informasi persyaratan dokumen untuk setiap jenis surat keterangan yang dapat diakses publik (tanpa login) maupun oleh warga yang sudah login, agar dokumen dapat dipersiapkan sebelum pengajuan.
- Informasi status pengajuan surat dengan enam tahapan status (_diajukan_, _diproses_, _siap diambil_, _selesai_, _ditolak_, dan _ajukan ulang_) yang dapat dipantau warga melalui dashboard secara mandiri.
- Riwayat seluruh pengajuan surat milik masing-masing warga sebagai bentuk transparansi layanan.
- File PDF surat keterangan yang dapat diunduh dan dicetak warga secara mandiri setelah pengajuan disetujui dan surat diterbitkan.
- QR Code sekali pakai yang terdapat pada surat keterangan untuk memverifikasi pengambilan dokumen fisik di kantor desa.
- Nomor surat resmi otomatis dengan format baku Kantor Desa (contoh: 470/001/DS-WDN/I/2025).
- Rekapitulasi data pengajuan surat bagi petugas/Admin desa yang dapat difilter berdasarkan jenis surat, status, dan rentang tanggal, serta dapat diekspor ke format CSV.
- Notifikasi perubahan status pengajuan yang disampaikan secara in-app kepada warga melalui panel notifikasi di header dashboard.
- Riwayat/timeline proses pengajuan secara kronologis bagi Admin, mulai dari pengajuan masuk, keputusan verifikasi, penerbitan surat, hingga pengambilan dokumen.

**_3.5.2.4 Kebutuhan Pengguna (User)_**

Sistem yang diusulkan melibatkan tiga peran (role) pengguna sebagaimana disajikan pada Tabel 3.6 berikut.

**Tabel 3.6 Kebutuhan Pengguna**

| **Peran**                           | **Hak Akses**                                                                                                                                                                                                      |
| ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Publik (Tamu/Guest)                 | Mengakses halaman landing dan halaman informasi persyaratan dokumen setiap jenis surat tanpa perlu mendaftarkan akun.                                                                                              |
| Warga                               | Registrasi akun; login; mengelola profil; mengajukan surat keterangan beserta unggah berkas persyaratan; memantau status dan riwayat pengajuan; menerima notifikasi in-app; mengajukan ulang setelah ditolak; serta mengunduh dan mencetak surat keterangan yang telah diterbitkan. |
| Admin/Petugas Desa (Kasi Pelayanan) | Mengelola data jenis surat beserta persyaratannya; memverifikasi atau menolak pengajuan warga; mengelola daftar surat yang sedang diproses; menetapkan tanggal pengambilan dokumen; memindai QR Code untuk menandai pengambilan selesai; melihat rekap dan timeline pengajuan; serta mengekspor data ke CSV. |

**_3.5.3 Analisis Kelayakan Sistem_**

Analisis kelayakan dilakukan untuk menilai apakah sistem yang diusulkan layak untuk dikembangkan dan diterapkan di Kantor Desa Widodaren, ditinjau dari empat aspek berikut.

**_3.5.3.1 Kelayakan Teknologi_**

Secara teknologi, sistem yang diusulkan dinilai layak karena menggunakan framework Laravel yang bersifat open-source, memiliki dokumentasi yang luas, serta umum digunakan pada penelitian rekayasa perangkat lunak sejenis. Penggunaan Livewire 4 sebagai kerangka kerja antarmuka reaktif memungkinkan pengembangan fitur interaktif tanpa memerlukan keahlian JavaScript tingkat lanjut. Basis data SQLite yang digunakan pada tahap pengembangan bersifat serverless dan tidak memerlukan instalasi tambahan, sementara DomPDF dan BaconQrCode tersedia sebagai paket open-source dengan dukungan komunitas yang baik.

**_3.5.3.2 Kelayakan Hukum_**

Secara hukum, sistem yang diusulkan tidak melanggar ketentuan yang berlaku karena seluruh perangkat lunak yang digunakan merupakan perangkat lunak open-source dengan lisensi bebas digunakan untuk kepentingan pengembangan maupun penelitian. Data pribadi warga yang diunggah, seperti Kartu Tanda Penduduk (KTP) dan Kartu Keluarga (KK), disimpan di disk privat (tidak dapat diakses langsung melalui URL publik) dan dikelola khusus untuk kebutuhan verifikasi pelayanan administrasi desa sesuai kewenangan Kantor Desa Widodaren.

**_3.5.3.3 Kelayakan Operasional_**

Secara operasional, sistem ini layak diterapkan karena warga maupun petugas desa telah terbiasa menggunakan peramban (browser) dalam kesehariannya, sehingga adopsi sistem dapat dilakukan tanpa memerlukan pelatihan yang intensif. Petugas pelayanan (Kasi Pelayanan) sebagai pengguna utama sistem juga memiliki kemampuan dasar mengoperasikan komputer sehingga siap menjalankan proses verifikasi melalui sistem. Fitur pemindaian QR Code memanfaatkan kamera perangkat yang sudah tersedia sehingga tidak memerlukan perangkat keras tambahan yang khusus.

**_3.5.3.4 Kelayakan Ekonomi_**

Secara ekonomi, biaya pengembangan sistem relatif minim karena seluruh perangkat lunak yang digunakan bersifat open-source dan tidak memerlukan lisensi berbayar. Biaya operasional pasca-implementasi hanya berupa biaya hosting dan domain yang bersifat opsional, karena untuk kebutuhan penelitian skripsi sistem dapat dijalankan pada lingkungan pengembangan lokal (localhost), sebagaimana disajikan pada Tabel 3.7 berikut.

**Tabel 3.7 Estimasi Biaya Implementasi**

| **Komponen**                                                       | **Estimasi Biaya**                           |
| ------------------------------------------------------------------ | -------------------------------------------- |
| Perangkat lunak (Laravel, PHP, SQLite, VS Code, Livewire, Flux UI) | Rp0 (open-source)                            |
| Hosting dan domain (per tahun, opsional)                           | \[isi sesuai penyedia hosting yang dipilih\] |
| Biaya pengembang (jika ada)                                        | Tidak ada, dikerjakan mandiri oleh peneliti  |

**3.6 Perancangan Sistem**

Perancangan sistem meliputi perancangan arsitektur, perancangan proses, perancangan basis data, dan perancangan antarmuka (interface) yang menjadi acuan dalam tahap implementasi pada Bab IV.

**_3.6.1 Arsitektur Sistem_**

Sistem dibangun menggunakan arsitektur berbasis komponen Livewire dengan pendekatan full-stack PHP, di mana setiap halaman (route) dipetakan ke satu komponen Livewire yang mengelola seluruh logika dan tampilan halaman tersebut. Penjelasan tiap lapisan (layer) pada arsitektur sistem adalah sebagai berikut.

- **Client Layer**, yaitu antarmuka yang dirender di sisi server menggunakan Blade View dan komponen Flux UI, ditata dengan Tailwind CSS v4. Interaktivitas ringan seperti dropdown dan konfirmasi modal menggunakan Alpine.js secara minimal. Livewire mengelola reaktivitas data antara server dan browser melalui WebSocket/AJAX tanpa memerlukan penulisan JavaScript manual.
- **Application Layer**, yaitu lapisan yang berisi Routes, Middleware (autentikasi Fortify dan role Warga/Admin), serta komponen Livewire sebagai unit logika dan tampilan utama. Seluruh logika bisnis ditulis langsung di dalam method komponen Livewire menggunakan Eloquent ORM, tanpa lapisan Service Class atau Repository yang terpisah.
- **Data Layer**, yaitu basis data SQLite pada lingkungan pengembangan (dapat diganti PostgreSQL/MySQL pada lingkungan produksi) yang dikelola melalui Eloquent migration. Laravel local disk digunakan untuk menyimpan berkas dokumen persyaratan (KTP/KK) dan file PDF surat keterangan secara privat.

Alur permintaan (request flow) pada sistem secara ringkas adalah sebagai berikut: (1) warga atau admin mengakses aplikasi melalui peramban; (2) permintaan HTTP diterima oleh Routes yang meneruskannya ke Middleware; (3) Middleware memeriksa status autentikasi dan role pengguna; (4) komponen Livewire merender halaman penuh saat pertama kali diakses, kemudian menangani interaksi berikutnya melalui AJAX tanpa reload halaman; (5) Livewire component berinteraksi langsung dengan Eloquent Model untuk membaca atau menulis data ke basis data; (6) berkas dokumen pengajuan dan PDF surat disimpan dan diambil melalui Laravel Storage (local disk); (7) notifikasi in-app disimpan pada tabel `notifikasi` dan ditampilkan melalui komponen `PanelNotifikasi` yang tertanam di layout navigasi; dan (8) komponen Livewire mengembalikan respons Blade yang diperbarui kepada peramban pengguna.

_Sisipkan Gambar 3.2 Diagram Arsitektur Sistem Informasi Pelayanan Surat Keterangan._

**_3.6.2 Perancangan Proses_**

Perancangan proses digambarkan menggunakan pendekatan berorientasi objek melalui Unified Modeling Language (UML), yaitu use case diagram dan activity diagram sebagaimana telah diuraikan pada Bab II.

**_1\. Use Case Diagram_**

Aktor yang terlibat dalam sistem terdiri atas tiga peran: Publik (Tamu), Warga, dan Admin/Petugas Desa. Daftar use case sistem disajikan pada Tabel 3.8.

**Tabel 3.8 Deskripsi Use Case**

| **Use Case**                          | **Aktor**             | **Deskripsi Singkat**                                                                                               |
| ------------------------------------- | --------------------- | ------------------------------------------------------------------------------------------------------------------- |
| Lihat Persyaratan Dokumen             | Publik, Warga, Admin  | Menampilkan daftar dokumen persyaratan untuk tiap jenis surat; dapat diakses tanpa login.                          |
| Registrasi Akun                       | Publik                | Mendaftarkan akun warga baru dengan data diri (NIK, nama, no. telepon, alamat, email, kata sandi).                  |
| Login                                 | Warga, Admin          | Autentikasi pengguna berdasarkan role sebelum mengakses dashboard masing-masing.                                    |
| Kelola Profil & Reset Password        | Warga, Admin          | Mengubah data profil dan mereset kata sandi melalui email.                                                          |
| Ajukan Surat Keterangan               | Warga                 | Mengisi form pengajuan surat sesuai jenis yang dipilih beserta unggah berkas persyaratan (KTP/KK).                  |
| Lihat Status & Riwayat Pengajuan      | Warga                 | Menampilkan status terkini dan riwayat seluruh pengajuan milik warga beserta detailnya.                             |
| Ajukan Ulang Setelah Ditolak          | Warga                 | Mengajukan kembali pengajuan yang sebelumnya ditolak dengan data yang diperbarui.                                   |
| Unduh / Cetak Surat Keterangan        | Warga                 | Mengunduh atau mencetak file PDF surat keterangan yang telah diterbitkan.                                           |
| Terima Notifikasi In-App              | Warga                 | Menerima dan membaca notifikasi perubahan status pengajuan di dalam aplikasi.                                       |
| Kelola Data Jenis Surat               | Admin                 | Menambah, mengubah, menonaktifkan, dan menghapus data jenis surat beserta persyaratannya.                           |
| Verifikasi / Tolak Pengajuan Surat    | Admin                 | Memeriksa kelengkapan dokumen yang diunggah warga, kemudian menyetujui (terbitkan PDF + QR) atau menolak pengajuan. |
| Kelola Daftar Surat Diproses          | Admin                 | Memantau pengajuan yang sudah disetujui, menetapkan tanggal pengambilan, dan menandai siap diambil.                 |
| Scan QR Pengambilan                   | Admin                 | Memindai QR Code pada surat warga untuk mengonfirmasi pengambilan dokumen fisik dan menandai pengajuan selesai.     |
| Rekap Pengajuan & Ekspor CSV          | Admin                 | Melihat rekapitulasi data pengajuan dengan filter jenis surat/status/tanggal dan mengekspor data ke CSV.            |
| Lihat Timeline Detail Pengajuan       | Admin                 | Melihat riwayat kronologis proses pengajuan dari awal hingga pengambilan dokumen.                                   |

_Sisipkan Gambar 3.3 Use Case Diagram Sistem Informasi Pelayanan Surat Keterangan._

**_2\. Activity Diagram_**

Activity Diagram digunakan untuk menggambarkan alur kerja proses utama pada sistem. Berikut diuraikan alur aktivitas pada proses-proses utama yang diimplementasikan.

**a. Activity Diagram Registrasi Akun Warga**

Alur dimulai ketika pengunjung membuka halaman registrasi. Pengunjung mengisi formulir dengan data NIK, nama lengkap, nomor telepon, alamat, email, dan kata sandi. Sistem melakukan validasi; apabila NIK atau email sudah terdaftar, sistem menampilkan pesan kesalahan. Apabila seluruh data valid, sistem menyimpan akun dengan role "warga" dan mengarahkan pengguna ke halaman dashboard warga.

_Sisipkan Gambar 3.4 Activity Diagram Registrasi Akun Warga._

**b. Activity Diagram Login dan Redirect Berdasarkan Role**

Alur dimulai ketika pengguna membuka halaman login dan memasukkan email beserta kata sandi. Laravel Fortify memverifikasi kredensial; jika gagal, sistem menampilkan pesan kesalahan. Jika berhasil, sistem membaca nilai `role` pada tabel `users`: pengguna dengan role "warga" diarahkan ke `/dashboard`, sedangkan role "admin" diarahkan ke `/admin/dashboard`.

_Sisipkan Gambar 3.5 Activity Diagram Login dan Redirect Berdasarkan Role._

**c. Activity Diagram Pengajuan Surat oleh Warga**

Alur dimulai ketika warga login dan membuka halaman pengajuan surat. Warga memilih jenis surat, kemudian sistem menampilkan daftar persyaratan dokumen yang harus dilampirkan. Warga mengisi keperluan surat dan mengunggah berkas KTP dan/atau KK sesuai persyaratan. Sistem melakukan validasi kelengkapan; apabila berkas belum lengkap atau data tidak valid, sistem menampilkan pesan kesalahan. Apabila valid, sistem menyimpan data pengajuan dengan status "diajukan" beserta nomor pengajuan unik (format: PJ-YYYYMMDD-####) dan file dokumen pada disk privat.

_Sisipkan Gambar 3.6 Activity Diagram Pengajuan Surat oleh Warga._

**d. Activity Diagram Verifikasi Pengajuan oleh Admin**

Alur dimulai ketika admin membuka halaman Daftar Pengajuan Surat (Verifikasi) yang secara default menampilkan pengajuan berstatus "diajukan". Admin memilih satu pengajuan untuk diperiksa pada halaman detail. Admin meninjau data warga dan pratinjau dokumen KTP/KK yang diunggah. Apabila dokumen dinyatakan lengkap dan sesuai, admin mengklik tombol "Setujui"; sistem secara atomik (menggunakan `DB::transaction` + `lockForUpdate`) mengubah status menjadi "diproses", men-generate nomor surat resmi, file PDF surat, dan QR Code sekali pakai, lalu mencatat log verifikasi dan mengirim notifikasi in-app kepada warga. Apabila dokumen tidak sesuai, admin mengklik "Tolak" disertai catatan alasan; sistem mengubah status menjadi "ditolak", mencatat log verifikasi, dan mengirim notifikasi kepada warga.

_Sisipkan Gambar 3.7 Activity Diagram Verifikasi Pengajuan oleh Admin._

**e. Activity Diagram Alur Status Pengajuan (Keseluruhan)**

Alur status pengajuan surat mengikuti diagram transisi berikut: (1) warga submit pengajuan → status "diajukan"; (2) admin setujui → status "diproses" (PDF + QR digenerate otomatis); (3) admin tolak → status "ditolak" (warga dapat mengajukan ulang → kembali ke "diajukan"); (4) admin tetapkan tanggal pengambilan → status "siap diambil"; (5) admin scan QR → status "selesai".

_Sisipkan Gambar 3.8 Activity Diagram Alur Status Pengajuan._

**f. Activity Diagram Pengambilan Dokumen dengan Scan QR**

Alur dimulai ketika admin membuka halaman Scan QR Pengambilan. Admin memasukkan atau memindai token QR dari surat warga. Sistem memverifikasi QR: apabila status QR "invalid" atau token tidak ditemukan, sistem menampilkan pesan kesalahan. Apabila valid, sistem secara atomik menandai QR sebagai "invalid" (sekali pakai), mengubah status pengajuan menjadi "selesai", mencatat waktu scan, dan mengirimkan notifikasi kepada warga bahwa dokumen telah diambil.

_Sisipkan Gambar 3.9 Activity Diagram Pengambilan Dokumen dengan Scan QR._

**_3.6.3 Perancangan Basis Data_**

Perancangan basis data digambarkan melalui Entity Relationship Diagram (ERD) yang menunjukkan entitas beserta relasinya, kemudian diimplementasikan ke dalam struktur tabel pada basis data melalui Eloquent migration. Entitas utama yang terlibat dalam sistem meliputi `users`, `jenis_surat`, `pengajuan_surat`, `dokumen_persyaratan`, `log_verifikasi`, `notifikasi`, dan `surat_terbit`.

_Sisipkan Gambar 3.10 Entity Relationship Diagram (ERD) Sistem Informasi Pelayanan Surat Keterangan._

Rancangan struktur tabel basis data hasil dari ERD tersebut disajikan pada Tabel 3.9 sampai dengan Tabel 3.15.

**Tabel 3.9 Struktur Tabel `users`**

| **Kolom**               | **Tipe Data**         | **Keterangan**                                                           |
| ----------------------- | --------------------- | ------------------------------------------------------------------------ |
| id                      | BIGINT (PK, AI)       | Primary key                                                              |
| nik                     | VARCHAR(16), UNIQUE   | Nomor Induk Kependudukan; digunakan sebagai identifier unik warga        |
| name                    | VARCHAR(100)          | Nama lengkap pengguna                                                    |
| email                   | VARCHAR(100), UNIQUE  | Email untuk keperluan reset password                                     |
| no_telepon              | VARCHAR(20)           | Nomor telepon warga                                                      |
| alamat                  | TEXT                  | Alamat domisili warga                                                    |
| role                    | VARCHAR(20)           | Peran pengguna: `warga` (default) atau `admin`                           |
| password                | VARCHAR(255)          | Kata sandi terenkripsi (bcrypt)                                          |
| email_verified_at       | TIMESTAMP (nullable)  | Null jika email belum diverifikasi                                       |
| created_at / updated_at | TIMESTAMP             | Waktu pembuatan/pembaruan data                                           |

**Tabel 3.10 Struktur Tabel `jenis_surat`**

| **Kolom**               | **Tipe Data**            | **Keterangan**                                                              |
| ----------------------- | ------------------------ | --------------------------------------------------------------------------- |
| id                      | BIGINT (PK, AI)          | Primary key                                                                 |
| nama_surat              | VARCHAR(100), UNIQUE     | Nama jenis surat keterangan                                                 |
| deskripsi               | TEXT (nullable)          | Deskripsi singkat kegunaan surat                                            |
| persyaratan_dokumen     | TEXT (nullable)          | Daftar dokumen yang wajib dilampirkan (teks bebas)                          |
| deleted_at              | TIMESTAMP (nullable)     | SoftDeletes: null = aktif; diisi saat admin menonaktifkan jenis surat       |
| created_at / updated_at | TIMESTAMP                | Waktu pembuatan/pembaruan data                                              |

**Tabel 3.11 Struktur Tabel `pengajuan_surat`**

| **Kolom**               | **Tipe Data**   | **Keterangan**                                                                     |
| ----------------------- | --------------- | ---------------------------------------------------------------------------------- |
| id                      | BIGINT (PK, AI) | Primary key                                                                        |
| user_id                 | BIGINT (FK)     | Relasi ke `users.id` (pemohon)                                                     |
| jenis_surat_id          | BIGINT (FK)     | Relasi ke `jenis_surat.id`                                                         |
| nomor_pengajuan         | VARCHAR(30), UK | Nomor unik pengajuan dengan format `PJ-YYYYMMDD-####`                              |
| keperluan               | TEXT            | Tujuan penggunaan surat yang diisi warga                                           |
| status                  | VARCHAR(20)     | Status pengajuan: `diajukan` \| `diproses` \| `siap_diambil` \| `selesai` \| `ditolak` \| `disetujui` (historis) |
| catatan_admin           | TEXT (nullable) | Catatan atau alasan penolakan dari admin                                           |
| diverifikasi_oleh       | BIGINT (nullable, FK) | Relasi ke `users.id` (admin yang memverifikasi)                              |
| tanggal_pengajuan       | DATE            | Tanggal warga mengajukan                                                           |
| created_at / updated_at | TIMESTAMP       | Waktu pembuatan/pembaruan data                                                     |

**Tabel 3.12 Struktur Tabel `dokumen_persyaratan`**

| **Kolom**               | **Tipe Data**   | **Keterangan**                                                        |
| ----------------------- | --------------- | --------------------------------------------------------------------- |
| id                      | BIGINT (PK, AI) | Primary key                                                           |
| pengajuan_id            | BIGINT (FK)     | Relasi ke `pengajuan_surat.id`                                        |
| jenis_dokumen           | VARCHAR(10)     | Jenis dokumen yang diunggah: `ktp` atau `kk`                         |
| file_path               | VARCHAR(255)    | Path relatif penyimpanan berkas pada local disk (privat)              |
| created_at / updated_at | TIMESTAMP       | Waktu pembuatan/pembaruan data                                        |

Setiap pengajuan hanya dapat memiliki satu berkas per jenis dokumen (satu KTP dan satu KK). Berkas hanya dapat diakses melalui route yang dilindungi middleware `role:admin`, bukan melalui URL publik.

**Tabel 3.13 Struktur Tabel `log_verifikasi`**

| **Kolom**    | **Tipe Data**   | **Keterangan**                                               |
| ------------ | --------------- | ------------------------------------------------------------ |
| id           | BIGINT (PK, AI) | Primary key                                                  |
| pengajuan_id | BIGINT (FK)     | Relasi ke `pengajuan_surat.id`                               |
| admin_id     | BIGINT (FK)     | Relasi ke `users.id` (admin yang melakukan aksi)             |
| aksi         | VARCHAR         | Jenis aksi yang dilakukan: `setujui`, `tolak`, `siap_diambil` |
| keterangan   | TEXT (nullable) | Alasan penolakan atau catatan tambahan                       |
| created_at   | TIMESTAMP       | Waktu aksi dilakukan                                         |

Tabel ini bersifat hanya-tambah (insert-only). Tidak ada operasi update atau delete. Digunakan sebagai audit trail lengkap setiap keputusan verifikasi.

**Tabel 3.14 Struktur Tabel `notifikasi`**

| **Kolom**    | **Tipe Data**                   | **Keterangan**                                          |
| ------------ | ------------------------------- | ------------------------------------------------------- |
| id           | BIGINT (PK, AI)                 | Primary key                                             |
| user_id      | BIGINT (FK)                     | Relasi ke `users.id` (penerima notifikasi)              |
| pengajuan_id | BIGINT (FK)                     | Relasi ke `pengajuan_surat.id` (pemicu notifikasi)      |
| pesan        | TEXT                            | Isi pesan notifikasi dalam Bahasa Indonesia             |
| status_baca  | ENUM(`belum`, `dibaca`)         | Status baca notifikasi; default `belum`                 |
| created_at   | TIMESTAMP                       | Waktu notifikasi dibuat                                 |

**Tabel 3.15 Struktur Tabel `surat_terbit`**

| **Kolom**              | **Tipe Data**         | **Keterangan**                                                                                    |
| ---------------------- | --------------------- | ------------------------------------------------------------------------------------------------- |
| id                     | BIGINT (PK, AI)       | Primary key                                                                                       |
| pengajuan_id           | BIGINT (FK, UNIQUE)   | Relasi 1:1 ke `pengajuan_surat.id`; satu pengajuan hanya menghasilkan satu surat                  |
| nomor_surat            | VARCHAR(50), UNIQUE   | Nomor surat resmi otomatis dengan format `470/{urut}/DS-WDN/{bulan Romawi}/{tahun}`               |
| file_path              | VARCHAR(255)          | Path relatif file PDF surat pada local disk (privat)                                              |
| tanggal_terbit         | DATE                  | Tanggal surat digenerate                                                                          |
| tanggal_pengambilan    | DATE (nullable)       | Tanggal yang ditetapkan admin untuk pengambilan dokumen fisik                                     |
| siap_diambil_at        | TIMESTAMP (nullable)  | Waktu admin menandai surat siap diambil                                                           |
| jam_kerja_label        | VARCHAR(100) (nullable) | Label jam kerja yang ditampilkan pada surat, contoh: "Senin–Kamis 08.00–16.00 WIB"             |
| qr_token               | VARCHAR(64), UNIQUE   | Token opaque acak 64 karakter untuk QR Code pengambilan; berlaku sekali                           |
| qr_status              | VARCHAR(20)           | Status QR: `valid` (belum digunakan) atau `invalid` (sudah digunakan)                            |
| qr_digunakan_at        | TIMESTAMP (nullable)  | Waktu QR berhasil dipindai untuk pengambilan                                                      |
| qr_digunakan_oleh      | BIGINT (nullable, FK) | Relasi ke `users.id` (admin yang memindai QR)                                                    |
| diterbitkan_oleh       | BIGINT (FK)           | Relasi ke `users.id` (admin yang menyetujui pengajuan)                                           |
| created_at / updated_at | TIMESTAMP            | Waktu pembuatan/pembaruan data                                                                    |

Tabel `surat_terbit` diisi secara otomatis saat admin menyetujui pengajuan (aksi "Setujui" pada halaman Detail Pengajuan Verifikasi). Proses penerbitan menggunakan `DB::transaction` dan `lockForUpdate` untuk memastikan nomor surat tidak terduplikasi meskipun terdapat request serentak (concurrency-safe).

**_3.6.4 Perancangan Antarmuka (Interface)_**

Perancangan antarmuka bertujuan menggambarkan rancangan halaman-halaman utama pada sistem sebelum diimplementasikan ke dalam kode program. Rancangan antarmuka dibagi menjadi tiga kelompok berdasarkan pengguna, yaitu antarmuka untuk Publik (Tamu), antarmuka untuk Warga, dan antarmuka untuk Admin/Petugas Desa.

**a. Rancangan Antarmuka Publik (Tamu/Guest)**

- **Halaman Landing (Beranda Publik)**, menampilkan informasi singkat tentang sistem pelayanan dan tautan menuju halaman login, registrasi, serta informasi persyaratan dokumen.
- **Halaman Persyaratan Dokumen**, menampilkan daftar jenis surat keterangan beserta dokumen yang wajib dilampirkan. Halaman ini dapat diakses oleh siapa saja tanpa perlu mendaftarkan atau login ke akun.

_Sisipkan Gambar 3.11 Rancangan Antarmuka Halaman Publik._

**b. Rancangan Antarmuka Warga**

- **Halaman Registrasi dan Login**, berisi formulir pendaftaran akun warga (NIK, nama, no. telepon, alamat, email, kata sandi) serta formulir masuk ke dalam sistem.
- **Halaman Dashboard Warga**, menampilkan kartu status pengajuan aktif terbaru (_hero status card_) beserta tombol unduh surat apabila sudah diterbitkan, ringkasan pengajuan yang masih berlangsung, dan tautan cepat menuju riwayat pengajuan.
- **Halaman Form Pengajuan Surat**, berisi pemilihan jenis surat, kolom keperluan, tampilan persyaratan dokumen yang harus dilampirkan, serta area unggah berkas KTP dan/atau KK.
- **Halaman Riwayat Pengajuan**, menampilkan seluruh riwayat pengajuan milik warga dengan filter status, dilengkapi tautan ke detail pengajuan dan tombol ajukan ulang untuk pengajuan yang ditolak.
- **Halaman Detail Pengajuan Warga**, menampilkan informasi lengkap satu pengajuan: status terkini, nomor pengajuan, keperluan, riwayat notifikasi, serta tombol unduh dan cetak surat apabila surat sudah diterbitkan.
- **Panel Notifikasi In-App**, tertanam di header navigasi sebagai ikon lonceng; menampilkan jumlah notifikasi belum dibaca dan daftar notifikasi terbaru yang dapat diklik untuk menandai sudah dibaca.

_Sisipkan Gambar 3.12 Rancangan Antarmuka Warga._

**c. Rancangan Antarmuka Admin/Petugas Desa**

- **Halaman Dashboard Admin**, menampilkan kartu jumlah pengajuan per status dengan kode warna berdasarkan usia pengajuan (normal/peringatan/mendesak), tabel antrian prioritas pengajuan yang membutuhkan tindakan segera, dan tabel aktif seluruh pengajuan yang belum selesai.
- **Halaman Kelola Jenis Surat**, digunakan untuk menambah, mengubah, menonaktifkan (soft delete), dan menghapus permanen data jenis surat beserta persyaratan dokumennya, dilengkapi fitur pencarian langsung.
- **Halaman Daftar Pengajuan Surat (Verifikasi)**, menampilkan seluruh pengajuan warga yang dapat difilter berdasarkan status, dengan tombol tangani yang mengarahkan ke halaman detail pengajuan.
- **Halaman Detail Pengajuan Verifikasi**, menampilkan data pengajuan lengkap beserta pratinjau berkas KTP/KK yang diunggah warga; dilengkapi tombol "Setujui" (yang secara otomatis men-generate PDF dan QR) dan "Tolak" beserta kolom alasan penolakan.
- **Halaman Daftar Surat Diproses**, menampilkan pengajuan dengan status "diproses" atau "siap diambil" beserta informasi nomor surat dan tanggal terbit.
- **Halaman Detail Surat Diproses**, menampilkan detail surat yang sedang diproses; admin dapat menetapkan tanggal pengambilan dokumen dan menandai surat sebagai "siap diambil".
- **Halaman Scan QR Pengambilan**, menyediakan kolom input token QR (dapat diisi manual atau dengan pemindai QR) untuk memproses pengambilan dokumen fisik oleh warga dan menandai pengajuan sebagai "selesai".
- **Halaman Rekap Pengajuan**, menampilkan seluruh data pengajuan yang dapat difilter berdasarkan jenis surat, status, dan rentang tanggal, dilengkapi ringkasan jumlah per status serta tombol ekspor ke CSV.
- **Halaman Detail Rekap / Timeline Pengajuan**, menampilkan riwayat kronologis lengkap satu pengajuan mulai dari waktu pengajuan masuk, keputusan verifikasi, penerbitan surat, penetapan tanggal pengambilan, hingga scan QR selesai.

_Sisipkan Gambar 3.13 Rancangan Antarmuka Admin/Petugas Desa._
