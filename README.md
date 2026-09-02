<div align="center">

# 🎧 Read-Assist

**Platform akses belajar berbasis web untuk penyandang tunanetra** — katalog buku audio, QR Code, dan asisten baca berbasis AI.

<br />

<p align="center">
  <img src="./public/logo-read-assist.png" alt="Logo Read-Assist" width="110">
</p>

<br />

![GitHub last commit](https://img.shields.io/github/last-commit/Ayries18/Read-Assist?style=flat&label=Terakhir%20diperbarui&color=34d399)
![GitHub repo size](https://img.shields.io/github/repo-size/Ayries18/Read-Assist?style=flat&label=Ukuran%20repo&color=60a5fa)
![GitHub language top](https://img.shields.io/github/languages/top/Ayries18/Read-Assist?style=flat&label=Bahasa&color=22d3ee)
![GitHub stars](https://img.shields.io/github/stars/Ayries18/Read-Assist?style=social)

<br />

![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=black)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Blade](https://img.shields.io/badge/Blade_Template-F55247?style=for-the-badge&logo=laravel&logoColor=white)
![QR Code](https://img.shields.io/badge/QR_Code-000000?style=for-the-badge&logo=qrcode&logoColor=white)

</div>

---

## 📑 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Utama](#-fitur-utama)
- [Aksesibilitas](#-aksesibilitas)
- [Teknologi](#-teknologi)
- [Cara Menjalankan](#-cara-menjalankan)
- [Konfigurasi Lingkungan](#-konfigurasi-lingkungan)
- [Struktur Proyek](#-struktur-proyek)
- [Pengembang](#-pengembang)

---

## 💡 Tentang Proyek

Read-Assist adalah aplikasi web berbasis **Laravel 13** yang dikembangkan untuk membantu penyandang tunanetra mengakses materi pembelajaran secara lebih mandiri melalui **audio digital** dan **QR Code**.

Pengguna dapat **memindai QR Code** pada buku menggunakan smartphone untuk membuka halaman buku beserta pemutaran audionya. Antarmuka dirancang sederhana, responsif, dan berfokus pada kemudahan akses bagi pengguna.

---

## ✨ Fitur Utama

| | Fitur | Keterangan |
| --- | --- | --- |
| 📚 | **Upload buku digital** | Upload buku dalam format PDF dengan ekstraksi teks otomatis menggunakan *poppler-utils* |
| 🔊 | **Audio buku (TTS)** | Upload audio secara manual maupun menghasilkan audio dari teks |
| 🧩 | **QR Code otomatis** | Setiap buku mendapatkan QR Code unik untuk akses cepat melalui perangkat seluler |
| 🎧 | **Pemutar audio terintegrasi** | Pemutar audio halaman penuh dan mini player yang tetap tersedia saat berpindah halaman |
| 🗂️ | **Manajemen katalog** | Dashboard admin untuk mengelola buku dan audio |
| 📊 | **Progres belajar** | Pelacakan durasi mendengarkan pengguna |

---

## ♿ Aksesibilitas

Aplikasi dibangun dengan mengacu pada prinsip **WCAG 2.2** dan diverifikasi menggunakan **axe-core** dengan hasil **0 pelanggaran**:

- 🔷 **Mode kontras tinggi** hitam–kuning yang konsisten di seluruh halaman
- 🗣️ Kompatibel dengan **TalkBack** dan *screen reader* melalui `aria-label`, *live region*, serta struktur heading yang terorganisasi
- ⌨️ **Navigasi keyboard penuh** dengan indikator fokus yang jelas dan dukungan penutupan modal menggunakan `Escape`
- 🔈 **Suara pendamping (TTS)** untuk membantu membacakan konten halaman
- 🎯 **Kontras warna** yang memenuhi rasio AA pada komponen yang diuji

---

## 🧰 Teknologi

| Lapisan | Teknologi |
| --- | --- |
| **Backend** | Laravel 13, PHP 8.3 |
| **Database** | SQLite |
| **Frontend** | Blade, Tailwind CSS, JavaScript |
| **Build Tool** | Vite |
| **Ekstraksi PDF** | poppler-utils (`pdftotext`) |
| **Audio** | Text-to-Speech (TTS) |
| **QR Code** | QR Code Generator |

---

## 🚀 Cara Menjalankan

### Prasyarat

- **PHP 8.3+** dan Composer
- **Node.js** dan npm
- **poppler-utils** (`pdftotext`) — opsional, digunakan untuk ekstraksi teks dari PDF

### 1. Instalasi & Development

```bash
git clone https://github.com/Ayries18/Read-Assist.git
cd Read-Assist

composer install
npm install

cp .env.example .env
php artisan key:generate
php artisan migrate

npm run dev          # terminal 1 — hot-reload aset
php artisan serve    # terminal 2 — server aplikasi
