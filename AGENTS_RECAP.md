# Read-Assist — Sesi Recap

## 📋 Technical Reference

**Deskripsi:** Platform aksesibilitas buku audio untuk tunanetra. Upload PDF/EPUB → auto-generate audio (TTS via Google) + QR code. Scan QR dari HP → play audio buku tanpa login.

### Tech Stack

- Laravel 13, PHP ^8.3, SQLite (default)
- Tailwind CSS v4 + Vite + DaisyUI 5
- simplesoftwareio/simple-qrcode 4.2 (QR generation)
- Google Translate TTS API via Guzzle (per-sentence MP3, concatenated to full.mp3)
- `pdftotext` (poppler-utils) — system dependency for PDF text extraction
- Queue: `database` driver — jobs table
- Session-based auth (custom, no Breeze/Jetstream). Roles: `admin`, `user`.

### Quick Start

```bash
composer setup      # install + .env + key + migrate + npm build
npm run build       # build frontend assets (CSS/JS via Vite)
composer dev        # full dev: server + queue + logs + vite + tunnel (5 processes)
```

### Dev Commands

| Command | Purpose |
|---------|---------|
| `composer dev` | 5 parallel services via `concurrently` (server, queue, logs, vite, tunnel) |
| `composer test` | `config:clear` → `php artisan test` |
| `composer fresh-seed` | `migrate:fresh --seed` |
| `php artisan serve` | Overridden to bind `0.0.0.0` (LAN access) + auto-open browser |
| `php artisan queue:listen --tries=1 --timeout=0` | Process TTS audio generation jobs |
| `php artisan tunnel:start` | SSH tunnel (localhost.run) — internet access for QR |
| `php artisan tunnel:stop` | Kill active tunnel |
| `php artisan qr:regenerate` | Regenerate QR SVG files for all/specific books |

### QR Flow Architecture

1. **Upload buku** → `AudioBukuController::store()` → simpan ke DB, generate UUID `qr_token`, dispatch `GenerateBookAudio` job
2. **QR encode** → `{APP_URL}/katalog-audio/{id}` (lihat `buildQrUrl()`) → SVG disimpan di `storage/app/public/qr/qr-book-{id}.svg`
3. **Scan QR** (dari HP) → `GET /katalog-audio/{id}` → `AudioBukuController::show()`:
   - Kalau guest (belum login) → set session `qr_restricted_token` → redirect ke `GET /katalog/{qr_token}` (play view)
   - Kalau login → tampil halaman detail buku normal (`show.blade.php`)
4. **`RestrictQrGuest` middleware** (registered globally via `bootstrap/app.php:14`):
   - Guest dengan session `qr_restricted_token` cuma bisa akses: `/`, `/login`, `/register`, `/katalog/*`, `/katalog-audio/*`, `/logout`, `/api/*`
   - Route lain → redirect balik ke play view
5. **Play view** → `audio-books.play.blade.php` — TTS player via Web Speech API (browser native). Tidak perlu nunggu audio MP3 selesai di-generate.

### Route Map

| Route | Method | Function |
|-------|--------|----------|
| `/katalog-audio` | GET | Book catalog index |
| `/katalog-audio/{id}` | GET | Book detail + QR entry point (target QR encode) |
| `/katalog-audio/{id}/edit` | GET | Edit book (admin only) |
| `/katalog-audio/tambah` | GET | Upload book form |
| `/katalog-audio` | POST | Upload + trigger audio generation |
| `/scan/book/{qr_token}` | GET | Alternative QR entry |
| `/katalog/{slug}` | GET | TTS audio player (slug = qr_token) |
| `/audio-stream/{audioBook}` | GET | Stream generated MP3 (hanya jika `audio_status=completed`) |
| `/progress/sync` | POST | Save listening progress (auth) |
| `/progress/{audioBook}` | GET | Get listening progress (auth) |
| `/qr-code` | GET | Generic QR generator (`?data=&size=`) |

### Key Gotchas

