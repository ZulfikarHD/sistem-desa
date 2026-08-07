**BAB III**

**METODE PENELITIAN**

**3.1 Tinjauan Umum**

Kantor Desa Widodaren merupakan salah satu instansi pemerintahan tingkat desa yang menyelenggarakan pelayanan administratif kepada warga, salah satunya berupa penerbitan surat keterangan yang meliputi Surat Keterangan Domisili, Surat Keterangan Kelahiran/Kematian, dan Surat Keterangan Tidak Mampu. Volume pengajuan surat keterangan pada Kantor Desa Widodaren tergolong tinggi, yaitu mencapai kurang lebih 100 pengajuan setiap bulan, dengan Surat Keterangan Domisili sebagai jenis surat yang paling banyak diajukan, disusul Surat Keterangan Kelahiran/Kematian dan Surat Keterangan Tidak Mampu.

Berdasarkan hasil observasi dan wawancara yang telah diuraikan pada Bab I, proses pengajuan surat keterangan pada Kantor Desa Widodaren hingga saat ini masih dilaksanakan secara konvensional. Warga diharuskan datang langsung ke kantor desa untuk mengisi formulir secara manual, sementara pencatatan pengajuan masih menggunakan buku register sehingga data pengajuan rentan hilang, sulit direkapitulasi, dan tidak dapat ditelusuri secara cepat apabila dibutuhkan sewaktu-waktu untuk keperluan pelaporan desa.

Permasalahan utama yang melatarbelakangi penelitian ini bukan terletak pada lamanya waktu pemrosesan surat, melainkan pada ketiadaan media yang dapat menginformasikan persyaratan dokumen secara jelas kepada warga sebelum mengajukan, serta belum tersedianya sistem pencatatan digital yang dapat menggantikan pencatatan manual berbasis buku. Oleh karena itu, penelitian ini difokuskan pada perancangan dan pembangunan Sistem Informasi Pelayanan Surat Keterangan berbasis web dengan memanfaatkan framework Laravel sebagai kerangka kerja utama pengembangan perangkat lunak dan metode Prototyping sebagai pendekatan pengembangan sistem.

**3.2 Objek Penelitian**

Objek penelitian ini adalah proses pelayanan administrasi surat keterangan pada Kantor Desa Widodaren, yang meliputi tahapan pengajuan oleh warga, verifikasi kelengkapan dokumen oleh petugas (Kasi Pelayanan), hingga penerbitan surat oleh pihak berwenang di desa. Pihak-pihak yang berkepentingan (stakeholder) dalam penelitian ini diuraikan pada Tabel 3.1 berikut.

**Tabel 3.1 Stakeholder Penelitian**

| **Stakeholder**                     | **Peran/Kepentingan**                                                         |
| ----------------------------------- | ----------------------------------------------------------------------------- |
| Warga Desa Widodaren                | Pengguna akhir yang mengajukan surat keterangan.                              |
| Petugas/Admin Desa (Kasi Pelayanan) | Mengelola data jenis surat serta memverifikasi kelengkapan dokumen pengajuan. |
| Kepala Desa                         | Penanggung jawab pelayanan publik desa.                                       |
| Peneliti/Pengembang                 | Merancang, membangun, dan menguji sistem yang diusulkan.                      |

**_3.2.1 Sejarah Singkat_**

_Isi dengan sejarah singkat Desa Widodaren (tahun berdiri, asal-usul nama, perkembangan pemerintahan desa) - dapat diperoleh dari profil desa/monografi desa atau wawancara dengan perangkat desa._

**_3.2.2 Visi dan Misi_**

_Isi dengan visi dan misi resmi Desa Widodaren, dapat diperoleh dari dokumen RPJMDes (Rencana Pembangunan Jangka Menengah Desa) atau papan profil di kantor desa._

**_3.2.3 Struktur Organisasi_**

Struktur organisasi Kantor Desa Widodaren terdiri atas Kepala Desa sebagai penanggung jawab pelayanan publik desa, Sekretaris Desa, serta beberapa Kepala Seksi (Kasi) dan Kepala Urusan (Kaur), salah satunya Kasi Pelayanan yang secara langsung menangani proses pengajuan dan verifikasi surat keterangan warga.

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

| **Tahap**                            | **Aktivitas dalam Penelitian**                                                                                                                            |
| ------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1\. Pengumpulan Kebutuhan            | Observasi alur layanan surat dan wawancara dengan Kasi Pelayanan Kantor Desa Widodaren untuk menggali kebutuhan fungsional dan non-fungsional sistem.     |
| 2\. Membangun Prototipe              | Merancang tampilan dan alur awal sistem (Use Case Diagram, ERD, arsitektur sistem, rancangan antarmuka) berdasarkan kebutuhan yang telah dikumpulkan.     |
| 3\. Evaluasi Prototipe oleh Pengguna | Prototipe didiskusikan kembali dengan petugas desa untuk memastikan kesesuaian rancangan dengan kebutuhan lapangan.                                       |
| 4\. Perbaikan Prototipe              | Penyesuaian rancangan (Use Case, ERD, tampilan) dilakukan berdasarkan hasil evaluasi; tahap 3-4 dapat berulang hingga rancangan disepakati oleh pengguna. |
| 5\. Pengkodean Sistem                | Implementasi rancangan final ke dalam kode program menggunakan Laravel 11, MySQL, Blade Templating Engine, dan Tailwind CSS.                              |
| 6\. Pengujian Sistem                 | Pengujian dengan metode Black Box Testing terhadap seluruh fitur utama sesuai skenario pengujian sebagaimana disajikan pada Bab IV.                       |
| 7\. Implementasi                     | Penerapan sistem yang telah diuji pada lingkungan Kantor Desa Widodaren, atau simulasi pada lingkungan pengembangan lokal untuk kebutuhan penelitian.     |

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

