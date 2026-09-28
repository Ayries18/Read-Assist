# DEPLOY.md

Prosedur deployment Read-Assist ke hosting cPanel (Laravel 13 + LiteSpeed + Cloudflare).

---

## 1. Arsitektur yang harus dipahami lebih dulu

```
/home/cp2ujcb5545/
├── Read-Assist/              <- folder aplikasi (git)
│   ├── app/ bootstrap/ config/ database/ resources/ routes/ storage/ vendor/
│   ├── public/               <- sumber kebenaran aset yang akan disajikan
│   │   ├── .htaccess
│   │   ├── .user.ini
│   │   ├── index.php
│   │   ├── robots.txt
│   │   ├── logo*.png|svg
│   │   ├── favicon.*
│   │   ├── build/            <- hasil npm run build (tidak masuk git)
│   │   ├── storage -> storage/app/public   (symlink)
│   │   └── js/
│   └── scripts/deploy-public.sh
│
└── public_html/              <- DOCUMENT ROOT cPanel (BUKAN symlink)
    ├── build      -> Read-Assist/public/build     (symlink)
    ├── storage    -> Read-Assist/public/storage   (symlink)
    ├── favicon.ico-> Read-Assist/public/favicon.ico (symlink)
    ├── .htaccess  .user.ini  index.php  robots.txt  logo*  sw.js  ...  (salinan)
    ├── .well-known/acme-challenge/   <- milik cPanel, untuk renewal SSL
    ├── php.ini                       <- milik cPanel (MultiPHP INI)
    ├── error_log                     <- milik cPanel
    └── google*.html                  <- verifikasi Google Search Console
```

### Mengapa `public_html` tidak dijadikan symlink penuh

Ditolak dengan sengaja. Empat alasan, semuanya fatal:

1. **`.well-known/acme-challenge` hanya ada di `public_html`.** Mengganti document root
   dengan symlink ke `public/` akan menghapus direktori itu dan **renewal SSL berikutnya
   akan gagal**.
2. **`php.ini` dan `error_log` hanya ada di `public_html`** dan dikelola cPanel.
3. **`index.php` hasil symlink penuh menunjuk `__DIR__.'/../'`,** yaitu
   `/home/cp2ujcb5545/vendor/`, yang tidak ada. Situs akan balas HTTP 500 total.
   (Sudah diatasi di `public/index.php` dengan deteksi lokasi, tapi lihat tetap tidak
  heer)
4. cPanel menyimpan document root di konfigurasi domain; mengganti direktori tersebut
   dengan symlink sering merusak ACL LiteSpeed dan proses paduan cPanel.

Karena itu sinkronisasi dilakukan dengan **script**, bukan symlink.

### Yang sudah ditangani `public/index.php`

Satu file berlaku untuk dua layout, mendeteksi sendiri letaknya:

| Layout | Letak folder aplikasi | Cara deteksi |
|---|---|---|
| Lokal | `Read-Assist/public` | `__DIR__/..` punya `vendor/autoload.php` |
| cPanel | `Read-Assist/public_html` | `__DIR__/Read-Assist` punya `vendor/autoload.php` |

Kalau tidak ditemukan, halaman mengembalikan 500 dengan pesan jelas, bukan
`require` ke file yang tidak ada.

---

## 2. Prasyarat

```bash
# Di server
cd ~/Read-Assist
php -v                 # PHP 8.3+
git remote -v          # sudah ada origin

# Aset front-end WAJIB dibangun lebih dulu, karena node tidak ada di server cPanel.
# Bangun di mesin lokal, commit bila ada perubahan, lalu rsync/scp public/build.
npm run build
```

> **Penting:** `public/build` ada di `.gitignore`, jadi `git pull` **tidak** pernah
> mengirim aset front-end ke server. Kalau CSS/JS berubah, `public/build` harus
> diunggah terpisah. `scripts/deploy-public.sh` tidak membuat build; ia hanya
> menyalin apa yang sudah ada.

