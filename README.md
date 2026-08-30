# Read-Assist

Aplikasi layanan akses belajar mandiri berbasis web dengan fitur **QR-Audio** dan manajemen katalog buku audio untuk mendukung penyandang tunanetra.

<p align="center">
  <img src="public/logo.png" alt="Read-Assist" width="120" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/SQLite-003B57?style=for-the-badge&logo=sqlite&logoColor=white" />
  <img src="https://img.shields.io/badge/Tailwind_CSS-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" />
  <img src="https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white" />
  <img src="https://img.shields.io/badge/Blade_Template-F55247?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/QR_Code-000000?style=for-the-badge&logo=qrcode&logoColor=white" />
</p>

---

## Gambaran Umum

Read-Assist adalah aplikasi web berbasis **Laravel** yang dirancang untuk membantu penyandang tunanetra memperoleh akses belajar yang lebih mudah melalui teknologi audio digital dan QR Code.

Pengguna cukup **memindai QR Code** pada buku dengan smartphone untuk langsung membuka dan memutar audio buku secara praktis. Aplikasi dikembangkan dengan antarmuka sederhana, responsif, dan fokus penuh pada **aksesibilitas**.

---

## Tujuan Pengembangan

- Membangun sistem akses belajar berbasis web untuk tunanetra
- Mengimplementasikan framework Laravel dalam pengembangan aplikasi
- Mengintegrasikan QR Code dengan audio digital
- Mengelola katalog buku dan audio secara terstruktur
- Menerapkan arsitektur MVC serta prinsip aksesibilitas WCAG
- Analisis otomatis ringkasan dan kata kunci teks via Google Gemini

---

## Fitur Utama

- 📚 Upload buku digital (PDF) dengan ekstraksi teks otomatis (`pdftotext`)
- 🔊 Upload dan generate audio buku (TTS)
- 📱 Generate QR Code otomatis untuk setiap buku
- 🎧 Audio player terintegrasi + mini player saat berpindah halaman
- 🗂️ Manajemen katalog buku (admin) & dashboard pengguna
- ✨ Asisten baca: ringkasan & kata kunci otomatis via Google Gemini
- 📊 Pelacakan progres mendengarkan per pengguna

### Aksesibilitas

Aplikasi dibangun dengan standar **WCAG 2.2** dan diuji dengan *axe-core*:

- 🔷 **Mode kontras tinggi** (hitam–kuning) yang konsisten di seluruh halaman
- 🗣️ Kompatibel dengan **TalkBack** / screen reader (aria-label, live region, struktur heading yang benar)
- ⌨️ Navigasi **keyboard-only** penuh, termasuk fokus & penutupan modal dengan Escape
- 🔈 **Suara pendamping** (TTS) untuk membacakan teks
- 🔍 Kontras warna yang lolos rasio AA pada semua komponen

---

## Tangkapan Layar

Tambahkan hasil tangkapan layar aplikasi ke folder `docs/screenshots/` lalu referensikan di sini, contoh:

```markdown
![Beranda](docs/screenshots/beranda.png)
![Katalog](docs/screenshots/katalog.png)
```

---

## Teknologi yang Digunakan

| Bagian | Teknologi |
| --- | --- |
| Backend | Laravel 13, PHP 8.3 |
| Database | SQLite |
| Frontend | Blade, Tailwind CSS, JavaScript (Vite) |
| Ekstraksi PDF | poppler-utils (`pdftotext`) |
| AI Analisis Teks | Google Gemini API |

---

## Cara Menjalankan

### Prasyarat

- PHP 8.3+
- Composer
- Node.js + npm
- `poppler-utils` (`pdftotext`) — opsional untuk ekstraksi PDF

### Development

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

Aplikasi berjalan pada `http://127.0.0.1:8000`.

### Production (build aset)

```bash
npm run build
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Untuk pemrosesan antrean (generate audio), jalankan worker:

```bash
php artisan queue:work
```

### Konfigurasi Lingkungan

Salin `.env.example` menjadi `.env` lalu sesuaikan nilai penting berikut:

| Variabel | Keterangan | Contoh |
| --- | --- | --- |
| `APP_NAME` | Nama aplikasi | `Read-Assist` |
| `APP_URL` | URL aplikasi | `http://127.0.0.1:8000` |
| `DB_CONNECTION` | Driver database | `sqlite` |
| `GEMINI_API_KEY` | API key Google Gemini (untuk analisis teks) | `AIza...` |
| `GEMINI_MODEL` | Model Gemini yang digunakan | `gemini-2.5-flash` |

> `SESSION_DRIVER=database` memerlukan tabel session — sudah tercakup dalam `php artisan migrate`.

---

## Struktur Proyek

```bash
Read-Assist/
├── app/
│   ├── Http/Controllers/   # Controller aplikasi
│   ├── Jobs/               # Antrean (generate audio, dll.)
│   ├── Models/             # Model Eloquent
│   └── Services/           # Layanan pendukung (tunnel, dsb.)
├── bootstrap/
├── config/
├── database/
├── public/                 # Aset publik & logo
├── resources/
│   ├── css/                # Styling (termasuk mode kontras tinggi)
│   └── views/              # Blade templates
├── routes/
├── storage/
└── tests/
```

---

## Pengembang

**Muhammad Almuwarisin** — Mahasiswa Teknologi Informasi

- GitHub: [https://github.com/Ayries18](https://github.com/Ayries18)

---

Dibangun sebagai proyek penelitian dan pembelajaran dengan pendekatan profesional.