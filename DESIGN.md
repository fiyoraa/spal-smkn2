# SPAL (Sistem Peminjaman Alat Laboratorium) - Master Design System

Dokumen ini adalah aturan desain mutlak. Dilarang keras menggunakan gaya bawaan AI (seperti gradien ungu-biru, border kiri berwarna, atau tata letak kaku). 

## 1. Aturan Dasar & Kebersihan Visual
- **TIDAK BOLEH ADA EMOJI** di seluruh aplikasi. Gunakan pustaka ikon `Lucide` atau `Phosphor` untuk ikonografi fungsional.
- **Ruang Kosong (Whitespace)**: Gandakan jarak margin dan padding standar. Antarmuka harus terasa lega dan bisa bernapas.
- **Tanpa Garis Tepi Kaku**: Hindari penggunaan border abu-abu 1px yang membosankan. Pisahkan elemen menggunakan ruang kosong atau perbedaan warna latar belakang yang sangat tipis.

## 2. Palet Warna (Sistem Semantik)
Gunakan penamaan semantik untuk variabel CSS/Tailwind, bukan nama warna dekoratif:
- `--color-bg-base`: `#F9FAFB` (Off-white / abu-abu sangat terang untuk latar belakang utama)
- `--color-surface`: `rgba(255, 255, 255, 0.7)` (Putih transparan dengan efek *backdrop-blur* 12px untuk efek *Liquid Glass*)
- `--color-text-primary`: `#1E293B` (Slate dark, abu-abu kehitaman elegan, bukan hitam pekat #000)
- `--color-text-secondary`: `#64748B` (Untuk teks deskripsi/subtitle)
- `--color-action-primary`: `#2563EB` (Biru korporat / Steel Blue)

## 3. Tipografi Kontras Ekstrem
Gunakan font `Plus Jakarta Sans`. Terapkan hierarki yang ekstrem:
- **Judul/Angka Statistik**: Ukuran SANGAT BESAR (misal 3rem/48px ke atas) dengan *font-weight* tebal (Bold/ExtraBold).
- **Teks Paragraf/Label**: Ukuran kecil (14px atau 13px), tipis, dengan warna abu-abu pudar. 
- Jangan gunakan ukuran font yang nanggung atau seragam.

## 4. Tata Letak (Layout) & Struktur
- **Bento Box Asimetris**: Jangan buat susunan kartu yang ukurannya sama persis. Buat tata letak organik di mana elemen paling penting (seperti "Peminjaman Aktif") memiliki kartu yang jauh lebih besar (Hero Card), sementara elemen lain berukuran lebih kecil di sekitarnya.
- **Radius Sudut Dinamis**: Gunakan sudut membulat 24px untuk kontainer luar/kartu besar, dan bentuk kapsul (*pill-shaped* / `rounded-full`) untuk tombol aksi atau baris tabel.

## 5. Gerak & Interaksi (Tactile Digital)
- **Dilarang**: Animasi berdenyut (*pulsing*), berputar, atau *fade-in* yang terlalu lama.
- **Wajib**: Terapkan efek fisika nyata. Saat tombol atau baris tabel di-hover/diklik, berikan efek menekan ke dalam (`transform: scale(0.98)`) atau sedikit melayang dengan durasi transisi cepat `0.2s ease-out`.
- **Status Komponen**: Setiap input dan tombol interaktif WAJIB memiliki gaya visual spesifik untuk status `hover`, `focus` (dengan *underline glow*), `disabled`, `error`, dan `empty` (saat tidak ada data).