| **Kelemahan Sistem Berjalan**                                                                                       | **Solusi yang Diusulkan**                                                                                                      |
| ------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| Warga tidak mengetahui persyaratan dokumen sebelum datang ke kantor desa sehingga harus kembali lagi.               | Sistem menyediakan informasi persyaratan dokumen untuk setiap jenis surat sebelum warga melakukan pengajuan.                   |
| Pencatatan pengajuan surat masih menggunakan buku register sehingga rentan hilang, rusak, dan sulit direkapitulasi. | Sistem menyediakan pencatatan digital yang tersimpan pada basis data MySQL, sehingga data lebih aman dan mudah direkapitulasi. |
| Warga tidak memperoleh informasi mengenai status pengajuan surat yang telah diajukan.                               | Sistem menyediakan fitur notifikasi status pengajuan secara in-app yang dapat dipantau warga secara real-time.                 |
| Proses verifikasi dan pencatatan yang seluruhnya manual meningkatkan beban kerja petugas dan risiko human error.    | Sistem menyediakan halaman verifikasi bagi Admin/petugas desa untuk memeriksa kelengkapan dokumen secara digital.              |

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

**_3.5.2.2 Kebutuhan Perangkat Lunak_**

Kebutuhan perangkat lunak yang digunakan dalam pembangunan sistem diuraikan pada Tabel 3.5 berikut.

**Tabel 3.5 Kebutuhan Perangkat Lunak**

| **Layer**                     | **Teknologi**                           | **Keterangan**                                                         |
| ----------------------------- | --------------------------------------- | ---------------------------------------------------------------------- |
| Backend Framework             | Laravel 11 (PHP 8.2+)                   | MVC, routing, Eloquent ORM, validasi, middleware                       |
| Frontend/Templating           | Blade Templating Engine                 | Bawaan Laravel untuk merender tampilan sisi server                     |
| Styling                       | Tailwind CSS                            | Utility-first CSS untuk mempercepat pembangunan antarmuka responsif    |
| Interaktivitas Ringan         | Alpine.js                               | Dropdown notifikasi dan modal konfirmasi tanpa build SPA yang kompleks |
| Basis Data                    | MySQL / MariaDB                         | Basis data relasional, kompatibel dengan XAMPP/phpMyAdmin              |
| Autentikasi                   | Laravel Breeze                          | Starter kit login/register beserta middleware role Warga/Admin         |
| Manajemen Berkas              | Laravel Filesystem (Storage)            | Unggah dan penyimpanan berkas KTP/KK                                   |
| Notifikasi In-App             | Laravel Notification (Database Channel) | Notifikasi status pengajuan tersimpan pada tabel notifikasi            |
| Kontrol Versi                 | Git dan GitHub                          | Pengelolaan versi kode program                                         |
| Lingkungan Pengembangan Lokal | XAMPP / Laravel Herd / Laragon          | Web server, PHP, dan MySQL lokal untuk keperluan pengembangan          |

Pemilihan Laravel sebagai framework utama didasarkan pada beberapa pertimbangan, yaitu: (1) menerapkan pola Model View Controller (MVC) yang memisahkan logika bisnis, tampilan, dan data secara jelas; (2) Eloquent ORM mempermudah implementasi relasi antartabel sesuai rancangan basis data tanpa banyak penulisan kueri SQL secara manual; (3) tersedianya fitur bawaan seperti migration, seeder, validation, dan notification yang mempercepat proses pengembangan pada skala penelitian dengan waktu terbatas; serta (4) dokumentasi resmi yang lengkap dan komunitas pengguna yang besar sehingga memudahkan proses debugging.

**_3.5.2.3 Kebutuhan Informasi_**

Kebutuhan informasi atau keluaran (output) yang disediakan oleh sistem yang diusulkan meliputi:

- Informasi persyaratan dokumen untuk setiap jenis surat keterangan, agar warga dapat mempersiapkan berkas sebelum mengajukan.
- Informasi status pengajuan surat (diajukan, diproses, disetujui, atau ditolak) yang dapat dipantau warga secara mandiri.
- Riwayat pengajuan surat milik masing-masing warga sebagai bentuk transparansi layanan.
- Rekapitulasi data pengajuan surat bagi petugas/Admin desa sebagai pengganti pencatatan buku manual.
- Notifikasi perubahan status pengajuan disampaikan secara in-app secara real-time kepada warga melalui dashboard.

**_3.5.2.4 Kebutuhan Pengguna (User)_**

