# Read-Assist — Sesi Recap

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
