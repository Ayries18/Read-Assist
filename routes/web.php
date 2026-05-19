<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AudioBookController;
use App\Http\Controllers\ReadAssistController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AudioBookController::class, 'landing'])->name('home');

Route::post('/proses-teks', [ReadAssistController::class, 'process'])->name('read.process');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
Route::get('/user/dashboard', [AuthController::class, 'userDashboard'])->name('user.dashboard');

Route::get('/katalog-audio', [AudioBookController::class, 'index'])->name('audio-books.index');
Route::get('/katalog-audio/tambah', [AudioBookController::class, 'create'])->name('audio-books.create');
Route::post('/katalog-audio', [AudioBookController::class, 'store'])->name('audio-books.store');
Route::get('/katalog-audio/{audioBook}/edit', [AudioBookController::class, 'edit'])->name('audio-books.edit');
Route::get('/katalog-audio/{audioBook}', [AudioBookController::class, 'show'])->name('audio-books.show');
Route::put('/katalog-audio/{audioBook}', [AudioBookController::class, 'update'])->name('audio-books.update');
Route::delete('/katalog-audio/{audioBook}', [AudioBookController::class, 'destroy'])->name('audio-books.destroy');

Route::get('/qr-audio/{token}', [AudioBookController::class, 'play'])->name('audio-books.play');
Route::get('/user/tambah-buku', [AudioBookController::class, 'create'])->name('user.books.create');
Route::post('/user/tambah-buku', [AudioBookController::class, 'store'])->name('user.books.store');