---

## 3. Prosedur deployment

```bash
# 0. Masuk ke server
ssh readassist

# 1. Tarik perubahan kode
cd ~/Read-Assist
git pull --ff-only origin master

# 2. Bersihkan cache lama
php artisan optimize:clear

# 3-5. Bangun ulang cache produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Sinkronkan public/ ke public_html
bash scripts/deploy-public.sh

# 7. Verifikasi (bisa dijalankan terpisah kapan saja)
bash scripts/deploy-public.sh --verify
```

Langkah 3 sampai 5 dipisah secara sengaja. `optimize` menyatukan ketiganya, tetapi
memakai `config:cache`, `route:cache`, dan `view:cache` satu per satu membuat
kesalahan lebih mudah dilacak.

### Opsi script

| Perintah | Fungsi |
|---|---|
| `bash scripts/deploy-public.sh` | Sinkronkan, lalu verifikasi |
| `bash scripts/deploy-public.sh --dry-run` | Tampilkan rencana saja, tidak menulis apa pun |
| `bash scripts/deploy-public.sh --verify` | Hanya verifikasi, tidak menulis |
| `APP_DIR=/path/to/app DOC_DIR=/path/to/root bash scripts/deploy-public.sh` | Override lokasi, untuk menguji di staging |

Script akan:

- menolak jalan bila `public/index.php`, `.htaccess`, atau `robots.txt` hilang;
- mencadangkan file yang akan berubah ke `public_html/.deploy-backups/<timestamp>/`;
- menyalin seluruh isi `public/` dengan mempertahankan permission;
- membuat ulang symlink `build`, `storage`, dan `favicon.ico`;
- menetapkan direktori 755 dan file 644;
- memverifikasi hasilnya per file dan keluar dengan kode bukan 0 bila ada yang gagal.

### Cara aman menghapus file usang

Script **tidak** memakai `rm -rf` untuk semua yang tidak ada di repo. Itu akan
menghapus `.well-known`, `php.ini`, dan `error_log`. Sebagai gantinya script menyimpan
`.deploy-public.manifest` berisi nama yang pernah disinkronkan, lalu hanya menghapus
nama yang tercatat di sana **dan** sudah hilang dari `public/`.

---

## 4. Verifikasi setelah deploy

```bash
# Dari server
bash scripts/deploy-public.sh --verify

# Cek respons HTTP dan header cache
curl -sS -o /dev/null -D - https://readassist.web-id.id/ | grep -iE 'HTTP|cache-control|content-encoding'
curl -sS -o /dev/null -D - https://readassist.web-id.id/build/manifest.json | grep -iE 'HTTP|cache-control'

# Route publik harus 200
for p in / /katalog-audio /login /register; do
  printf '%-20s %s\n' "$p" "$(curl -sS -o /dev/null -w '%{http_code}' "https://readassist.web-id.id$p")"
  sleep 1
done

# Route terproteksi harus 302 ke /login, bukan 500
for p in /admin/dashboard /user/dashboard /katalog-audio/tambah /user/tambah-buku; do
  printf '%-24s %s -> %s\n' "$p" \
    "$(curl -sS -o /dev/null -w '%{http_code}' "https://readassist.web-id.id$p")" \
    "$(curl -sS -o /dev/null -w '%{redirect_url}' "https://readassist.web-id.id$p")"
  sleep 1
done

# Token QR palsu harus 404, bukan 500 (500 berarti front controller rusak)
curl -sS -o /dev/null -w '%{http_code}\n' https://readassist.web-id.id/scan/book/token-palsu

# Isi halaman dan error log
curl -sS https://readassist.web-id.id/ | grep -c '<h1'
grep -c '\[28-Sep-2026' ~/public_html/error_log      # sesuaikan tanggal
```

Yang harus benar:

