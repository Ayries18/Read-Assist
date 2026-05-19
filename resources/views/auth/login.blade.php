@extends('layouts.app')

@section('content')
    <div class="form-card" style="max-width: 450px;">
        <h2 class="form-title">Login Ke Akun</h2>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <span>✗</span> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <!-- Role Selector -->
            <div class="form-group">
                <label for="role" class="form-label">Masuk sebagai</label>
                <select id="role" name="role" class="form-control" required>
                    <option value="user" @selected(old('role') === 'user')>User Biasa</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin Katalog</option>
                </select>
            </div>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Masukkan email Anda" required>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem; width: 100%;">
                🔑 Masuk
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.8rem; font-size: 0.9rem; color: var(--text-secondary);">
            Belum memiliki akun? 
            <a href="{{ route('register') }}" style="color: var(--accent-primary); font-weight: bold; text-decoration: underline;">
                Daftar Sekarang
            </a>
        </p>
    </div>
@endsection