Sistem yang diusulkan melibatkan dua peran (role) pengguna sebagaimana disajikan pada Tabel 3.6 berikut.

**Tabel 3.6 Kebutuhan Pengguna**

| **Peran**                           | **Hak Akses**                                                                                                                                             |
| ----------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Warga                               | Registrasi akun, melihat persyaratan dokumen, mengajukan surat keterangan, mengunggah dokumen persyaratan, serta memantau status dan riwayat pengajuan.   |
| Admin/Petugas Desa (Kasi Pelayanan) | Mengelola data jenis surat beserta persyaratannya, memverifikasi atau menolak kelengkapan dokumen pengajuan, serta mengelola rekapitulasi data pengajuan. |

**_3.5.3 Analisis Kelayakan Sistem_**

Analisis kelayakan dilakukan untuk menilai apakah sistem yang diusulkan layak untuk dikembangkan dan diterapkan di Kantor Desa Widodaren, ditinjau dari empat aspek berikut.

**_3.5.3.1 Kelayakan Teknologi_**

Secara teknologi, sistem yang diusulkan dinilai layak karena menggunakan framework Laravel yang bersifat open-source, memiliki dokumentasi yang luas, serta umum digunakan pada penelitian rekayasa perangkat lunak sejenis. Kebutuhan perangkat pendukung seperti basis data MySQL juga tersedia dengan biaya rendah dan mudah diperoleh, sementara perangkat keras yang dibutuhkan baik untuk pengembangan maupun akses pengguna tergolong perangkat standar yang umum tersedia.

**_3.5.3.2 Kelayakan Hukum_**

Secara hukum, sistem yang diusulkan tidak melanggar ketentuan yang berlaku karena seluruh perangkat lunak yang digunakan merupakan perangkat lunak open-source dengan lisensi bebas digunakan untuk kepentingan pengembangan maupun penelitian. Data pribadi warga yang diunggah, seperti Kartu Tanda Penduduk (KTP) dan Kartu Keluarga (KK), disimpan dan dikelola khusus untuk kebutuhan verifikasi pelayanan administrasi desa sesuai kewenangan Kantor Desa Widodaren.

**_3.5.3.3 Kelayakan Operasional_**

Secara operasional, sistem ini layak diterapkan karena warga maupun petugas desa telah terbiasa menggunakan peramban (browser) dalam kesehariannya, sehingga adopsi sistem dapat dilakukan tanpa memerlukan pelatihan yang intensif. Petugas pelayanan (Kasi Pelayanan) sebagai pengguna utama sistem juga memiliki kemampuan dasar mengoperasikan komputer sehingga siap menjalankan proses verifikasi melalui sistem.

**_3.5.3.4 Kelayakan Ekonomi_**

Secara ekonomi, biaya pengembangan sistem relatif minim karena seluruh perangkat lunak yang digunakan bersifat open-source dan tidak memerlukan lisensi berbayar. Biaya operasional pasca-implementasi hanya berupa biaya hosting dan domain yang bersifat opsional, karena untuk kebutuhan penelitian skripsi sistem dapat dijalankan pada lingkungan pengembangan lokal (localhost), sebagaimana disajikan pada Tabel 3.7 berikut.

**Tabel 3.7 Estimasi Biaya Implementasi**

| **Komponen**                                          | **Estimasi Biaya**                           |
| ----------------------------------------------------- | -------------------------------------------- |
| Perangkat lunak (Laravel, PHP, MySQL, XAMPP, VS Code) | Rp0 (open-source)                            |
| Hosting dan domain (per tahun, opsional)              | \[isi sesuai penyedia hosting yang dipilih\] |
| Biaya pengembang (jika ada)                           | Tidak ada, dikerjakan mandiri oleh peneliti  |

**3.6 Perancangan Sistem**

Perancangan sistem meliputi perancangan arsitektur, perancangan proses, perancangan basis data, dan perancangan antarmuka (interface) yang menjadi acuan dalam tahap implementasi pada Bab IV.

**_3.6.1 Arsitektur Sistem_**

Penjelasan tiap lapisan (layer) pada arsitektur sistem adalah sebagai berikut.

- Client Layer, yaitu antarmuka berbasis Blade View yang ditata menggunakan Tailwind CSS, dengan interaktivitas ringan menggunakan Alpine.js, misalnya pada dropdown notifikasi in-app.
- Application Layer, yaitu lapisan yang berisi Routes, Middleware (autentikasi dan role Warga/Admin), Controller, Form Request (validasi input), Service/Logic Layer, dan Eloquent Model.
- Data Layer, yaitu MySQL sebagai basis data relasional utama, serta Laravel Storage untuk menyimpan berkas dokumen persyaratan berupa KTP dan KK.

Alur permintaan (request flow) pada sistem secara ringkas adalah sebagai berikut: (1) warga atau admin mengakses aplikasi melalui peramban, permintaan HTTP diterima oleh Routes; (2) Middleware memeriksa status login dan role pengguna; (3) Controller memproses permintaan dan memanggil Form Request untuk validasi input; (4) Controller meneruskan logika bisnis ke Service Layer; (5) Service Layer berinteraksi dengan Eloquent Model untuk membaca atau menulis data ke basis data melalui ORM; (6) berkas dokumen pengajuan disimpan melalui Laravel Storage; (7) untuk notifikasi, Service Layer menyimpan notifikasi in-app ke basis data agar dapat ditampilkan secara real-time pada dashboard warga; dan (8) Controller mengembalikan respons berupa halaman Blade kepada peramban pengguna.