- `/` balas HTTP 200, berisi 1 `<h1>`, dan berukuran puluhan ribu byte;
- route terproteksi balas **302 ke `/login`**, bukan 500;
- `/scan/book/token-palsu` balas **404**, bukan 500;
- CSS dan font punya `Cache-Control: public, max-age=31536000, immutable`;
- `public_html/build/manifest.json` ada dan seluruh aset rujukannya ada;
- symlink `build`, `storage`, `favicon.ico` tidak putus;
- `.well-known/acme-challenge` masih ada;
- `~/public_html/error_log` tidak menambah baris error pada tanggal hari ini.

> Beri jeda 1 detik antar `curl`. Mengakses beruntun tanpa jeda memicu Cloudflare
> mengembalikan 522/525 sesaat, yang keliru dan bukan tanda situs rusak.

---

## 5. Rollback

### 5a. Rollback aset statis saja (paling sering dipakai)

Kalau situs masih hidup tapi CSS atau gambar rusak:

```bash
cd ~/Read-Assist
ls -1dt ~/public_html/.deploy-backups/* | head -5      # lihat daftar backup

# Salin kembali isi backup
cp -a ~/public_html/.deploy-backups/<timestamp>/. ~/public_html/
php artisan view:cache

# If the problem is the code, roll back first, then repeat the sync
git checkout <commit-sebelumnya>
bash scripts/deploy-public.sh
```

### 5b. Rollback kode

```bash
cd ~/Read-Assist
git log --oneline -10                       # cari commit target
git revert <commit-yang-bermasalah>         # lebih aman daripada reset
# atau, bila belum ada yang pushed:
git reset --hard <commit-sebelumnya>        # HATI-HATI: membuang perubahan lokal

php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
bash scripts/deploy-public.sh
```

### 5c. Kalau situs tidak mau hidup sama sekali

```bash
cd ~/Read-Assist
mv ~/public_html/index.php ~/public_html/index.php.broken
echo '<?php require "/home/cp2ujcb5545/Read-Assist/vendor/autoload.php";
      $app = require_once "/home/cp2ujcb5545/Read-Assist/bootstrap/app.php";
      $app->handleRequest(Illuminate\Http\Request::capture());' > ~/public_html/index.php
chmod 644 ~/public_html/index.php
```

Lalu diagnose dari `~/public_html/error_log` dan `~/Read-Assist/storage/logs/laravel.log`.

---

## 6. Pemeliharaan berkelanjutan

### Bila batas PHP diubah lewat cPanel

cPanel menuliskan balik blok miliknya ke `~/public_html/.htaccess`, **bukan** ke
`Read-Assist/public/.htaccess`. Setelah mengubah PHP INI di cPanel:

```bash
# Ambil blok terbaru dari document root
sed -n '/BEGIN cPanel-generated/,$p' ~/public_html/.htaccess
# Salin blok itu ke Read-Assist/public/.htaccess, lalu commit
cd ~/Read-Assist && git add public/.htaccess && git commit -m "Sync cPanel PHP INI block"
```

Kalau langkah ini dilewatkan, sinkronisasi berikutnya akan mengembalikan nilai lama
dan batas PHP produksi bisa berubah tanpa Anda sadari.

### Bila ada front controller dijalankan dua kali

Bila muncul `Command line code line 1` atau respons kosong untuk `.php`, cek apakah
ada blok rewrite di `.htaccess` yang menulis ulang ke `index.php` dua kali.
Jalankan:

```bash
grep -c 'RewriteRule.*index.php' ~/public_html/.htaccess
```

Nilai yang benar adalah `1`.

### T merchand spurred check berkala

Jalankan `bash scripts/deploy-public.sh --verify` setelah setiap perubahan `.htaccess`,
setiap pergantian cPanel, dan sebelum harden yang berikutnya. Perintah ini hanya
membaca, jadi aman dijalankan sesering mungkin.
