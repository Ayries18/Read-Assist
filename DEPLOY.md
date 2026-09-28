# DEPLOY.md

Prosedur deployment Read-Assist ke hosting cPanel (Laravel 13 + LiteSpeed + Cloudflare).

---

## 1. Diagram deployment

```
  MESIN LOKAL                        SERVER cPanel
  ───────────                        ─────────────

  git push  ──────────────────────►  git pull --ff-only
       │                                       │
       │                                 php artisan optimize:clear
  npm run build                            config:cache
       │                                   route:cache
       │                                   view:cache
       │                                       │
       │  scp / rsync public/build  ──────►  ~/Read-Assist/public/build/
       │                                       │
       │                                       ▼
       │                          ┌──────────────────────────────┐
       │                          │  scripts/deploy-public.sh    │
       │                          │  · validasi + proteksi       │
       │                          │  · backup (jika berubah)     │
       │                          │  · sinkron public/ → docroot │
       │                          │  · upkeep symlink            │
       │                          │  · verifikasi byte-per-byte  │
       │                          │  · health check              │
       │                          │  · tulis manifest JSON       │
       │                          └──────────────┬───────────────┘
       │                                         │
       │                                         ▼
       │        ~/Read-Assist/          ~/public_html/
       │        (git, sumber)            (document root, disposable)
       │        ├── app/                 ├── .htaccess        ← salinan
       │        ├── config/              ├── .user.ini        ← salinan
       │        ├── routes/              ├── index.php        ← salinan
       │        ├── storage/             ├── robots.txt       ← salinan
       │        └── public/              ├── logo*.png|svg    ← salinan
       │            ├── .htaccess        ├── favicon.png/.svg ← salinan
       │            ├── .user.ini        ├── manifest.json    ← salinan
       │            ├── index.php        ├── sw.js            ← salinan
       │            ├── build/           ├── build      ──► symlink
       │            └── storage ──┐      ├── storage    ──► symlink
       │                         │      ├── favicon.ico ──► symlink
       │                         │      │
       │                         └──────┤
       │                                ├── .well-known/acme-challenge/  🔒 cPanel
       │                                ├── php.ini                      🔒 cPanel
       │                                ├── error_log                    🔒 cPanel
       │                                └── google*.html                 🔒 verifikasi
       ▼
  https://readassist.web-id.id  ◄── Cloudflare ──► health check
```

Aturan yang dibaca dari diagram:

- `~/Read-Assist/public` = **sumber kebenaran**. `~/public_html` = hasil sinkronisasi.
- Yang bertanda 🔒 **tidak pernah** ditulis, dipindahkan, atau dihapus oleh script.
- `public/build` tidak masuk git, jadi harus diunggah terpisah tiap `npm run build`.
- Tiga symlink (`build`, `storage`, `favicon.ico`) dipelihara oleh script, bukan disalin.

### Kenapa `public_html` TIDAK dijadikan symlink penuh

Putusan ini sudah final dan tidak perlu ditinjau ulang:

1. **`.well-known/acme-challenge` hanya ada di `public_html`.** Mengganti document root
   akan menghapus direktori itu dan **renewal SSL berikutnya gagal**.
2. **`php.ini` dan `error_log` hanya ada di `public_html`** dan dikelola cPanel.
   Tidak bisa dipulihkan tanpa akses cPanel.
3. **`index.php` hasil symlink menunjuk `/home/cp2ujcb5545/vendor/`**, yang tidak ada.
   Situs balas HTTP 500 total.
4. cPanel menyimpan document root di konfigurasi domain. Mengganti direktori tersebut
   dengan symlink merusak ACL LiteSpeed dan proses cPanel.

### Peran `public/index.php`

`public/index.php` dilayani dari dua lokasi, jadi ia menentukan letak folder aplikasi
dulu. Satu berkas berlaku untuk keduanya, supaya sinkronisasi tidak perlu menyunting
file di server:

| Layout | Document root | Folder aplikasi |
|---|---|---|
| Lokal | `Read-Assist/public` | `__DIR__/..` |
| cPanel | `~/public_html` | `dirname(__DIR__).'/Read-Assist'` |

Di layout cPanel folder aplikasi adalah **saudara sejajar** document root, bukan
anaknya. Kalau salah menulis `__DIR__.'/Read-Assist'`, hasilnya menunjuk
`public_html/Read-Assist` yang kosong dan situs balas 500. Health check ada
guna menangkap kelas kesalahan ini.

---

