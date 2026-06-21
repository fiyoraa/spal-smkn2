# Walkthrough: Tata Letak Utama (Main Layout) Vue 3 - Anti-Gravity

Implementasi tata letak utama dengan konsep Anti-Gravity telah berhasil diselesaikan pada proyek Vue 3 ini. Konsep visual diwujudkan melalui perpaduan latar belakang mesh gradient pastel yang dianimasikan secara halus dengan panel Sidebar dan Top Navbar berbasis glassmorphic transparan berukuran sudut membulat lebar 24px dan bayangan drop-shadow yang sangat lembut. Seluruh tipografi menggunakan font modern Plus Jakarta Sans yang dioptimalkan sejak proses pemuatan awal pada halaman HTML.

Komponen [MainLayout.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/MainLayout.vue) bertindak sebagai bingkai utama yang membagi area kerja menjadi Sidebar navigasi di sisi kiri dan Top Navbar di bagian atas. Sidebar ini memuat seluruh menu navigasi yang diminta mulai dari Dashboard hingga Blacklist, di mana setiap item menu dilengkapi efek hover transisi melayang ke atas, pembesaran skala mikro, dan pancaran cahaya berpendar yang sangat responsif. Di bagian atas Sidebar terdapat ornamen logo futuristik berupa bola bersinar yang melayang di tengah cincin orbit berputar 3D.

Top Navbar mendukung interaksi dinamis seperti kolom pencarian yang memanjang saat aktif, lencana status melayang, popover drop-down notifikasi glassmorphic untuk menampilkan pesan simulasi, serta kartu dropdown profil pengguna. Halaman [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) mengintegrasikan layout ini dengan menyediakan area konten mockup interaktif yang mewakili setiap menu navigasi.

--------------------------------------------------

Update: Penambahan Komponen Dashboard Laboran

Komponen [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) telah berhasil dibuat dan diintegrasikan ke dalam tata letak utama pada menu Dashboard di [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue). Di bagian atas, komponen ini menampilkan empat kartu statistik yang interaktif untuk Total Alat, Alat Tersedia, Sedang Dipinjam, dan Peminjaman Aktif, yang masing-masing dihiasi dengan warna gradien pastel lembut serta efek bayangan drop-shadow yang melayang ke atas setinggi lima piksel saat di-hover oleh kursor pengguna. Di bagian bawah, tata letak dua kolom memisahkan panel daftar peminjaman aktif di sebelah kiri dan grafik statistik mingguan di sebelah kanan.

Kolom kiri memuat tabel peminjaman aktif tanpa garis pembatas kaku yang memanfaatkan jarak pemisah baris untuk merepresentasikan baris tabel berbentuk pill melengkung penuh, lengkap dengan efek transisi pendar putih bersinar dan pengangkatan bayangan halus ketika baris tersebut disorot. Kolom kanan memuat statistik peminjaman bulan ini yang dirancang dengan panel glassmorphic berisi visualisasi grafik batang vertikal berwarna gradasi dengan sudut atas membulat halus serta tooltip informasi interaktif yang memudar muncul saat disentuh. Seluruh komponen baru ini terus berjalan aktif dan responsif di server pengembangan lokal http://localhost:5173/ untuk divalidasi secara manual oleh Anda.

--------------------------------------------------

Update: Pembuatan Halaman Katalog & Form Peminjaman User

Komponen [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue) and [FormPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/FormPeminjaman.vue) telah dikembangkan secara khusus untuk melayani alur pengajuan peminjaman alat di sisi siswa dan guru. Katalog alat disajikan dalam struktur tata letak grid, di mana setiap kartu alat didesain melayang ringan dengan tampilan simbol gambar yang terpusat di tengah kartu serta dilengkapi efek hover pengangkatan dan perputaran gambar secara mikro. Status ketersediaan alat ditunjukkan menggunakan lencana berwarna pastel estetik, seperti warna hijau pastel lembut yang menyala untuk ketersediaan alat berstatus Tersedia.

Formulir pengajuan peminjaman diimplementasikan dalam komponen terpisah bernama FormPeminjaman.vue dengan menghindari bentuk input box kotak standar yang kaku. Formulir ini menggunakan gaya label melayang yang otomatis bergeser ke atas saat area input diklik atau terisi data, serta menampilkan garis bawah bercahaya gradien lembut yang melebar secara penuh saat status input sedang aktif fokus. Field input mencakup pemilih jumlah unit alat, datepicker minimalis untuk tanggal peminjaman dan pengembalian, serta area input keperluan peminjaman yang luas tanpa garis tepi yang kaku. Seluruh alur pengajuan ditutup dengan tombol kirim bertipe kapsul membulat berwarna gradien biru modern yang memiliki bayangan memancar lembut di bagian bawahnya, dan terintegrasi penuh ke dalam server lokal di http://localhost:5173/.

--------------------------------------------------

Update: Pembuatan Halaman Validasi Pengembalian Alat Laboran

Komponen [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue) telah berhasil dibuat dan diintegrasikan ke dalam menu Pengembalian pada [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue). Halaman ini ditujukan untuk antarmuka laboran yang memadukan tiga fungsionalitas utama di dalam estetika Anti-Gravity. Di bagian atas, area pemindai QR Code dirancang dengan panel glassmorphic yang memiliki pembingkai kamera bersudut tumpul dan ornamen bidik sudut menyala biru-cyan yang berdenyut secara perlahan, serta dilengkapi animasi laser scanning horizontal menyapu vertikal untuk mensimulasikan pembacaan data.

