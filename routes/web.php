<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\AudioBukuController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\ReadAssistController;
use Illuminate\Support\Facades\Route;

Route::get('/qr-code', [QRCodeController::class, 'generate'])->name('qr-code.generate');

Route::get('/', [AudioBukuController::class, 'landing'])->name('home');

Route::get('/read-assist', [ReadAssistController::class, 'index'])->name('read.index');
Route::post('/proses-teks', [ReadAssistController::class, 'process'])->name('read.process');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/user/dashboard', [AuthController::class, 'userDashboard'])->name('user.dashboard');

Route::get('/profile', [AuthController::class, 'showProfile'])->name('profile');
Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

// Reset password routes
Route::get('/lupa-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/lupa-password', [AuthController::class, 'sendResetLink'])->name('password.send');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.process');

// Audio Book routes
Route::get('/katalog-audio', [AudioBukuController::class, 'index'])->name('audio-books.index');
Route::get('/katalog-audio/tambah', [AudioBukuController::class, 'create'])->name('audio-books.create');
Route::post('/katalog-audio', [AudioBukuController::class, 'store'])->name('audio-books.store');
Route::get('/katalog-audio/{audioBook}/edit', [AudioBukuController::class, 'edit'])->name('audio-books.edit');
Route::get('/katalog-audio/{id}', [AudioBukuController::class, 'show'])->name('katalog.show');
Route::put('/katalog-audio/{audioBook}', [AudioBukuController::class, 'update'])->name('audio-books.update');
Route::delete('/katalog-audio/{audioBook}', [AudioBukuController::class, 'destroy'])->name('audio-books.destroy');
Route::post('/katalog-audio/{audioBook}/retry-audio', [AudioBukuController::class, 'retryAudio'])->name('audio-books.retry-audio');

Route::get('/scan/book/{qr_token}', [AudioBukuController::class, 'scan'])->name('scan.book');
Route::get('/katalog/{slug}', [AudioBukuController::class, 'play'])->name('audio-books.play');
Route::get('/audio-stream/{audioBook}', [AudioBukuController::class, 'streamAudio'])->name('audio.stream');
Route::post('/progress/sync', [AudioBukuController::class, 'syncProgress'])->name('progress.sync');
Route::get('/progress/{audioBook}', [AudioBukuController::class, 'getProgress'])->name('progress.get');
Route::get('/user/tambah-buku', [AudioBukuController::class, 'create'])->name('user.books.create');
Route::post('/user/tambah-buku', [AudioBukuController::class, 'store'])->name('user.books.store');