## 2. Prasyarat

```bash
# Server
cd ~/Read-Assist && php -v && git remote -v

# Lokal, hanya bila CSS/JS berubah
npm run build
# lalu unggah public/build ke server
```

> `public/build` ada di `.gitignore`, jadi `git pull` **tidak pernah** mengirim aset
> front-end. Script tidak membangun aset; ia hanya menyalin yang sudah ada.

---

## 3. Checklist SEBELUM deploy

```bash
# 1. Repo lokal bersih dan test hijau
git status --porcelain          # harus kosong
php artisan test                # 48/48
vendor/bin/pint --test

# 2. Aset front-end dibangun bila ada perubahan CSS/JS
npm run build

# 3. Lihat rencana sinkronisasi di server, tanpa writes
ssh readassist 'cd ~/Read-Assist && git pull --ff-only && bash scripts/deploy-public.sh --dry-run'

# 4. Pastikan .well-known dan php.ini masih ada sebelum mulai
ssh readassist 'ls -d ~/public_html/.well-known && ls ~/public_html/php.ini'
```

- [ ] `git status` bersih, tidak ada perubahan belum ter-commit
- [ ] `php artisan test` hijau
- [ ] `npm run build` dijalankan bila ada perubahan CSS/JS
- [ ] `public/build` **sudah diunggah** ke server
- [ ] `--dry-run` tidak menunjukkan penghapusan yang tidak diharapkan
- [ ] Tidak ada deploy pada saat cPanel sedang mengubah PHP INI
- [ ] `.well-known` dan `php.ini` masih ada di document root

---

## 4. Prosedur deploy

```bash
ssh readassist
cd ~/Read-Assist

git pull --ff-only origin master     # 1. kode
php artisan optimize:clear           # 2. bersihkan cache
php artisan config:cache             # 3
php artisan route:cache              # 4
php artisan view:cache               # 5

bash scripts/deploy-public.sh        # 6. sinkron + verifikasi + health check
```

Langkah 6 sudah mencakup semuanya: backup, sinkron, upkeep symlink, verifikasi
byte-per-byte, health check, dan penulisan manifest.

### Opsi script

| Perintah | Fungsi |
|---|---|
| `bash scripts/deploy-public.sh` | Deploy penuh, lalu health check |
| `bash scripts/deploy-public.sh --dry-run` | Rencana saja: file baru/berubah/dihapus/symlink. Tidak menulis |
| `bash scripts/deploy-public.sh --verify` | Verifikasi + health check, tanpa menulis |
| `bash scripts/deploy-public.sh --list-backups` | Daftar backup beserta jumlah berkas |
| `bash scripts/deploy-public.sh --rollback latest` | Pulihkan backup terakhir |
| `HEALTH_BASE_URL=http://localhost bash scripts/deploy-public.sh` | Health check lewat URL lain |

Exit code 0 berarti deploy sukses. Selain itu ada masalah dan script mencetak
perintah pemulihan yang harus dijalankan.

---

## 5. Checklist SESUDAH deploy

Script mencetak sendiri hampir semuanya. Verifikasi manual:

```bash
ssh readassist 'cd ~/Read-Assist && bash scripts/deploy-public.sh --verify'
```

Yang harus terpenuhi:

- [ ] `file baru`, `file berubah`, `symlink diperbaiki` = 0
- [ ] 12 berkas lolos `cmp` byte-per-byte (`index.php`, `.htaccess`, `.user.ini`, aset logo/favicon)
- [ ] 3 symlink valid: `build`, `storage`, `favicon.ico`
- [ ] Seluruh aset rujukan `build/manifest.json` ada
- [ ] `.well-known`, `php.ini`, `error_log` masih ada
- [ ] Health check: `/`, `/login`, `/register`, `/katalog-audio` semuanya 200
- [ ] `error_log` tidak bertambah baris error hari ini

```bash
ssh readassist "grep -c \"\[$(date +%d-%b-%Y)\" ~/public_html/error_log"
```

> Beri jeda 1 detik antar `curl`. Akses beruntun tanpa jeda memicu Cloudflare
> mengembalikan 522/525 sesaat, yang keliru dan bukan tanda situs rusak.

---

## 6. Flow rollback

```
        deploy gagal
              │
              ▼
   health check tidak lolos / verify reported FAILURES > 0
              │
      ┌───────┴────────┐
      │                │
      ▼                ▼
  aset statis saja?   kode yang salah?
  (CSS/gambar)     (500, 500, fitur rusak)
      │                │
      ▼                ▼
  --rollback      git revert <commit>
  latest              │
      │                ▼
      │          deploy ulang (7.1)
      │                │
      └───────┬────────┘
              ▼
    verify + health check
              │
              ▼
          sukses / eskalasi
```

