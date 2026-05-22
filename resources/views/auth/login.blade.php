@extends('layouts.app')

@section('content')
    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Login Ke Akun</h2>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login" id="login-form">
            @csrf

            <div class="form-control w-full mb-4">
                <label for="role" class="label-text text-slate-300 text-sm font-medium mb-2 block">Masuk sebagai</label>
                <select id="role" name="role" class="select select-bordered w-full bg-base-300/60 text-white @error('role') border-red-500 @enderror" required>
                    <option value="user" @selected(old('role') === 'user')>User</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
                @error('role') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full mb-4">
                <label for="email" class="label-text text-slate-300 text-sm font-medium mb-2 block">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 @error('email') border-red-500 @enderror" placeholder="Masukkan email Anda" required>
                @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full mb-4">
                <label for="password" class="label-text text-slate-300 text-sm font-medium mb-2 block">Password</label>
                <input type="password" id="password" name="password" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 @error('password') border-red-500 @enderror" placeholder="Masukkan password Anda" required>
                @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full mt-4" id="login-btn">
                <span id="login-btn-text">Masuk</span>
                <span id="login-spinner" class="loading loading-spinner loading-sm" style="display: none;"></span>
            </button>
        </form>

        <p class="text-sm text-slate-300 text-center mt-4">
            <a href="{{ route('password.forgot') }}" class="text-slate-400 hover:text-indigo-300 underline">Lupa Password?</a>
        </p>

        <p class="text-sm text-slate-300 text-center mt-2">
            Belum memiliki akun? 
            <a href="{{ route('register') }}" class="text-indigo-400 font-bold underline hover:text-indigo-300">Daftar Sekarang</a>
        </p>
    </div>

    <script>
        document.getElementById('login-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('login-btn');
            const text = document.getElementById('login-btn-text');
            const spinner = document.getElementById('login-spinner');
            btn.disabled = true;
            text.textContent = 'Memproses...';
            spinner.style.display = 'inline-block';
        });
    </script>
@endsection
