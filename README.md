# Read-Assist

Aplikasi layanan akses belajar mandiri berbasis web dengan fitur QR-Audio dan manajemen katalog audio buku untuk mendukung penyandang tunanetra.

Mahasiswa Teknologi Informasi  
Muhammad Almuwarisin

---

## Gambaran Umum

Read-Assist merupakan aplikasi web berbasis Laravel yang dirancang untuk membantu penyandang tunanetra memperoleh akses belajar yang lebih mudah melalui teknologi audio digital dan QR Code.

Sistem ini memungkinkan pengguna memindai QR Code menggunakan smartphone untuk langsung membuka dan memutar audio buku secara praktis.

Aplikasi dikembangkan dengan pendekatan antarmuka sederhana, responsif, dan fokus pada aksesibilitas pengguna.

---

## Tujuan Pengembangan

- Membangun sistem akses belajar berbasis web untuk tunanetra
- Mengimplementasikan framework Laravel dalam pengembangan aplikasi
- Mengintegrasikan QR Code dengan audio digital
- Mengelola katalog buku dan audio secara terstruktur
- Menghasilkan sistem informasi dengan pendekatan aksesibilitas
- Menerapkan arsitektur MVC Laravel

---

## Teknologi yang Digunakan

<p align="center">

<img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
<img src="https://img.shields.io/badge/PHP-8-777BB4?style=for-the-badge&logo=php&logoColor=white" />
<img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
<img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
<img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" />
<img src="https://img.shields.io/badge/Blade_Template-F55247?style=for-the-badge&logo=laravel&logoColor=white" />
<img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" />
<img src="https://img.shields.io/badge/QR_Code-000000?style=for-the-badge&logo=qrcode&logoColor=white" />

</p>

---

## Fitur Aplikasi

- Upload buku digital
- Upload audio buku
- Generate QR Code otomatis
- Audio player terintegrasi
- Manajemen katalog buku
- Dashboard admin
- Scan QR menggunakan smartphone
- Desain responsif
- Arsitektur MVC Laravel

---

## Antarmuka Aplikasi

Antarmuka aplikasi dirancang dengan fokus pada:

- Aksesibilitas pengguna tunanetra
- Navigasi sederhana
- Tampilan responsif
- Tata letak modern
- Fokus pada kemudahan akses audio
- Integrasi mobile access melalui QR Code

Aplikasi dapat diakses melalui route utama Laravel.

---

## Struktur Proyek

```bash
Read-Assist/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
└── vendor/
```

---

## Cara Menjalankan Aplikasi

```bash
git clone https://github.com/Ayries18/Read-Assist.git
cd Read-Assist
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
```

Aplikasi berjalan pada:

```bash
http://127.0.0.1:8000
```

---

## Catatan Akademik

Aplikasi ini dikembangkan sebagai proyek penelitian dan pembelajaran dengan fokus pada:

- Pemanfaatan teknologi untuk aksesibilitas
- Implementasi Laravel Framework
- Penerapan konsep MVC
- Integrasi QR Code dan audio digital
- Kerapian struktur kode
- Konsistensi antarmuka aplikasi

---

## Pengembang

Nama: Muhammad Almuwarisin  
Program Studi: Teknologi Informasi

GitHub: https://github.com/Ayries18

---

Dibangun sebagai proyek pembelajaran dan penelitian dengan pendekatan profesional