- **`php artisan serve` is overridden** — binds `0.0.0.0` (bukan 127.0.0.1). Lihat `app/Console/Commands/ServeCommand.php`.
- **Built CSS > Vite HMR** — layout prioritaskan `public/build/manifest.json`. Vite HMR tidak reliable dari HP/eksternal.
- **SQLite `DB_DATABASE` trap** — jangan set `DB_DATABASE=` di `.env` kalau pakai SQLite. Biarkan kosong agar Laravel fallback ke `database_path('database.sqlite')`.
- **Queue worker WAJIB jalan** — tanpa `queue:listen`, TTS audio tidak akan pernah di-generate (`audio_status` stuck di `pending`).
- **`pdftotext` required** — ekstraksi teks PDF gagal silent kalau `poppler-utils` tidak terinstall.
- **`canManageBook()` cuma `auth_role === 'admin'`** — pemilik buku (user) tidak bisa edit/hapus bukunya sendiri. Known issue.
- **Tunnel URL** — disimpan di `storage/app/tunnel_url.txt`. Update `APP_URL` di `.env` kalau tunnel/IP berubah, lalu regenerate QR.
- **QR SVG di-regenerate setiap `show()`** — setiap kali halaman detail dibuka, QR code di-rebuild pakai `APP_URL` terbaru.
- **No cron/scheduled tasks** — `routes/console.php` cuma `inspire`. Queue worker harus manual.
- **Docker** — pakai `php:8.2-apache` + poppler-utils. **Tidak ada queue worker** di Dockerfile — perlu process terpisah untuk production.

### Testing

- SQLite `:memory:`, `RefreshDatabase`, queue `sync`, session `array`, cache `array`
- Run: `composer test` (wajib `config:clear` dulu)
- Tests: `tests/Feature/AudioBookTest.php` — 10 tests (landing, catalog, detail, auth, 404)

### App Structure

```
Read-Assist/
├── app/
│   ├── Console/Commands/     # ServeCommand (0.0.0.0), TunnelStart/Stop, RegenerateQr
│   ├── Http/
│   │   ├── Controllers/      # AudioBukuController (main), AuthController, QRCodeController, ReadAssistController
│   │   └── Middleware/       # RestrictQrGuest
│   ├── Jobs/                 # GenerateBookAudio (queued TTS)
│   ├── Models/               # AudioBuku, ListeningProgress
│   └── Services/             # TunnelService, TTSEngine
├── bootstrap/app.php         # Middleware registration
├── config/
│   ├── app.php               # APP_URL = config('app.url')
│   ├── queue.php             # default = database
│   └── tts.php               # TTS_TIMEOUT (default 120s)
├── resources/
│   ├── css/app.css           # All styles (Tailwind v4 + custom)
│   ├── js/app.js             # Vanilla JS (Web Speech API TTS)
│   └── views/                # Blade templates (app layout, audio-books: index/create/edit/show/play)
├── routes/
│   ├── web.php               # All HTTP routes
│   └── console.php           # Only inspire (no cron)
├── tests/Feature/            # AudioBookTest (10 tests)
├── Dockerfile                # php:8.2-apache + poppler-utils
├── render.yaml               # Render deploy (single web service, SQLite)
└── composer.json             # Scripts: dev, setup, test, tunnel, fresh-seed
```

---

## Goal
Bikin Read-Assist Laravel web app fully functional from mobile via QR code scan, dengan look, feel, dan audio features yang sama kayak laptop.

## Constraints & Preferences
- Mobile display harus ngikutin laptop (layout, colors, dark theme).
- Audio (TTS via Web Speech API) harus work di mobile.
- QR code harus bisa di-scan dari network mana aja.
- Dev pake Windows + `php artisan serve`.

---

## Progress

