@extends('layouts.app')

@section('content')
    <div style="max-width: 600px; margin: 0 auto;">
        <div class="card bg-base-300/50 border border-white/10 shadow-xl p-6 sm:p-8">
            <h2 class="text-2xl font-bold mb-6">Profil Saya</h2>

            @if ($errors->any())
                <div class="alert alert-error shadow-lg mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/profile">
                @csrf
                @method('PUT')

                <div class="form-control w-full mb-4">
                    <label for="name" class="label-text text-slate-300 text-sm font-medium mb-2 block">Nama</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $role === 'admin' ? $account->nama : $account->name) }}" class="input input-bordered w-full bg-base-300/60 text-white" required>
                    @error('name') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label for="email" class="label-text text-slate-300 text-sm font-medium mb-2 block">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $account->email) }}" class="input input-bordered w-full bg-base-300/60 text-white" required>
                    @error('email') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">Simpan Perubahan</button>
            </form>
        </div>

        <div class="card bg-base-300/50 border border-white/10 shadow-xl p-6 sm:p-8 mt-6">
            <h3 class="text-xl font-bold mb-6">Ganti Password</h3>

            <form method="POST" action="/profile/password">
                @csrf
                @method('PUT')

                <div class="form-control w-full mb-4">
                    <label for="current_password" class="label-text text-slate-300 text-sm font-medium mb-2 block">Password Saat Ini</label>
                    <input type="password" id="current_password" name="current_password" class="input input-bordered w-full bg-base-300/60 text-white" required>
                    @error('current_password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label for="password" class="label-text text-slate-300 text-sm font-medium mb-2 block">Password Baru</label>
                    <input type="password" id="password" name="password" class="input input-bordered w-full bg-base-300/60 text-white" minlength="6" required>
                    @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="form-control w-full mb-4">
                    <label for="password_confirmation" class="label-text text-slate-300 text-sm font-medium mb-2 block">Konfirmasi Password Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full bg-base-300/60 text-white" required>
                </div>

                <button type="submit" class="btn btn-ghost w-full">Ubah Password</button>
            </form>
        </div>
    </div>
@endsection