### 6a. Rollback aset statis (paling sering dipakai)

```bash
ssh readassist
cd ~/Read-Assist
bash scripts/deploy-public.sh --list-backups
bash scripts/deploy-public.sh --rollback latest
bash scripts/deploy-public.sh --verify
```

`--rollback latest` mengembalikan berkas dari backup terakhir **dan** menghapus
berkas yang baru dibuat deploy sebelumnya, sehingga document root kembali persis
ke kondisi sebelum deploy.

Rollback ini **tidak** menyentuh kode. Kalau deploy gagal karena perubahan kode,
lihat 6b.

### 6b. Rollback kode

```bash
cd ~/Read-Assist
git log --oneline -10                    # cari commit target
git revert <commit-yang-bermasalah>      # lebih aman daripada reset
git push origin master

php artisan optimize:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache
bash scripts/deploy-public.sh
```

Rollback kode selalu diikuti deploy ulang, karena `public/` harus ikut sinkron.

### 6c. Lift total (situs tidak mau hidup sama sekali)

`.htaccess` dan `index.php` bisa diperbaiki dari sisi server tanpa menunggu git:

```bash
cd ~/Read-Assist
mv ~/public_html/index.php ~/public_html/index.php.broken
cp public/index.php ~/public_html/index.php
mv ~/public_html/.htaccess ~/public_html/.htaccess.broken
cp public/.htaccess   ~/public_html/.htaccess
chmod 644 ~/public_html/index.php ~/public_html/.htaccess
php artisan optimize:clear && php artisan config:cache
curl -sS -o /dev/null -w '%{http_code}\n' https://readassist.web-id.id/
```

Lalu diagnose dari `~/public_html/error_log` dan `~/Read-Assist/storage/logs/laravel.log`.

---

## 7. Manifest deployment

Setiap deploy sukses menulis `~/public_html/.deploy-public.manifest.json`:

```jsonc
{
  "schema": 1,
  "commit": "<git rev-parse HEAD>",
  "deployed_at": "2026-09-28T20:15:30+07:00",
  "app_dir": "...", "doc_dir": "...",
  "sync_engine": "cp",
  "changed_count": 2,
  "added": [], "changed": ["index.php"], "removed": [],
  "synced_entries": ["..."],          // dasar deletion di deploy berikutnya
  "symlinks_verified": { "build": {"is_symlink": true, "valid": true}, ... },
  "protected_present": [".well-known", "php.ini", "error_log"],
  "checksums_sha256": { "index.php": "...", ".htaccess": "..." },
  "backup_path": "/home/.../.deploy-backups/20260928-201530",
  "health_check_ok": true
}
```

`backup_path` adalah yang dibaca `--rollback latest`. `synced_entries` yang membuat
penghapusan tetap aman: hanya nama yang tercatat di sini yang boleh dihapus, jadi
berkas milik cPanel tidak pernah ikut terhapus.

---

## 8. Pemeliharaan

### Bila batas PHP diubah lewat cPanel

cPanel menulis balik blok miliknya ke `~/public_html/.htaccess`, **bukan** ke repo:

```bash
ssh readassist 'sed -n "/BEGIN cPanel-generated/,\$p" ~/public_html/.htaccess'
# salin blok itu ke public/.htaccess, lalu commit
```

Lewati langkah ini, sinkronisasi berikutnya akan mengembalikan nilai lama dan batas
PHP produksi bisa berubah tanpa disadari.

### Bila front controller dijalankan dua kali

```bash
ssh readassist 'grep -c "RewriteRule.*index.php" ~/public_html/.htaccess'   # harus 1
```

### Bila deploy ditolakprotected file

`deploy-public.sh` berhenti sebelum menyalin apa pun bila `.well-known` atau
`php.ini` tidak ada di document root. almost always berarti `DOC_DIR` salah, bukan
repo rusak. Periksa `DOC_DIR` sebelum memaksa.

### Menghapus backup lama

```bash
ssh readassist 'ls -1dt ~/public_html/.deploy-backups/* | tail -n +8 | xargs rm -rf'
# menyisakan 7 backup terbaru
```

Backup berisi aset statis saja,Ukuran kecil, tapi tetap perlu dibatasi.