**_3.6.2 Perancangan Proses_**

Perancangan proses digambarkan menggunakan pendekatan berorientasi objek melalui Unified Modeling Language (UML), yaitu use case diagram dan activity diagram sebagaimana telah diuraikan pada Bab II.

**_1\. Use Case Diagram_**

Aktor yang terlibat dalam sistem terdiri atas Warga dan Admin/Petugas Desa, dengan daftar use case sebagaimana disajikan pada Tabel 3.8.

**Tabel 3.8 Deskripsi Use Case**

| **Use Case**                     | **Aktor**    | **Deskripsi Singkat**                                                            |
| -------------------------------- | ------------ | -------------------------------------------------------------------------------- |
| Registrasi Akun                  | Warga        | Warga mendaftarkan akun baru dengan data diri (NIK, nama, no. telepon, alamat).  |
| Login                            | Warga, Admin | Autentikasi pengguna berdasarkan role sebelum mengakses dashboard.               |
| Lihat Persyaratan Dokumen        | Warga        | Menampilkan daftar dokumen persyaratan untuk tiap jenis surat sebelum pengajuan. |
| Ajukan Surat Keterangan          | Warga        | Warga mengisi form pengajuan surat sesuai jenis yang dipilih.                    |
| Unggah Dokumen Persyaratan       | Warga        | Include dari Ajukan Surat; warga mengunggah KTP/KK sebagai syarat.               |
| Lihat Status & Riwayat Pengajuan | Warga        | Menampilkan status terkini dan riwayat seluruh pengajuan milik warga.            |
| Terima Notifikasi In-App         | Warga        | Warga menerima notifikasi perubahan status pengajuan di dalam aplikasi.          |
| Kelola Data Jenis Surat          | Admin        | Admin menambah/mengubah data jenis surat beserta persyaratannya.                 |
| Verifikasi Pengajuan Surat       | Admin        | Admin memeriksa kelengkapan dokumen dan menyetujui/menolak pengajuan.            |
| Kelola Data Pengajuan (Rekap)    | Admin        | Admin melihat rekap seluruh pengajuan sebagai pengganti pencatatan buku manual.  |

**_2\. Activity Diagram_**

Activity Diagram digunakan untuk menggambarkan alur kerja proses utama pada sistem. Berikut diuraikan alur aktivitas pada dua proses utama, yaitu proses pengajuan surat oleh warga dan proses verifikasi pengajuan oleh admin.

**a. Activity Diagram Pengajuan Surat oleh Warga**

Alur dimulai ketika warga melakukan login ke dalam sistem, kemudian memilih jenis surat keterangan yang akan diajukan. Sistem menampilkan informasi persyaratan dokumen untuk jenis surat tersebut. Warga selanjutnya mengisi formulir pengajuan beserta keperluan surat, kemudian mengunggah dokumen persyaratan (KTP dan/atau KK) sesuai jenis surat yang dipilih. Sistem melakukan validasi kelengkapan input; apabila data belum lengkap, sistem menampilkan pesan kesalahan dan warga diminta melengkapi kembali. Apabila data telah lengkap, sistem menyimpan data pengajuan dengan status "diajukan" dan menghasilkan nomor pengajuan unik.


_Gambar 3.4 Activity Diagram Pengajuan Surat oleh Warga._

**b. Activity Diagram Verifikasi Pengajuan oleh Admin**

Alur dimulai ketika admin login ke dalam sistem dan membuka daftar pengajuan yang berstatus "diajukan". Admin memeriksa kelengkapan dan kesesuaian dokumen yang diunggah warga. Apabila dokumen dinyatakan lengkap dan sesuai, admin menyetujui pengajuan sehingga status berubah menjadi "disetujui"; apabila tidak sesuai, admin menolak pengajuan disertai catatan alasan penolakan sehingga status berubah menjadi "ditolak". Pada kedua kondisi tersebut, sistem secara otomatis mencatat log verifikasi dan mengirimkan notifikasi perubahan status kepada warga.


_Gambar 3.5 Activity Diagram Verifikasi Pengajuan oleh Admin._

**_3.6.3 Perancangan Basis Data_**

Perancangan basis data digambarkan melalui Entity Relationship Diagram (ERD) yang menunjukkan entitas beserta relasinya, kemudian diimplementasikan ke dalam struktur tabel pada MySQL. Entitas utama yang terlibat dalam sistem meliputi users, jenis_surat, pengajuan_surat, dokumen_persyaratan, notifikasi, dan log_verifikasi.

_Gambar 3.6 Entity Relationship Diagram (ERD) Sistem Informasi Pelayanan Surat Keterangan._

Rancangan struktur tabel basis data hasil dari ERD tersebut disajikan pada Tabel 3.9 sampai dengan Tabel 3.14.

**Tabel 3.9 Struktur Tabel users**

