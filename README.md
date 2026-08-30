<div align="center">

# 🎧 Read-Assist

**Platform akses belajar berbasis web untuk penyandang tunanetra** — katalog buku audio, QR Code, dan asisten baca berbasis AI.

<br />

<p align="center">
  <img src="public/logo.png" alt="Read-Assist logo" width="110" />
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
![Google Gemini](https://img.shields.io/badge/Gemini_AI-8E75B2?style=for-the-badge&logo=google&logoColor=white)

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

Read-Assist adalah aplikasi web berbasis **Laravel 13** yang membantu penyandang tunanetra mengakses materi belajar secara mandiri melalui **audio digital** dan **QR Code**.

Pengguna cukup **memindai QR Code** pada buku menggunakan smartphone — aplikasi langsung membuka halaman buku beserta putaran audionya. Antarmuka dirancang sederhana, responsif, dan berfokus penuh pada aksesibilitas pengguna.

---

## ✨ Fitur Utama

| | Fitur | Keterangan |
| --- | --- | --- |
| 📚 | **Upload buku digital** | PDF dengan ekstraksi teks otomatis (*poppler-utils*) |
| 🔊 | **Audio buku (TTS)** | Upload audio manual maupun generate dari teks |
| 🧩 | **QR Code otomatis** | Setiap buku mendapat QR unik untuk akses cepat via ponsel |
| 🎧 | **Pemutar audio terintegrasi** | Pemutar halaman penuh + mini player yang tetap tampil saat berpindah halaman |
| 🗂️ | **Manajemen katalog** | Dashboard admin untuk kelola buku & audio |
| 🧠 | **Asisten baca AI** | Ringkasan & kata kunci otomatis dari teks via **Google Gemini** |
| 📊 | **Progres belajar** | Pelacakan durasi mendengarkan per pengguna |

---

## ♿ Aksesibilitas

Aplikasi dibangun sesuai standar **WCAG 2.2** dan diverifikasi dengan **axe-core** (0 pelanggaran):

- 🔷 **Mode kontras tinggi** hitam–kuning yang konsisten di seluruh halaman
- 🗣️ Kompatibel dengan **TalkBack** / *screen reader* — `aria-label`, *live region*, heading berstruktur
- ⌨️ **Navigasi keyboard penuh** — fokus terlihat jelas, modal tertutup dengan `Escape`
- 🔈 **Suara pendamping** (TTS) untuk membacakan konten halaman
- 🎯 Kontras warna lolos rasio AA pada semua komponen

---

## 🧰 Teknologi

| Lapisan | Teknologi |
| --- | --- |
| **Backend** | Laravel 13, PHP 8.3 |
| **Database** | SQLite |
| **Frontend** | Blade, Tailwind CSS, JavaScript (Vite) |
| **Ekstraksi PDF** | poppler-utils (`pdftotext`) |
| **AI** | Google Gemini (`gemini-2.5-flash`) |

---

## 🚀 Cara Menjalankan

### Prasyarat

- **PHP 8.3+** & Composer
- **Node.js** & npm
- `poppler-utils` (`pdftotext`) — opsional, untuk ekstraksi teks PDF

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
```

Buka **http://127.0.0.1:8000** 🎉

### 2. Production

```bash
npm run build
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Untuk memproses antrean generate audio, jalankan *worker*:

```bash
php artisan queue:work
```

---

## ⚙️ Konfigurasi Lingkungan

Salin `.env.example` menjadi `.env`, lalu sesuaikan variabel penting ini:

| Variabel | Keterangan | Contoh |
| --- | --- | --- |
| `APP_NAME` | Nama aplikasi | `Read-Assist` |
| `APP_URL` | URL aplikasi | `http://127.0.0.1:8000` |
| `DB_CONNECTION` | Driver database | `sqlite` |
| `SESSION_DRIVER` | Penyimpanan sesi | `database` |
| `GEMINI_API_KEY` | API key Google Gemini (analisis teks) | `AIza...` |
| `GEMINI_MODEL` | Model Gemini | `gemini-2.5-flash` |

---

## 📁 Struktur Proyek

```text
Read-Assist/
├── app/
│   ├── Http/Controllers/     # Controller aplikasi
│   ├── Jobs/                 # Antrean: generate audio, dsb.
│   ├── Models/               # Model Eloquent
│   └── Services/             # Layanan pendukung (tunnel, dsb.)
├── bootstrap/
├── config/
├── database/
├── public/
│   ├── logo.png
│   └── qr/                   # QR Code per buku
├── resources/
│   ├── css/                  # Styling + mode kontras tinggi
│   └── views/                # Blade templates
├── routes/
├── storage/
└── tests/
```

---

## 👤 Pengembang

<div align="center">

**Muhammad Almuwarisin** — Mahasiswa Teknologi Informasi

[![GitHub](https://img.shields.io/badge/GitHub-Ayries18-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/Ayries18)

</div>

---

<p align="center">
  Dibangun sebagai proyek penelitian & pembelajaran dengan pendekatan profesional 💡
</p>
