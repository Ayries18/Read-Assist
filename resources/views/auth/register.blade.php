@extends('layouts.app')

@section('content')
    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Daftar Akun Baru</h2>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/register" id="register-form">
            @csrf
            <input type="hidden" name="role" value="user">

            <div class="form-control w-full mb-4">
                <label for="name" class="label-text text-slate-300 text-sm font-medium mb-2 block">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 @error('name') border-red-500 @enderror" placeholder="Masukkan nama lengkap Anda" required>
                @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full mb-4">
                <label for="email" class="label-text text-slate-300 text-sm font-medium mb-2 block">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 @error('email') border-red-500 @enderror" placeholder="nama@email.com" required>
                @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full mb-4">
                <label for="password" class="label-text text-slate-300 text-sm font-medium mb-2 block">Password</label>
                <input type="password" id="password" name="password" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 @error('password') border-red-500 @enderror" placeholder="Minimal 6 karakter" required>
                @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full mb-4">
                <label for="password_confirmation" class="label-text text-slate-300 text-sm font-medium mb-2 block">Konfirmasi Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500 @error('password_confirmation') border-red-500 @enderror" placeholder="Ketik ulang password" required>
                @error('password_confirmation') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full mt-5" id="register-btn">
                <span id="register-btn-text">Daftar Sekarang</span>
                <span id="register-spinner" class="loading loading-spinner loading-sm" style="display: none;"></span>
            </button>
        </form>

        <p class="text-sm text-slate-300 text-center mt-6">
            Sudah memiliki akun? 
            <a href="{{ route('login') }}" class="text-indigo-400 font-bold underline hover:text-indigo-300">Login di sini</a>
        </p>
    </div>

    <script>
        document.getElementById('register-form')?.addEventListener('submit', function() {
            const btn = document.getElementById('register-btn');
            const text = document.getElementById('register-btn-text');
            const spinner = document.getElementById('register-spinner');
            btn.disabled = true;
            text.textContent = 'Mendaftarkan...';
            spinner.style.display = 'inline-block';
        });
    </script>
@endsection