| **Kolom**               | **Tipe Data**         | **Keterangan**                 |
| ----------------------- | --------------------- | ------------------------------ |
| id                      | BIGINT (PK, AI)       | Primary key                    |
| nik                     | VARCHAR(16)           | Nomor Induk Kependudukan warga |
| nama                    | VARCHAR(100)          | Nama lengkap pengguna          |
| email                   | VARCHAR(100), UNIQUE  | Email untuk login              |
| password                | VARCHAR(255)          | Kata sandi terenkripsi         |
| no_telepon              | VARCHAR(20)           | Nomor telepon warga            |
| alamat                  | TEXT                  | Alamat lengkap warga           |
| role                    | ENUM('warga','admin') | Peran pengguna                 |
| created_at / updated_at | TIMESTAMP             | Waktu pembuatan/pembaruan data |

**Tabel 3.10 Struktur Tabel jenis_surat**

| **Kolom**               | **Tipe Data**   | **Keterangan**                        |
| ----------------------- | --------------- | ------------------------------------- |
| id                      | BIGINT (PK, AI) | Primary key                           |
| nama_surat              | VARCHAR(100)    | Nama jenis surat keterangan           |
| deskripsi               | TEXT            | Deskripsi singkat kegunaan surat      |
| persyaratan_dokumen     | TEXT            | Daftar dokumen yang wajib dilampirkan |
| created_at / updated_at | TIMESTAMP       | Waktu pembuatan/pembaruan data        |

**Tabel 3.11 Struktur Tabel pengajuan_surat**

| **Kolom**               | **Tipe Data**                                     | **Keterangan**                 |
| ----------------------- | ------------------------------------------------- | ------------------------------ |
| id                      | BIGINT (PK, AI)                                   | Primary key                    |
| user_id                 | BIGINT (FK)                                       | Relasi ke users (pemohon)      |
| jenis_surat_id          | BIGINT (FK)                                       | Relasi ke jenis_surat          |
| nomor_pengajuan         | VARCHAR(30)                                       | Nomor unik pengajuan           |
| keperluan               | TEXT                                              | Tujuan penggunaan surat        |
| status                  | ENUM('diajukan','diproses','disetujui','ditolak') | Status pengajuan               |
| catatan_admin           | TEXT (nullable)                                   | Catatan/alasan dari admin      |
| diverifikasi_oleh       | BIGINT (FK, nullable)                             | Relasi ke users (admin)        |
| tanggal_pengajuan       | DATE                                              | Tanggal pengajuan dibuat       |
| created_at / updated_at | TIMESTAMP                                         | Waktu pembuatan/pembaruan data |

**Tabel 3.12 Struktur Tabel dokumen_persyaratan**

| **Kolom**               | **Tipe Data**    | **Keterangan**                 |
| ----------------------- | ---------------- | ------------------------------ |
| id                      | BIGINT (PK, AI)  | Primary key                    |
| pengajuan_id            | BIGINT (FK)      | Relasi ke pengajuan_surat      |
| jenis_dokumen           | ENUM('KTP','KK') | Jenis dokumen yang diunggah    |
| file_path               | VARCHAR(255)     | Path penyimpanan berkas        |
| created_at / updated_at | TIMESTAMP        | Waktu pembuatan/pembaruan data |

**Tabel 3.13 Struktur Tabel notifikasi**

| **Kolom**    | **Tipe Data**          | **Keterangan**             |
| ------------ | ---------------------- | -------------------------- |
| id           | BIGINT (PK, AI)        | Primary key                |
| user_id      | BIGINT (FK)            | Relasi ke users (penerima) |
| pengajuan_id | BIGINT (FK)            | Relasi ke pengajuan_surat  |
| pesan        | TEXT                   | Isi pesan notifikasi       |
| status_baca  | ENUM('dibaca','belum') | Status baca notifikasi     |
| created_at   | TIMESTAMP              | Waktu notifikasi dibuat    |

**Tabel 3.14 Struktur Tabel log_verifikasi**

| **Kolom**    | **Tipe Data**           | **Keterangan**                             |
| ------------ | ----------------------- | ------------------------------------------ |
| id           | BIGINT (PK, AI)         | Primary key                                |
| pengajuan_id | BIGINT (FK)             | Relasi ke pengajuan_surat                  |
| admin_id     | BIGINT (FK)             | Relasi ke users (admin yang memverifikasi) |
| aksi         | ENUM('setujui','tolak') | Aksi verifikasi yang dilakukan             |
| keterangan   | TEXT (nullable)         | Alasan/catatan tambahan                    |
| created_at   | TIMESTAMP               | Waktu aksi dilakukan                       |

**_3.6.4 Perancangan Antarmuka (Interface)_**

Perancangan antarmuka bertujuan menggambarkan rancangan halaman-halaman utama pada sistem sebelum diimplementasikan ke dalam kode program. Rancangan antarmuka dibagi menjadi dua kelompok berdasarkan pengguna, yaitu antarmuka untuk Warga dan antarmuka untuk Admin/Petugas Desa.

**a. Rancangan Antarmuka Warga**

