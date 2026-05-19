@extends('layouts.app')

@section('content')
    <div class="form-card" style="max-width: 500px;">
        <h2 class="form-title">Daftar Akun Baru</h2>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <span>✗</span> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/register">
            @csrf

            <!-- Role Selector -->
            <div class="form-group">
                <label for="role" class="form-label">Daftar sebagai</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user" @selected(old('role') === 'user')>User</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
            </div>

            <!-- Name Field -->
            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Masukkan nama lengkap Anda" required>
            </div>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ketik ulang password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1.2rem; width: 100%;">
                ✨ Daftar Sekarang
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.8rem; font-size: 0.9rem; color: var(--text-secondary);">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" style="color: var(--accent-primary); font-weight: bold; text-decoration: underline;">
                Login di sini
            </a>
        </p>
    </div>
@endsection