Di bawah area pemindai, panel split-pane membagi area input kondisi alat menjadi dua kolom untuk kondisi saat dipinjam di sebelah kiri dan kondisi saat dikembalikan di sebelah kanan tanpa adanya pembatas vertikal yang kaku. Pada masing-masing kolom kondisi disediakan area drag-and-drop unggah foto bersudut melengkung halus dengan garis putus-putus berwarna indigo yang berpendar terang ketika terdeteksi drag-over, disertai pilihan dropdown status kondisi dan area catatan masukan yang melayang. Terintegrasi pula di dalamnya kartu peringatan denda keterlambatan berwarna merah pastel glassmorphic yang otomatis bergeser muncul ke atas ketika terdeteksi data peminjaman yang melewati batas waktu, di mana sistem menghitung denda harian sebesar sepuluh ribu rupiah dikali jumlah hari keterlambatan dan menampilkan total denda dalam format nominal rupiah tebal berwarna merah menyala. Seluruh fungsionalitas simulasi ini dapat diuji secara interaktif di server lokal http://localhost:5173/.

--------------------------------------------------

Update: Inisialisasi Backend Laravel, Skema Migrasi Database, dan Data Seeders/Factories

Pembangunan backend menggunakan Laravel telah berhasil diinisialisasi dalam subdirektori [backend](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend) dengan konfigurasi koneksi MySQL ke database `db_spal` di berkas [.env](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/.env). Skema basis data disusun melalui berkas-berkas migrasi yang mencakup tabel [users](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/0001_01_01_000000_create_users_table.php) dengan tambahan kolom `role` (Laboran, Guru, Siswa), [equipment](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/2026_06_17_164618_create_equipment_table.php), [borrowing_requests](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/2026_06_17_164619_create_borrowing_requests_table.php), [transactions](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/2026_06_17_164620_create_transactions_table.php), [condition_records](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/2026_06_17_164621_create_condition_records_table.php), [fines](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/2026_06_17_164622_create_fines_table.php), dan [blacklist_managers](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/migrations/2026_06_17_164623_create_blacklist_managers_table.php).

Logika pengisian data dummy diatur melalui seeder secara terstruktur di mana [UserSeeder](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/seeders/UserSeeder.php) membuat tepat tiga akun pengguna default (Laboran, Guru, Siswa) untuk kebutuhan pengujian login, [EquipmentSeeder](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/seeders/EquipmentSeeder.php) menghasilkan lima puluh data inventaris alat acak yang terbagi rata pada kategori laptop, proyektor, kamera, dan tripod, serta [TransactionSeeder](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/seeders/TransactionSeeder.php) membuat tiga puluh data riwayat dan transaksi aktif peminjaman yang terhubung langsung dengan pengguna Siswa/Guru default dan mencatat denda otomatis untuk status keterlambatan. Seluruh skema migrasi dan seeder berhasil dieksekusi dan divalidasi ke basis data MySQL `db_spal` melalui perintah migrasi artisan tanpa adanya kesalahan kendala integritas data.

--------------------------------------------------

Update: Pembangunan Backend Laravel dengan Arsitektur 4-Layer

Sistem backend Laravel telah diperluas dengan menerapkan arsitektur 4-layer secara ketat guna memisahkan tanggung jawab kode secara profesional. Pada Presentation Layer, dibuat controller [EquipmentApiController](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Http/Controllers/Api/EquipmentApiController.php) and [BorrowingApiController](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Http/Controllers/Api/BorrowingApiController.php) yang dihubungkan ke berkas [api.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/routes/api.php) untuk melayani permintaan API inventaris dan transaksi peminjaman. Logika bisnis diisolasi sepenuhnya pada Business Logic Layer menggunakan [QrCodeService](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Services/QrCodeService.php) untuk memformat penomoran QR alat, [BorrowingService](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Services/BorrowingService.php) untuk memproses siklus peminjaman beserta kalkulasi denda otomatis, dan [NotificationService](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Services/NotificationService.php) untuk menganalisis pengingat jatuh tempo H-1 dan peringatan keterlambatan. Seluruh operasi basis data MySQL dan simulasi pengiriman notifikasi Email/WhatsApp ditangani pada Data Access Layer oleh [UserRepository](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Repositories/UserRepository.php), [EquipmentRepository](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Repositories/EquipmentRepository.php), [BorrowingRepository](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Repositories/BorrowingRepository.php), [TransactionRepository](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Repositories/TransactionRepository.php), [FineRepository](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Repositories/FineRepository.php), dan [NotificationGateway](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Repositories/NotificationGateway.php). Scheduler harian ditangani oleh berkas perintah artisan [SendBorrowingReminders](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Console/Commands/SendBorrowingReminders.php) yang terdaftar di [console.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/routes/console.php). Fungsionalitas sistem 4-layer ini telah diverifikasi secara penuh dan lolos uji coba integrasi menggunakan perintah [TestWorkflow](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Console/Commands/TestWorkflow.php) di terminal lokal.

--------------------------------------------------

Update: Perombakan Layout dengan Bento Box Grid dan Navigasi Horizontal Melayang