- Halaman Registrasi dan Login, berisi formulir pendaftaran akun (NIK, nama, no. telepon, alamat, email, kata sandi) serta formulir masuk ke dalam sistem.
- Halaman Beranda/Dashboard Warga, menampilkan ringkasan status pengajuan terbaru serta daftar jenis surat yang dapat diajukan.
- Halaman Persyaratan Dokumen, menampilkan daftar dokumen yang wajib dilampirkan untuk masing-masing jenis surat.
- Halaman Form Pengajuan Surat, berisi pilihan jenis surat, keperluan pengajuan, serta area unggah berkas KTP/KK.
- Halaman Status dan Riwayat Pengajuan, menampilkan status terkini beserta riwayat seluruh pengajuan milik warga.
- Panel Notifikasi In-App, menampilkan daftar notifikasi perubahan status pengajuan.


_Gambar 3.7 Rancangan Antarmuka Warga._

**b. Rancangan Antarmuka Admin/Petugas Desa**

- Halaman Login Admin, berbagi formulir masuk dengan pembeda role pada proses autentikasi.
- Halaman Dashboard Admin, menampilkan ringkasan jumlah pengajuan berdasarkan status.
- Halaman Kelola Jenis Surat, digunakan untuk menambah/mengubah data jenis surat beserta persyaratan dokumennya.
- Halaman Verifikasi Pengajuan, menampilkan detail data pengajuan beserta pratinjau berkas yang diunggah warga, dilengkapi tombol setujui/tolak beserta kolom catatan.
- Halaman Rekap Pengajuan, menampilkan seluruh data pengajuan yang dapat difilter berdasarkan jenis surat, status, maupun rentang tanggal, sebagai pengganti pencatatan buku manual.


_Gambar 3.8 Rancangan Antarmuka Admin/Petugas Desa._

**BAB IV**

**HASIL DAN PEMBAHASAN**

**4.1 Hasil Penelitian**

Bagian ini menguraikan hasil implementasi Sistem Informasi Pelayanan Surat Keterangan Berbasis Web pada Kantor Desa Widodaren berdasarkan rancangan yang telah diuraikan pada Bab III. Implementasi dibangun menggunakan framework Laravel 11, Blade Templating Engine, Tailwind CSS, dan basis data MySQL, mengikuti tahapan pengkodean pada metode Prototyping.

**_4.1.1 Implementasi Antarmuka Warga_**

**1\. Halaman Registrasi dan Login**

Menampilkan formulir pendaftaran akun warga (NIK, nama, no. telepon, alamat, email, kata sandi) serta formulir masuk berdasarkan role.

_Sisipkan Gambar 4.1 Halaman Registrasi dan Login._

**2\. Halaman Dashboard Warga**

Menampilkan ringkasan status pengajuan terbaru serta akses cepat menuju daftar jenis surat.

_Sisipkan Gambar 4.2 Halaman Dashboard Warga._

**3\. Halaman Persyaratan Dokumen**

Menampilkan daftar dokumen yang wajib dilampirkan untuk masing-masing jenis surat sebelum warga mengajukan permohonan.

_Sisipkan Gambar 4.3 Halaman Persyaratan Dokumen._

**4\. Halaman Form Pengajuan Surat**

Menampilkan formulir pengajuan surat beserta area unggah berkas KTP/KK sesuai jenis surat yang dipilih.

_Sisipkan Gambar 4.4 Halaman Form Pengajuan Surat._

**5\. Halaman Status dan Riwayat Pengajuan**

Menampilkan status terkini (diajukan/diproses/disetujui/ditolak) beserta riwayat seluruh pengajuan milik warga.

_Sisipkan Gambar 4.5 Halaman Status dan Riwayat Pengajuan._

**6\. Panel Notifikasi In-App**

Menampilkan daftar notifikasi perubahan status pengajuan yang diterima warga.

_Sisipkan Gambar 4.6 Panel Notifikasi In-App._

**_4.1.2 Implementasi Antarmuka Admin/Petugas Desa_**

**1\. Halaman Dashboard Admin**

Menampilkan ringkasan jumlah pengajuan berdasarkan status sebagai gambaran umum beban kerja pelayanan.

_Sisipkan Gambar 4.7 Halaman Dashboard Admin._

**2\. Halaman Kelola Jenis Surat**

Menampilkan daftar jenis surat beserta fitur tambah/ubah data persyaratan dokumen untuk setiap jenis surat.

_Sisipkan Gambar 4.8 Halaman Kelola Jenis Surat._

**3\. Halaman Verifikasi Pengajuan**

Menampilkan detail data pengajuan beserta pratinjau berkas yang diunggah warga, dilengkapi aksi setujui/tolak beserta kolom catatan admin.

_Sisipkan Gambar 4.9 Halaman Verifikasi Pengajuan._

**4\. Halaman Rekap Pengajuan**

Menampilkan seluruh data pengajuan yang dapat difilter berdasarkan jenis surat, status, maupun rentang tanggal pengajuan, sebagai bahan pelaporan bagi petugas dan Kepala Desa.

_Sisipkan Gambar 4.10 Halaman Rekap Pengajuan._

**4.2 Pembahasan**

Pembahasan pada bagian ini menguraikan hasil pengujian sistem menggunakan metode black box testing, membahas alur sistem secara menyeluruh, serta mengaitkan hasil implementasi dengan rumusan masalah dan tujuan penelitian yang telah ditetapkan pada Bab I.

