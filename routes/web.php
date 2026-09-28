<?php

use App\Http\Controllers\AudioBukuController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\ReadAssistController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/qr-code', [QRCodeController::class, 'generate'])->name('qr-code.generate');

// Service worker disajikan via Laravel (bukan file static) supaya header
// caching-nya bisa dijamin 'no-cache' — server-proxy (LiteSpeed/Cloudflare)
// menimpa header static sw.js menjadi immutable ke browser, yang berakibat
// update service worker tidak pernah keluar.
// Catatan: sesi sengaja TIDAK dilepas. Respons ber-Set-Cookie membuat
// Cloudflare mengirim cf-cache-status: BYPASS, jadi worker tidak pernah
// tersimpan di edge cache Cloudflare (CDN-Cache-Control: no-store
// mempertebalnya untuk CDN apa pun yang menghormatinya).
//
// URL PRIMER: /service-worker.js — path baru yang belum pernah ter-cache
// sebagai file static, jadi worker tidak mungkin disajikan basi. /sw.js
// dibiarkan sebagai alias untuk unit lama yang masih meregistrasikan di
// situ (broswer lama) sampai mereka pindah ke path baru.
$serveWorker = static function () {
    $worker = file_get_contents(resource_path('pwa/sw.js'));

    return response($worker, 200, [
        'Content-Type' => 'application/javascript',
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
        'CDN-Cache-Control' => 'no-store',
        'Pragma' => 'no-cache',
        'X-Content-Type-Options' => 'nosniff',
    ]);
};

Route::get('/service-worker.js', $serveWorker)->name('service-worker');
Route::get('/sw.js', $serveWorker);

Route::get('/', [AudioBukuController::class, 'landing'])->name('home');

Route::get('/read-assist', [ReadAssistController::class, 'index'])->name('read.index');
Route::post('/proses-teks', [ReadAssistController::class, 'process'])->name('read.process');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process')->middleware('throttle:5,1');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process')->middleware('throttle:3,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated routes (any role)
Route::middleware('auth.session')->group(function () {
    // Profile
    Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Book management (create/store/edit/update/destroy/retry) — admin or owner enforced in controller
    Route::get('/katalog-audio/tambah', [AudioBukuController::class, 'create'])->name('audio-books.create');
    Route::post('/katalog-audio', [AudioBukuController::class, 'store'])->name('audio-books.store');
    Route::get('/katalog-audio/{audioBook}/edit', [AudioBukuController::class, 'edit'])->name('audio-books.edit');
    Route::put('/katalog-audio/{audioBook}', [AudioBukuController::class, 'update'])->name('audio-books.update');
    Route::delete('/katalog-audio/{audioBook}', [AudioBukuController::class, 'destroy'])->name('audio-books.destroy');
    Route::post('/katalog-audio/{audioBook}/retry-audio', [AudioBukuController::class, 'retryAudio'])->name('audio-books.retry-audio');
});

// User-only routes
Route::middleware('auth.session:user')->group(function () {
    Route::get('/user/dashboard', [AuthController::class, 'userDashboard'])->name('user.dashboard');
    Route::get('/user/tambah-buku', [AudioBukuController::class, 'create'])->name('user.books.create');
    Route::post('/user/tambah-buku', [AudioBukuController::class, 'store'])->name('user.books.store');
});

// Admin-only routes
Route::middleware('auth.session:admin')->group(function () {
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
});

// Reset password routes (public — guest token access)
Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.send')->middleware('throttle:3,1');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.process')->middleware('throttle:5,1');

// Audio Book public routes (catalog browsing + QR guest reader)
Route::get('/katalog-audio', [AudioBukuController::class, 'index'])->name('audio-books.index');
Route::get('/katalog-audio/{id}', [AudioBukuController::class, 'show'])->name('katalog.show');

Route::get('/scan/book/{qr_token}', [AudioBukuController::class, 'scan'])->name('scan.book');
Route::get('/katalog/{slug}', [AudioBukuController::class, 'play'])->name('audio-books.play');
Route::get('/audio-stream/{audioBook}', [AudioBukuController::class, 'streamAudio'])->name('audio.stream');
Route::post('/progress/sync/{audioBook}', [AudioBukuController::class, 'syncProgress'])->name('progress.sync');
Route::get('/progress/{audioBook}', [AudioBukuController::class, 'getProgress'])->name('progress.get');
Route::get('/audio-progress/{audioBook}', [AudioBukuController::class, 'audioProgress'])->name('audio.progress');