Struktur tata letak utama aplikasi telah dirombak secara menyeluruh untuk menghilangkan kesan template dasbor klasik yang kaku. Pada berkas [MainLayout.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/MainLayout.vue), sidebar kiri dihapus sepenuhnya dan digantikan oleh Navigasi Atas Melayang (Floating Top Navigation) yang terpusat di tengah dengan sudut membulat, efek glassmorphic transparan, dan bayangan tipis yang elegan. Layout ini juga mengintegrasikan fitur Command Palette (MacOS Spotlight style) yang dapat diakses instan melalui tombol pencarian di navbar atau pintasan keyboard Ctrl + K untuk memfasilitasi pencarian menu navigasi dan pergantian tab secara interaktif. Berkas [style.css](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/style.css) telah diperbarui dengan spasi whitespace yang sangat longgar, kontras tipografi yang ekstrem (judul hero tebal berukuran besar disandingkan deskripsi tipis yang kecil), serta kelas utilitas grid Bento Box. Halaman utama di [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) disusun ulang secara asimetris menggunakan Bento Box Grid, yang memposisikan kartu pahlawan (Hero Card) berukuran besar 2x2 untuk memantau detail Peminjaman Aktif di samping widget statistik statis berukuran kecil dan grafik tren mingguan di sekitarnya. Seluruh pembaruan visual ini berjalan aktif dan lancar di server pengembangan lokal.

--------------------------------------------------

Update: Penyempurnaan Skema Warna Aksen Biru Baja, Penataan Floating Menu Navigasi, dan Eliminasi Animasi Berlebih

Aplikasi telah disempurnakan lebih lanjut dengan membatasi Floating Top Navigation di berkas [MainLayout.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/MainLayout.vue) hanya untuk 5 menu utama yaitu Dashboard, Katalog, Approval, Pengembalian, dan Riwayat dengan memetakan menu Katalog ke tab Peminjaman, sedangkan menu Data Alat, Laporan, dan Blacklist tetap dapat diakses secara penuh melalui pencarian Command Palette. Seluruh skema warna aksen biru standar dan bayangan biru lama di berkas [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue), [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue), [FormPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/FormPeminjaman.vue), dan [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue) telah dikonversi secara menyeluruh ke warna aksen korporat biru baja (steel blue) yang tenang dan profesional serta menghapus seluruh efek hover translasi translateY yang berlebihan demi kenyamanan pengguna dari mabuk gerakan (motion sickness). Selain itu, komponen [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue) telah dibersihkan dari animasi garis laser scanner yang bergerak naik-turun secara berulang dan efek putaran rotasi pada ikon kamera pemindai, sementara seluruh kelas pewarnaan indigo lama telah dieliminasi dan digantikan dengan integrasi warna aksen biru baja sistem yang secara otomatis berhasil dikompilasi oleh server pengembangan lokal tanpa adanya kendala atau peringatan eror.

--------------------------------------------------

Update: Penyatuan Kartu Statistik Bento, Reduksi Teks Deskripsi, dan Tata Letak Tabel yang Bernapas

Halaman utama di berkas [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) telah disempurnakan secara radikal untuk mengatasi kendala penumpukan kartu dan kepadatan teks. Empat buah kartu statistik terpisah kini telah dilebur ke dalam satu kartu penampung berukuran besar (Hero Stats Card) di bagian atas grid yang menyusun empat indikator metrik utama (Total Alat, Alat Tersedia, Sedang Dipinjam, Peminjaman Aktif) secara berdampingan dengan memanfaatkan ruang kosong whitespace yang proporsional dan tanpa dibatasi garis kaku. Hierarki tipografi ekstrem diterapkan secara tegas pada seluruh metrik ini dengan memperbesar ukuran angka hingga sangat besar dan tebal berukuran 3.75rem, serta memperkecil judul metrik menjadi kecil, tipis, dan berwarna abu-abu redup tanpa deskripsi teks tambahan. Selain itu, baris-baris pada tabel Peminjaman Aktif telah ditata ulang agar lebih bernapas dengan menerapkan padding vertikal longgar sebesar 20px 24px pada kelas baris kartu tabel serta menghapus teks awalan yang berulang secara redundan seperti kata 'Tempo:' guna melahirkan desain visual modern kelas premium yang tenang, bersih, dan lapang.

--------------------------------------------------

Update: Penyelarasan Desain Semantik Sesuai DESIGN.md dan Rombak Interaksi Tactile Digital