**_4.2.1 Pembahasan Alur Sistem_**

Berdasarkan hasil implementasi, sistem yang dibangun telah menjawab rumusan masalah yang diuraikan pada Bab I. Fitur informasi persyaratan dokumen memungkinkan warga mengetahui dokumen yang wajib dilampirkan sebelum mengajukan permohonan, sehingga diharapkan dapat mengurangi kunjungan berulang ke kantor desa akibat berkas yang belum lengkap. Fitur pengajuan dan pencatatan digital menggantikan pencatatan manual berbasis buku register, sehingga data pengajuan tersimpan pada basis data MySQL yang lebih tertib, aman, dan mudah direkapitulasi. Fitur notifikasi status pengajuan secara in-app memberikan kepastian informasi kepada warga mengenai perkembangan permohonan surat yang diajukan secara real-time melalui dashboard, tanpa memerlukan integrasi aplikasi pihak ketiga.

Dari sisi petugas, halaman verifikasi pengajuan mempermudah proses pemeriksaan kelengkapan dokumen serta pencatatan hasil verifikasi melalui tabel log_verifikasi, sehingga setiap keputusan persetujuan maupun penolakan pengajuan dapat ditelusuri kembali. Halaman rekap pengajuan turut membantu petugas dalam melakukan pelaporan berkala tanpa perlu merekapitulasi data secara manual dari buku register.

**_4.2.2 Pengujian Sistem (Black Box Testing)_**

Pengujian dilakukan terhadap fungsi-fungsi utama sistem, meliputi registrasi, login, informasi persyaratan dokumen, pengajuan surat, unggah dokumen, verifikasi oleh petugas, notifikasi, pengelolaan data jenis surat, dan rekapitulasi pengajuan, tanpa menguji struktur kode program secara internal. Skenario dan hasil pengujian disajikan pada Tabel 4.1 berikut.

**Tabel 4.1 Hasil Pengujian Sistem dengan Black Box Testing**

| **No** | **Skenario Pengujian**                                     | **Hasil yang Diharapkan**                                                         | **Hasil Pengujian** |
| ------ | ---------------------------------------------------------- | --------------------------------------------------------------------------------- | ------------------- |
| 1      | Registrasi akun warga dengan data lengkap dan valid        | Akun berhasil dibuat dan warga dapat login ke sistem                              | Sesuai harapan      |
| 2      | Registrasi akun dengan NIK yang sudah terdaftar            | Sistem menolak dan menampilkan pesan kesalahan                                    | Sesuai harapan      |
| 3      | Login dengan email dan kata sandi yang benar               | Pengguna berhasil masuk sesuai role (Warga/Admin)                                 | Sesuai harapan      |
| 4      | Login dengan kata sandi yang salah                         | Sistem menampilkan pesan kesalahan dan menolak akses                              | Sesuai harapan      |
| 5      | Melihat persyaratan dokumen pada suatu jenis surat         | Sistem menampilkan daftar dokumen persyaratan sesuai jenis surat                  | Sesuai harapan      |
| 6      | Mengajukan surat dengan seluruh berkas persyaratan lengkap | Data pengajuan tersimpan dengan status "diajukan" dan nomor pengajuan diterbitkan | Sesuai harapan      |
| 7      | Mengajukan surat tanpa mengunggah dokumen wajib            | Sistem menampilkan pesan validasi dan menolak penyimpanan data                    | Sesuai harapan      |
| 8      | Warga melihat status dan riwayat pengajuan                 | Sistem menampilkan status terkini beserta riwayat pengajuan yang sesuai           | Sesuai harapan      |
| 9      | Admin memverifikasi dan menyetujui pengajuan               | Status pengajuan berubah menjadi "disetujui" dan tercatat pada log_verifikasi     | Sesuai harapan      |
| 10     | Admin menolak pengajuan disertai catatan                   | Status pengajuan berubah menjadi "ditolak" beserta catatan alasan penolakan       | Sesuai harapan      |
| 11     | Sistem mengirim notifikasi in-app setelah status berubah   | Notifikasi baru muncul pada panel notifikasi warga                                | Sesuai harapan      |
| 12     | Admin menambah data jenis surat baru                       | Data jenis surat baru tersimpan dan tampil pada daftar jenis surat                | Sesuai harapan      |
| 13     | Admin melihat rekap pengajuan dengan filter status         | Sistem menampilkan data pengajuan sesuai filter yang dipilih                      | Sesuai harapan      |

Berdasarkan hasil pengujian pada Tabel 4.1, seluruh skenario pengujian terhadap fitur utama sistem - meliputi registrasi, autentikasi, informasi persyaratan dokumen, pengajuan surat, unggah dokumen, verifikasi oleh admin, notifikasi status, serta pengelolaan data jenis surat dan rekapitulasi pengajuan - menunjukkan hasil yang sesuai dengan harapan. Dengan demikian, secara fungsional sistem yang dibangun telah berjalan sesuai dengan rancangan yang diuraikan pada Bab III.

