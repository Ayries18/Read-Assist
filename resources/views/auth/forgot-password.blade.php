@extends('layouts.app')

@section('content')
    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Lupa Password</h2>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="text-slate-300 text-sm mb-6 text-center">
            Masukkan email Anda. Kami akan menampilkan tautan reset password.
        </p>

        <form method="POST" action="/lupa-password">
            @csrf

            <div class="form-control w-full mb-4">
                <label for="role" class="label-text text-slate-300 text-sm font-medium mb-2 block">Akun sebagai</label>
                <select id="role" name="role" class="select select-bordered w-full bg-base-300/60 text-white" required>
                    <option value="user" @selected(old('role') === 'user')>User</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                </select>
            </div>

            <div class="form-control w-full mb-4">
                <label for="email" class="label-text text-slate-300 text-sm font-medium mb-2 block">Alamat Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full bg-base-300/60 text-white" placeholder="nama@email.com" required>
                @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full mt-4">Kirim Tautan Reset</button>
        </form>

        <p class="text-sm text-slate-300 text-center mt-6">
            <a href="{{ route('login') }}" class="text-indigo-400 font-bold underline hover:text-indigo-300">Kembali ke Login</a>
        </p>
    </div>
@endsection