Seluruh sistem gaya dasar di berkas [style.css](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/style.css) telah diselaraskan 100% untuk mematuhi panduan desain mutlak di [DESIGN.md](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/DESIGN.md) dengan mendefinisikan variabel warna semantik --color-bg-base (#F9FAFB), --color-surface (rgba(255,255,255,0.7) dengan backdrop-blur 12px untuk efek Liquid Glass), --color-text-primary (#1E293B), --color-text-secondary (#64748B), dan --color-action-primary (#2563EB) sebagai sumber kebenaran tunggal yang terintegrasi penuh. Pada komponen [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) dan [MainLayout.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/MainLayout.vue), interaksi digital taktil (Tactile Digital) dirombak untuk memberikan efek menekan ke dalam berupa transform scale(0.98) saat diklik serta transform scale(0.99)/scale(1.02) melayang yang cepat dengan durasi transisi 0.2s ease-out pada seluruh tombol aksi dan baris tabel. Kontainer interaktif seperti tombol verifikasi denda, tab menu navigasi utama, tombol pencarian spotlight, pemicu notifikasi, profil pengguna, dan tombol item Command Palette telah diubah bentuk radius sudutnya menjadi kapsul dinamis (pill-shaped / rounded-full) serta membersihkan sisa border kaku 1px abu-abu abu yang membosankan untuk sepenuhnya mengandalkan visual whitespace yang legah dan kompilasi hot-reload di server pengembangan berjalan lancar tanpa hambatan.

--------------------------------------------------

Update: Perombakan Total Antarmuka PengembalianAlat.vue dengan Progressive Disclosure dan Kontrol HITL

Antarmuka [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue) telah dirombak secara total untuk mematuhi pedoman desain mutlak dalam [DESIGN.md](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/DESIGN.md) dengan menerapkan alur Progressive Disclosure 2 langkah yang bersih. Pada langkah pertama, ketika pemindaian belum selesai, layar hanya menyajikan modul Kamera Scanner QR Code minimalis di tengah dengan ruang kosong whitespace yang sangat lega di sekelilingnya serta simulator pemilih antrean berbentuk kapsul membulat tanpa border. Setelah data peminjaman ditemukan, transisi meluncur ke bawah (slide-down) yang mulus akan menampilkan Form Split-Pane untuk membandingkan kondisi awal serah terima dan kondisi pengembalian fisik alat secara interaktif. Logika kontrol manual Human-in-the-Loop (HITL) diintegrasikan pada panel denda keterlambatan di mana total denda dihitung otomatis harian dan ditampilkan secara kontras ekstrim menggunakan font 'Instrument Serif' italic merah bata super raksasa, yang mewajibkan laboran mengklik tombol persetujuan denda dengan efek klik tactile scale(0.98) sebelum tombol utama "Setujui Pengembalian" aktif terbuka. Selain itu, area drag-and-drop unggah foto bukti kerusakan serta catatan rincian kerusakan disembunyikan secara bawaan dan hanya akan terbuka melalui pengungkapan bertahap jika laboran memilih kondisi pengembalian selain 'Sangat Baik' atau 'Baik' guna menjaga kebersihan visual antarmuka kelas enterprise.

--------------------------------------------------

Update: Perombakan Total Antarmuka ApprovalPeminjaman.vue dengan Slide-over Panel dan Indikator Kepercayaan HITL

Komponen persetujuan peminjaman baru telah diimplementasikan dalam berkas [ApprovalPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/ApprovalPeminjaman.vue) dan diintegrasikan secara penuh pada [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) untuk membuang layout tabel kaku yang membosankan. Halaman ini menggunakan konsep Progressive Disclosure berupa daftar pengajuan minimalis (List View) dengan jarak spasi whitespace yang sangat longgar untuk menampilkan Nama Peminjam, Nama Alat, dan Tanggal secara bersih tanpa colored left border. Ketika salah satu baris pengajuan diklik oleh laboran, panel detail samping kanan (Slide-over panel) akan meluncur masuk secara mulus dari sisi kanan layar untuk menyajikan rincian lengkap pengajuan beserta Indikator Kepercayaan (Trust Indicator) kontekstual berupa informasi riwayat peminjaman siswa (contoh teks abu-abu: 'Siswa ini belum pernah terlambat' atau teks merah bata: 'Siswa ini pernah terlambat 1 kali') untuk mendukung pengambilan keputusan laboran. Di dalam panel detail disediakan tombol ganda taktil 'Setujui' berwarna biru korporat dan 'Tolak' berwarna transparan dengan teks merah yang memiliki umpan balik fisik scale(0.98) selama 0.2 detik saat diklik, di mana penolakan secara dinamis membuka textarea minimalis tanpa border kaku (animasi ekspansi ke bawah) yang mewajibkan laboran mengisikan alasan penolakan sebelum konfirmasi dikirimkan.

--------------------------------------------------

Update: Konfigurasi Aksesibilitas Jaringan Lokal untuk Server Pengembangan Vite

Berkas konfigurasi [vite.config.js](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/vite.config.js) telah diperbarui dengan menambahkan opsi server host yang bernilai true di dalam blok pendefinisian konfigurasi. Pembaruan ini menginstruksikan server pengembangan Vite untuk mengikat koneksi pada seluruh kartu antarmuka jaringan yang tersedia (0.0.0.0) alih-alih hanya localhost saja. Setelah berkas konfigurasi disimpan, server Vite secara otomatis melakukan restart dan kini mempublikasikan aplikasi pada alamat IP lokal di jaringan Wi-Fi (misalnya http://192.168.1.16:5173/) agar dapat diakses, diuji, dan divalidasi kinerjanya secara langsung dari perangkat lain seperti ponsel pintar atau laptop lain yang berada pada satu jaringan nirkabel yang sama.

--------------------------------------------------

Update: Perombakan Antarmuka Katalog & Form Peminjaman dengan Archival Index dan Progressive Disclosure

Komponen katalog alat di [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue) dan formulir pengajuan di [FormPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/FormPeminjaman.vue) telah dirombak secara menyeluruh untuk mematuhi DESIGN.md. Di area katalog, desain kartu tradisional bermodel kotak putih berbayang dan bersudut membulat seragam dibuang sepenuhnya untuk menerapkan estetika Archival Index murni berupa daftar baris (index rows) yang minimalis dan teratur. Konten dipisahkan menggunakan ruang kosong whitespace yang sangat lega dan garis batas (border) 1px yang sangat tipis dan redup. Judul kategori menggunakan tipografi raksasa (oversized) menggunakan font Geist dan judul halaman memakai Instrument Serif sebagai elemen jangkar visual yang elegan. Pencarian adaptif dan saran pintar (Ambient AI) diintegrasikan lewat chip-chip rekomendasi dinamis yang memandu pemilihan alat. Di sisi formulir, diterapkan alur Progressive Disclosure 2 langkah yang interaktif: ketika alat diklik, pertama-tama panel hanya menyajikan kalender ketersediaan jadwal visual untuk bulan Juni 2026. Baru setelah pengguna mengklik dan memilih tanggal peminjaman dan pengembalian yang valid pada grid kalender, input form untuk Keperluan Peminjaman dan Jumlah Unit memapar meluas ke bawah (slide-down) dengan transisi mulus. Area form dirancang borderless dengan efek pendar garis bawah (focus glow) saat input aktif dan dilengkapi visual status hover, focus, disabled, error, serta empty yang mematuhi pedoman secara global. Selain itu, detail teknis alat (seperti ID, Lokasi, dan Kategori) disembunyikan secara bawaan, dan hanya akan meluncur dan memudar muncul (progressive detail disclosure) ketika baris alat disorot (hover) atau diklik oleh pengguna. Seluruh baris list dan tombol aksi diatur memiliki respons klik mekanis fisik scale(0.98) yang instan dalam 0.2 detik guna memberikan umpan balik taktil digital yang memuaskan.

--------------------------------------------------

Update: Perombakan Total Desain Dashboard dengan Gaya Visual Stripe dan Notion

Halaman dashboard utama laboran di [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) telah dirombak secara total untuk mengadopsi perpaduan gaya visual Stripe yang presisi dan Notion yang hangat serta minimalis. Semua desain kartu putih kaku bersudut membulat lebar dengan box-shadow tebal telah dibuang sepenuhnya. Sebagai gantinya, konten dibingkai secara asimetris menggunakan grid bento borderless yang dipisahkan oleh garis batas 1px yang sangat tipis dan redup (`border: 1px solid rgba(26,26,26,0.08)`) dengan jarak padding dan margin yang ekstra luas dan longgar. Latar belakang disetel menggunakan warna krem pucat (`#F9F7F3`) dengan teks berwarna gelap charcoal (`#1A1A1A`). Judul halaman didesain dengan kontras tinggi menggunakan font `Instrument Serif` italic yang anggun, sedangkan nilai-nilai metrik statistik numerik disajikan dalam ukuran raksasa (oversized) dengan font `Geist` yang presisi. Baris peminjaman aktif kini tersaji sebagai daftar baris index bersih tanpa background box berbayang, dengan umpan balik taktil digital membal (`transform: scale(0.98)`) selama 0.2 detik ketika diklik atau disorot oleh laboran.

--------------------------------------------------

Update: Perombakan MainLayout.vue, Autentikasi Cardless Asimetris, dan Komponen Tabel Archival Index

Tata letak utama di berkas [MainLayout.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/MainLayout.vue) telah dirombak sepenuhnya menjadi gaya Archival Index dengan menghapus seluruh box-shadow and memosisikan navigasi atas secara fixed penuh di bagian atas layar tanpa sudut membulat, menyatu langsung dengan latar belakang off-white hangat, serta dipisahkan hanya oleh garis bawah 1px tipis yang pudar. Tipografi logo diperbarui menggunakan font *Instrument Serif* bergaya miring (*italic*) berukuran oversized, sementara baris menu tautan navigasi dirombak menggunakan transisi garis bawah kinetik 0.2 detik tanpa menggunakan kotak background hover. Halaman autentikasi baru [Login.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/Login.vue) dan [Register.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/Register.vue) telah dibuat secara asimetris di 1/3 kiri layar dengan whitespace 2/3 kanan masif, tanpa adanya kotak pembungkus (cardless) berbayang, menggunakan judul sapaan raksasa *Instrument Serif*, dan input bergaris bawah tipis yang menebal taktil ke warna aksen Steel Blue saat aktif. Komponen tabel riwayat peminjaman baru [RiwayatPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/RiwayatPeminjaman.vue) telah dibuat dan diintegrasikan di [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) untuk menggantikan timeline lama, sementara halaman Laporan di [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) diperkaya dengan tabel Utilisasi Inventaris. Kedua tabel dirancang tanpa batas vertikal kaku, dengan padding vertikal longgar 20px, teks utama dark slate, kolom angka monospace, dan badge status kapsul kecil tebal berwarna redup. Skema warna global di [style.css](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/style.css) telah diperbarui dengan mengubah aksen biru Tailwind ke Steel Blue `#46708F` dan solid background krem hangat, yang berhasil dikompilasi secara penuh dalam build produksi.

--------------------------------------------------

Update: Audit Global Warna Aksen Biru dan Pembersihan Kode CSS

Pembersihan menyeluruh terhadap warna biru Tailwind bawaan (seperti `37, 99, 235` dan `#1d4ed8`) telah berhasil diaudit dan diperbaiki di seluruh berkas komponen frontend utama, termasuk [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue), [ApprovalPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/ApprovalPeminjaman.vue), [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue), [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue), dan [FormPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/FormPeminjaman.vue). Seluruh warna biru standar Tailwind dan hover-nya diubah secara manual ke variasi warna aksen Steel Blue (`#46708F` / `rgba(70, 112, 143...)`) guna menjaga harmoni desain 'Nature Distilled' secara mutlak di seluruh aplikasi. Hasil peninjauan build produksi melalui Vite berjalan sukses dan bersih dari peringatan eror.

--------------------------------------------------

Update: Perombakan Total Antarmuka ApprovalPeminjaman.vue dengan Estetika Stripe-Notion Cardless

Komponen persetujuan peminjaman [ApprovalPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/ApprovalPeminjaman.vue) telah dirombak secara menyeluruh melalui Self-Audit AI Slop yang ketat untuk mengadopsi gaya visual minimalis dengan kontras tinggi ala Stripe, Linear, dan Notion. Seluruh tumpukan kartu putih membulat berbayang tebal di daftar tunggu (`.list-item`), riwayat peminjaman (`.history-card`), dan penunjuk kepercayaan (`.trust-indicator-card`) telah dihancurkan sepenuhnya. Konten kini menyatu langsung dengan latar belakang off-white (`var(--color-bg-base)`) dan dipisahkan hanya menggunakan ruang kosong whitespace yang masif serta garis batas tipis 1px redup (`rgba(0, 0, 0, 0.04)`).

Di samping itu, slide-over panel samping kanan diatur bebas bayangan (box-shadow: none) and hanya dibatasi oleh garis tepi kiri `1px` yang tipis. Tipografi judul panel dan halaman kini memanfaatkan font display `Instrument Serif` bergaya miring (*italic*) berukuran oversized untuk memandu mata laboran secara elegan. Masukan penolakan (`.minimalist-textarea`) dirancang borderless dengan transisi kinetic garis bawah (underline grow) menggunakan bayangan Steel Blue `#46708F` saat dalam status aktif fokus (focus). Tombol-tombol juga disesuaikan dengan interaksi taktil digital responsif dan skema warna Nature Distilled.

--------------------------------------------------

Update: Penyelarasan Tipografi Geist & Sentence/Title Case di ApprovalPeminjaman.vue

Komponen [ApprovalPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/ApprovalPeminjaman.vue) telah disempurnakan lebih lanjut dengan mengeliminasi total format all-caps (huruf kapital semua) pada seluruh elemen label bagian (`.detail-label`, `.trust-label`, `.textarea-label`, `.section-title-wrapper h3`, dan `.badge-pill`). Format label kini diubah menjadi Title Case atau Sentence Case secara konsisten agar antarmuka tidak terkesan sebagai hasil template generik (AI Slop).

Selain itu, seluruh label, penunjuk status (badge), dan tombol aksi utama/sekunder (Setujui, Tolak, Batal, Kirim Penolakan) kini menggunakan font berkarakter kuat `Geist` (diambil dari variabel `--font-number`) menggantikan font default `Inter`. Seluruh tombol aksi mempertahankan efek klik taktil scale(0.98) kinetik 0.2 detik tanpa warna gradien atau pendar glow, menghasilkan visual kontras tinggi minimalis yang sangat matang dan profesional.

--------------------------------------------------

Update: Integrasi Real Camera QR Scanner (html5-qrcode) di Pengembalian Alat dan Peminjaman

Logika pemindaian simulasi QR code telah digantikan sepenuhnya dengan pemindai kamera fisik nyata menggunakan pustaka `html5-qrcode` pada halaman Pengembalian Alat [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue) dan Katalog Peminjaman [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue).

1. **PengembalianAlat.vue**:
   - Simulator pemilih antrean dan simulator tombol scan dihapus secara total.
   - Viewfinder dirancang minimalis tanpa drop-shadow dengan ornamen pembingkai reticle 1px yang tipis.
   - Izin akses kamera diminta secara elegan kepada pengguna melalui tombol "Aktifkan Kamera".
   - Setelah kamera aktif dan diarahkan ke kode QR peminjaman (misal ID `PEM-201`), data peminjaman terkait langsung termuat secara otomatis dan membuka form langkah kedua.
   - Memberikan umpan balik audio berupa bunyi bip (*Web Audio API*) serta getaran taktil (*navigator.vibrate*) ketika pemindaian sukses.

2. **PeminjamanUser.vue**:
   - Ditambahkan tombol ikon scan QR di samping bar pencarian alat.
   - Ketika diklik, panel viewfinder kamera kustom meluncur turun secara progresif di bawah search bar.
   - Jika QR Code Alat (misal ID `ALT-101`) sukses terbaca, item alat akan langsung otomatis terpilih (`selectItem`) dan menggulirkan halaman ke formulir peminjaman jika status ketersediaannya terpenuhi.
   - Menyertakan umpan balik bip audio dan getaran taktil sukses pembacaan.

--------------------------------------------------

Update: Otomatisasi Pembuatan ID, Generasi QR Code Asli, dan Integrasi Lintas Halaman Real Scanner

1. **Otomatisasi Backend & Keamanan Unik Database**:
   - Berkas [QrCodeService.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Services/QrCodeService.php) diubah untuk menghasilkan format kode ID standardisasi `SMKN2-LAB-[Kategori]-[Tahun]-[NoUrut]` dengan kategori dalam huruf kapital (UPPERCASE) dan tahun berbasis tahun berjalan secara dinamis.
   - Mengatasi potensi bug kegagalan *unique constraint* database pada [EquipmentApiController.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Http/Controllers/Api/EquipmentApiController.php) dengan menyematkan string penanda waktu mikro (microtime) dan angka acak unik di kode temporer sebelum proses pemutakhiran final dilakukan.

2. **Integrasi Generasi QR Code di Frontend (Offline Fallback & Preview)**:
   - Memasang pustaka `qrcode` di frontend dan memanfaatkannya pada berkas [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) untuk memproduksi Data URL gambar QR asli secara real-time pada fallback luring (offline) dan modul preview "Tambah Alat Baru" ketika laboran menekan tombol Simpan.

3. **Sinkronisasi Katalog Lintas Halaman**:
   - Meneruskan data reaktif `items` dari parent [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) sebagai prop `:items` ke dalam komponen [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue), sehingga alat-alat baru yang berhasil ditambahkan oleh laboran langsung muncul seketika di katalog peminjaman siswa.

4. **Multi-Resolusi Pencocokan Real Scanner**:
   - Memutakhirkan logika pemindaian di [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue) dengan ornamen bidik viewfinder 1px bersih tanpa bayangan (sesuai `DESIGN.md`).
   - Logika pencocokan diubah agar laboran bisa memindai langsung kode QR pada fisik alat (seperti `ALT-xxx` atau `SMKN2-LAB-...`) maupun ID transaksi (`PEM-xxx`) untuk memicu verifikasi pengembalian dan denda keterlambatan secara instan.

--------------------------------------------------

Update: Penyusunan Database Seeder Massal untuk Keperluan Demo

1. **Pembuatan 55 Data Alat Laboratorium Komputer**:
   - Berkas [EquipmentSeeder.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/seeders/EquipmentSeeder.php) diubah untuk membuat 55 data alat komputer secara realistis (seperti Laptop Lenovo ThinkPad, Switch Cisco, Tang Crimping, dll.) dan memanggil `QrCodeService` untuk menghasilkan ID otomatis dengan format standardisasi `SMKN2-LAB-[Kategori]-[Tahun]-[NoUrut]` beserta gambar Base64 QR code aslinya.

2. **Pembuatan Data Peminjam & Riwayat Transaksi Massal**:
   - Memperbarui [UserSeeder.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/seeders/UserSeeder.php) untuk memproduksi 10 data siswa dan 5 data guru dengan nama realistis.
   - Memperbarui [TransactionSeeder.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/database/seeders/TransactionSeeder.php) untuk menata 35 transaksi peminjaman (20 selesai tepat waktu, 5 selesai terlambat, 5 aktif tepat waktu, dan 5 aktif terlambat).

3. **Skenario Uji Denda Terlambat**:
   - Membuat 10 transaksi terlambat (5 aktif berstatus 'Terlambat' dan 5 selesai berstatus 'Kembali' yang terlambat) dengan rentang waktu delay 2 hingga 5 hari serta kalkulasi otomatis nominal denda Rp 10.000 per hari, sehingga denda akumulasi (Rp 20.000 hingga Rp 50.000) terbit secara otomatis.

--------------------------------------------------

Update: Integrasi Akses Peran (RBAC), Pemisahan Alur Kerja (Workflows), dan Sinkronisasi Database Riil

Aplikasi SPAL SMKN 2 Palembang kini telah sepenuhnya terintegrasi secara dinamis dengan database Laravel backend, membedakan fungsionalitas dan alur proses secara terperinci untuk peran **Laboran (Admin)**, **Guru**, dan **Siswa**.

1. **Autentikasi Riil & Persistensi Sesi**:
   - Halaman [Login.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/Login.vue) kini memanggil endpoint `POST /api/login` untuk memvalidasi kredensial pengguna terhadap database (akun uji seperti `laboran@school.id`, `guru@school.id`, `siswa@school.id` dengan password `password`).
   - Halaman [Register.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/Register.vue) dilengkapi dengan dropdown pilihan peran (Siswa, Guru, Laboran) dan terhubung langsung ke `POST /api/register` untuk menyimpan data akun baru ke database.
   - Status login (`isLoggedIn`) dan data pengguna aktif (`currentUser`) disimpan di `localStorage` dan secara otomatis dimuat ulang oleh [App.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/App.vue) saat halaman di-refresh, mencegah pengguna terlempar kembali ke layar login.

2. **Diferensiasi Alur Proses & Menu Navigasi**:
   - Menu navigasi pada Floating Top Navbar di [MainLayout.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/MainLayout.vue) disaring secara dinamis berdasarkan peran pengguna:
     - **Laboran**: Memiliki hak akses penuh ke menu Dashboard, Data Alat, Approval, Pengembalian, Riwayat, Laporan, dan Blacklist.
     - **Guru & Siswa**: Hanya memiliki akses ke menu Dashboard, Katalog (Peminjaman), dan Riwayat.

3. **Dashboard Adaptif**:
   - Komponen [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) telah diubah menjadi dasbor adaptif:
     - **Tampilan Laboran**: Menyajikan total statistik global (Total Alat, Alat Dipinjam), antrean peminjaman aktif sistem, log pemberitahuan, log aktivitas terpusat, dan grafik transaksi laboratorium.
     - **Tampilan Guru & Siswa**: Menyajikan statistik personal (Alat Dipinjam Saya, Jumlah Pengajuan Pending Saya, Akumulasi Denda Saya yang terlambat), daftar peminjaman aktif pribadi, dan status terbaru pengajuan peminjaman mereka sendiri.

4. **Automasi Siklus Transaksi Lintas Peran**:
   - **Reservasi Alat**: Pada [PeminjamanUser.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PeminjamanUser.vue) dan [FormPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/FormPeminjaman.vue), pengajuan peminjaman kini dikirimkan sebagai `POST /api/borrowing/request` ke backend menggunakan ID pengguna dan ID database alat.
   - **Persetujuan (Approval)**: Pada [ApprovalPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/ApprovalPeminjaman.vue), Laboran memuat daftar pengajuan dari `/api/borrowings?status=Pending`. Pilihan 'Setujui' akan memanggil `POST /api/borrowing/approve/{id}` untuk membuat transaksi aktif, sementara 'Tolak' memanggil `POST /api/borrowing/reject/{id}`. Indikator Kepercayaan (Trust Indicator) dihitung secara riil berdasarkan jumlah keterlambatan pengguna di database.
   - **Pengembalian & Denda**: Pada [PengembalianAlat.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/PengembalianAlat.vue), daftar transaksi aktif ditarik dari `/api/transactions?status=Dipinjam`. Saat kamera sukses memindai kode QR alat atau ID peminjaman, sistem melakukan verifikasi denda secara otomatis. Submit pengembalian dikirimkan ke `POST /api/borrowing/return` untuk mengembalikan alat ke status "Tersedia".

5. **Optimasi Kinerja Seeder**:
   - Metode `generateQrImage()` di [QrCodeService.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/app/Services/QrCodeService.php) dioptimalkan dengan menambahkan stream context timeout sebesar 0.5 detik pada pemanggilan API eksternal. Hal ini membuat proses eksekusi seeder (`php artisan db:seed`) selesai dalam waktu singkat dengan beralih cepat ke fallback SVG lokal jika jaringan lambat.

--------------------------------------------------

Update: Pemisahan Struktur Layout Desktop dan Mobile Responsif untuk Dasbor, Approval, dan Riwayat Peminjaman

Telah diselesaikan pemisahan struktural yang menyeluruh antara tampilan Desktop dan Mobile pada tiga komponen utama (Dasbor, Approval, dan Riwayat Peminjaman) agar tidak saling tumpang tindih maupun merusak visual masing-masing viewport.

1. **Dashboard (Dasbor) Adaptif**:
   - Komponen [DashboardLaboran.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/DashboardLaboran.vue) diperbarui dengan mendeteksi ukuran layar secara reaktif (`isMobile`) melalui event listener pada window resize.
   - Memisahkan template root sehingga view mobile dan desktop tidak berada dalam wrapper yang salah (`v-if="!isMobile"` pada root dihapus).
   - Menghapus rendering ganda (duplicate templates) pada markup mobile view.
   
2. **Approval Peminjaman**:
   - Komponen [ApprovalPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/ApprovalPeminjaman.vue) memisahkan tampilan Desktop asimetris (Split-list + Slide-over panel) dari tampilan Mobile minimalis (Tab-switching + Slide-up bottom sheet) untuk kenyamanan layar sentuh.

3. **Riwayat Peminjaman**:
   - Komponen [RiwayatPeminjaman.vue](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/src/components/RiwayatPeminjaman.vue) membagi visualisasi log menjadi Desktop Modern Table (lebar, data lengkap) dan Mobile Stack Cards (vertikal, compact) dengan penanda status denda terintegrasi secara rapi.
   
4. **Verifikasi & Build Sukses**:
    - Menjalankan build produksi (`npm run build`) dengan sukses tanpa adanya warning kompilasi, serta memverifikasi bahwa perubahan berjalan mulus dan responsif di server pengembangan lokal.

--------------------------------------------------

Update: Konfigurasi Deployment Multi-Platform (Vercel & Railway) dan Mekanisme Migrasi Mandiri

Pengaturan integrasi deployment multi-platform telah selesai dikonfigurasi guna memisahkan hosting Vue 3 frontend (Vercel) dan Laravel backend + MySQL database (Railway) dengan mulus tanpa kendala CORS.

1. **Konfigurasi Reverse Proxy Vercel (`vercel.json`)**:
   - Membuat berkas [vercel.json](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/vercel.json) pada root direktori proyek. Konfigurasi ini mendefinisikan aturan rewrite yang mengalihkan seluruh pemanggilan rute `/api/:path*` di frontend ke domain Railway backend. Hal ini mengeliminasi masalah CORS browser karena browser tetap melakukan request ke origin Vercel yang sama.

2. **Rute Migrasi Jarak Jauh Terproteksi (`/api/migrate`)**:
   - Menambahkan rute GET `/api/migrate` pada berkas [api.php](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/backend/routes/api.php). Endpoint ini memfasilitasi jalannya migrasi skema database (`migrate --force`) dan seeding data awal (`db:seed --force`) langsung dari peramban (browser) web, sehingga pengguna tidak perlu menggunakan CLI/terminal jarak jauh. Route dilindungi oleh verifikasi parameter query `key` berbasis variabel lingkungan `MIGRATION_KEY` untuk mencegah akses tidak sah.

3. **Penyusunan Dokumentasi Deployment Terpadu**:
   - Memperbarui berkas [README.md](file:///c:/Users/fihil/OneDrive/Dokumen/from%20yora/Project%20RPL/README.md) dengan menambahkan bagian panduan deployment terperinci. Panduan ini menjelaskan alur pembuatan akun Railway/Vercel, pengisian variabel lingkungan Laravel yang terintegrasi dengan MySQL di Railway, pemetaan domain, dan pengoperasian endpoint migrasi database.
