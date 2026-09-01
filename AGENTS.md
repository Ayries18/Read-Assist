# AGENTS.md

## Project Overview
Read-Assist is a Laravel 13 web platform providing accessible audio books for visually impaired users (*tunanetra*) via automated Text-to-Speech (TTS), QR code routing, and Web Speech API player.

## Tech Stack
- **Backend**: Laravel 13 (PHP 8.3+), SQLite (default: `database/database.sqlite`, testing: `:memory:`)
- **Frontend**: Blade templates, Tailwind CSS v4, DaisyUI 5, Vite 8
- **Audio & Extraction**: `poppler-utils` (`pdftotext`), `ZipArchive` (EPUB), Google Translate TTS endpoint (`tw-ob`)
- **Tunneling**: SSH reverse tunnel via `nokey@localhost.run` (`TunnelService`)

---

## Essential Commands

### Development
- Run full dev stack (Server + Queue + Vite + Tunnel concurrently):
  ```bash
  composer run dev
  ```
- Run individual services:
  ```bash
  php artisan serve                                         # Web server (http://127.0.0.1:8000)
  npm run dev                                               # Vite hot-reload
  php artisan queue:listen --tries=1 --timeout=0            # Queue worker for TTS audio generation
  php artisan tunnel:start                                  # SSH tunnel for public/LAN QR code access
  ```

### Testing & Code Style
- Run all tests:
  ```bash
  php artisan test
  ```
- Run a single test file:
  ```bash
  php artisan test tests/Feature/AudioBukuTest.php
  ```
- Run a specific test method:
  ```bash
  php artisan test --filter=test_user_can_login
  ```
- Code formatting check / fix:
  ```bash
  vendor/bin/pint --test   # Check formatting (PSR-12)
  vendor/bin/pint          # Apply automatic fixes
  ```

### Database & Seeders
- Reset and seed database:
  ```bash
  php artisan migrate:fresh --seed
  ```
- Default Seeder Accounts:
  - Admin: `admin@example.com` / `password`
  - User: `muwarisin@gmail.com` / `Aris1234`

---

## Architecture & Codebase Gotchas

### 1. Custom Session-Based Authentication (Not Native Laravel Auth Guards)
- Authentication does **NOT** use `Auth::user()` or standard guards.
- Auth state is checked directly via session:
  - User ID: `session('auth_id')`
  - User Role: `session('auth_role')` (`'admin'` or `'user'`)
  - User Name: `session('auth_name')`
- Middleware and controllers must check `session('auth_role')` instead of `Auth::check()`.

### 2. Audio Generation & Background Queue
- Uploaded books trigger `GenerateBookAudio` job dispatched to the queue.
- `GenerateBookAudio` splits text into sentence chunks and calls `TTSEngine`.
- `TTSEngine` fetches MP3 audio chunks and concatenates raw MP3 binaries into `storage/app/public/audio/`.
- For background audio generation to work locally, queue worker (`php artisan queue:listen`) must be running.

### 3. Text Extraction Dependencies
- **PDF Extraction**: Uses shell command `pdftotext -enc UTF-8 -layout` (`poppler-utils`). Ensure poppler-utils is installed in the environment.
- **EPUB Extraction**: Uses PHP `ZipArchive` to parse and strip HTML tags from internal XHTML files.

### 4. QR Code & Tunnel Routing
- Each `AudioBuku` model has a `qr_token` (UUID).
- QR codes point to `/scan/book/{qr_token}`.
- `AudioBukuController::scan()` checks active SSH tunnel URL (`TunnelService`), fallback Ngrok, or detected LAN IP so mobile devices on the same network can access the audio reader.
- Visiting via QR code sets a guest session (`RestrictQrGuest` middleware).

### 5. Accessibility (WCAG 2.2 / A11y Standards)
- **High-contrast mode**: Black background (`#000000`) and yellow accents (`#FBBF24`).
- **Keyboard navigation**: Standard shortcuts must remain supported (`Space` to toggle play/pause, `ArrowLeft` / `ArrowRight` for sentence seeking, `Escape` to stop/close).
- **Screen Reader / TalkBack**: Retain ARIA landmarks, `aria-live="polite"` regions, and descriptive `aria-label` tags on all interactive elements.

### 6. AI & 9Router Integration
- `NineRouterService` is configured in `config/services.php` pointing to `http://localhost:20128/v1` (`NINEROUTER_KEY`).
- Model definition is in `opencode.json` using `ninerouter` provider.