### Done — Sesi 1
- Fix `php artisan serve` binding ke `0.0.0.0` (bukan `127.0.0.1`) via `ServeCommand.php` override — biar perangkat LAN bisa reach server.
- Bikin `app/Services/TunnelService.php` — dedicated service buat manage SSH tunnel (localhost.run).
- Bikin `php artisan tunnel:start` dan `tunnel:stop` commands.
- Pindahin logic tunnel start/stop keluar dari `AudioBookController@show` — sekarang cuma baca stored tunnel URL.
- Update `composer.json` dev script — include `php artisan tunnel:start`.
- Clean up old temp files (`localtunnel_temp.txt`, `localtunnel_err.txt`).
- Redesign `play.blade.php` — mirror layout dari `show.blade.php` (full book details, metadata grid, description card, audio player, swipe gestures, progress storage).
- Fix SVG sizing di play view.
- QR route target berubah: dari `/qr-audio/{token}` → `/katalog-audio/{id}?qr=token`.
- Update `RestrictQrGuest` middleware — allow `katalog-audio/*`.
- Tambah `?qr=token` query param di QR URLs, di-handle di `show()` buat set `qr_restricted_token` session.
- Wrap QR code section + "Kembali ke Katalog" link dengan QR-guest condition (hide pas QR access).
- Fix CSS loading — pake built CSS dari `public/build/` always via manifest, instead of Vite HMR (gagal dari HP).
- Refactor accessibility widget markup ke semantic classes.
- Full CSS buat accessibility panel (dark card, control rows, size buttons, active states, high-contrast, mobile responsive).
- Tambah `data-text-size`, `aria-expanded`, `aria-pressed` buat screen reader.
- Tambah `updateAccessibilityButtonStates()` JS function.
- Build assets pake `npm run build`.

### Done — Sesi 2 (This Session)
- **Error 1 — getTunnelUrl()**:
  - `user-dashboard.blade.php:5` dan `admin-dashboard.blade.php:5` manggil `AudioBookController::getTunnelUrl()` static — method udah di-remove.
  - Fix: Ganti pake `TunnelService::getStoredUrl()` dan `TunnelService::getLocalIp()`.
  - Tambah method `getLocalIp()` di `TunnelService` — parsing `ipconfig`, skip VirtualBox/VMware/WSL, fallback `127.0.0.1`.
- **Error 2 — database path**:
  - `.env` punya `DB_DATABASE=read_assist` — SQLite nyari file literally `read_assist`, bukan `database/database.sqlite`.
  - Fix: Comment out `DB_DATABASE=`, biar fallback ke default `database_path('database.sqlite')`.
  - Plus comment out sisa config MySQL (`DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`) — irrelevant buat SQLite.

### In Progress
- (none)

### Blocked
- (none)

---

## Key Decisions
- QR target pindah dari play view ke full show view — biar mobile user liat layout yang sama.
- Built CSS > Vite HMR buat reliability dari perangkat external.
- Accessibility panel pake custom CSS classes (bukan inline).
- Tunnel lifecycle dipisah dari web requests — manage via Artisan commands.

## Known Issues
- User masih gak bisa manage own book setelah dibuat (`canManageBook` cuma check `auth_role === 'admin'`).
- Default player sentence-by-sentence TTS di play view mungkin perlu tuning buat mobile.

## Relevant Files
- `app/Services/TunnelService.php` — tunnel management + local IP detection.
- `app/Console/Commands/ServeCommand.php` — bind `0.0.0.0`.
- `app/Console/Commands/TunnelStartCommand.php` — `php artisan tunnel:start`.
- `app/Console/Commands/TunnelStopCommand.php` — `php artisan tunnel:stop`.
- `app/Http/Middleware/RestrictQrGuest.php` — QR guest access control.
- `app/Http/Controllers/AudioBookController.php` — show(), store(), audio logic.
- `resources/views/layouts/app.blade.php` — layout with a11y widget, CSS/Vite loading, navbar.
- `resources/views/auth/user-dashboard.blade.php` — user dashboard.
- `resources/views/auth/admin-dashboard.blade.php` — admin dashboard.
- `resources/views/audio-books/show.blade.php` — book detail + QR target.
- `resources/views/audio-books/play.blade.php` — TTS player (mirrors show).
- `resources/css/app.css` — all styles.
- `.env` — DB_DATABASE commented, DB_HOST/DB_PORT/DB_USER/DB_PASS commented.
- `composer.json` — custom scripts.