_Catatan: Kolom "Hasil Pengujian" pada Tabel 4.1 perlu disesuaikan dengan hasil pengujian aktual (Sesuai harapan/Tidak sesuai) setelah implementasi sistem selesai diuji langsung pada lingkungan pengembangan atau bersama pengguna di Kantor Desa Widodaren._

**_4.2.3 Pembahasan Hasil terhadap Rumusan Masalah_**

Merujuk pada rumusan masalah yang telah ditetapkan pada Bab I, hasil implementasi sistem dapat diuraikan sebagai berikut.

1\. Sistem yang dibangun menyediakan halaman informasi persyaratan dokumen untuk setiap jenis surat keterangan yang dapat diakses warga sebelum mengajukan surat, sehingga menjawab rumusan masalah pertama mengenai kebutuhan informasi persyaratan yang jelas.

2\. Seluruh data pengajuan tersimpan dalam basis data MySQL menggantikan pencatatan manual berbasis buku, sehingga data lebih tertib, aman dari risiko kehilangan fisik, dan mudah direkapitulasi melalui halaman rekap pengajuan, menjawab rumusan masalah kedua.

3\. Sistem menyediakan fitur status pengajuan yang dapat dipantau warga secara langsung melalui dashboard, dilengkapi notifikasi in-app secara real-time, sehingga menjawab rumusan masalah ketiga mengenai transparansi status pengajuan.

Berdasarkan uraian tersebut, sistem yang dibangun secara umum telah menjawab permasalahan yang diidentifikasi pada Bab I, meskipun beberapa penyempurnaan masih dapat dilakukan sebagaimana diuraikan pada bagian saran.

**BAB V**

**PENUTUP**

**5.1 Kesimpulan**

Berdasarkan hasil perancangan, pembangunan, dan pengujian Sistem Informasi Pelayanan Surat Keterangan Berbasis Web dengan Metode Prototyping di Desa Widodaren, dapat ditarik kesimpulan sebagai berikut.

1\. Sistem informasi pelayanan surat keterangan berbasis web berhasil dirancang dan dibangun menggunakan framework Laravel, yang menyediakan informasi persyaratan dokumen secara jelas untuk setiap jenis surat, sehingga warga dapat mengetahui dokumen yang wajib disiapkan sebelum mengajukan permohonan.

2\. Sistem yang dibangun mampu memfasilitasi proses pendaftaran akun, pengunggahan berkas persyaratan, dan pengajuan surat secara digital, sehingga menggantikan pencatatan manual berbasis buku register yang sebelumnya digunakan oleh Kantor Desa Widodaren.

3\. Mekanisme verifikasi pengajuan surat oleh petugas desa telah dirancang melalui halaman verifikasi yang dilengkapi pencatatan log_verifikasi, sehingga proses persetujuan maupun penolakan pengajuan dapat berjalan lebih tertib, terstruktur, dan dapat ditelusuri kembali.

4\. Sistem mampu mengurangi ketergantungan terhadap pencatatan manual serta mempermudah proses rekapitulasi data pengajuan surat bagi petugas desa melalui fitur rekap pengajuan yang dapat difilter berdasarkan jenis surat, status, dan rentang tanggal.

5\. Hasil pengujian menggunakan metode Black Box Testing terhadap fitur-fitur utama sistem menunjukkan bahwa seluruh fungsi yang diuji telah berjalan sesuai dengan spesifikasi kebutuhan yang dirancang pada Bab III.

_Catatan: Sesuaikan kalimat kesimpulan di atas dengan hasil pengujian aktual pada Tabel 4.1 - jika ada skenario yang tidak sesuai/butuh perbaikan, sebutkan secara jujur di sini atau di bagian saran._

**5.2 Saran**

Berdasarkan hasil penelitian yang telah dilaksanakan, terdapat beberapa saran yang dapat menjadi bahan pertimbangan untuk pengembangan sistem lebih lanjut, di antaranya:

1\. Pengembangan sistem selanjutnya dapat menambahkan integrasi notifikasi WhatsApp menggunakan WhatsApp Business API resmi, mengingat pada penelitian ini notifikasi WhatsApp berbasis tautan click-to-chat (wa.me) tidak lagi digunakan, agar pengiriman notifikasi status pengajuan dapat dilakukan secara otomatis tanpa keterlibatan manual dari petugas.

2\. Sistem dapat dikembangkan lebih lanjut dengan menambahkan fitur pembubuhan tanda tangan elektronik pada surat keterangan yang diterbitkan, guna meningkatkan keabsahan dokumen secara digital.

3\. Cakupan jenis surat keterangan yang dilayani dapat diperluas melebihi tiga jenis surat yang menjadi batasan pada penelitian ini, sesuai dengan kebutuhan administrasi Kantor Desa Widodaren yang terus berkembang.

4\. Aspek keamanan sistem, seperti enkripsi data, audit trail yang lebih menyeluruh, dan pengujian keamanan (security testing), perlu dikaji lebih mendalam pada penelitian selanjutnya mengingat sistem ini mengelola data kependudukan warga.

5\. Perlu dilakukan pengujian usability, misalnya menggunakan metode System Usability Scale (SUS), untuk mengukur tingkat kepuasan dan kemudahan penggunaan sistem oleh warga maupun petugas desa secara lebih objektif.