@extends('layouts.app')

@section('content')
    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-center mb-6">Reset Password</h2>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="text-slate-300 text-sm mb-6 text-center">
            Masukkan password baru untuk akun <strong>{{ $record->email }}</strong> ({{ ucfirst($record->role) }}).
        </p>

        <form method="POST" action="/reset-password">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-control w-full mb-4">
                <label for="password" class="label-text text-slate-300 text-sm font-medium mb-2 block">Password Baru</label>
                <input type="password" id="password" name="password" class="input input-bordered w-full bg-base-300/60 text-white" minlength="6" required>
                @error('password') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="form-control w-full mb-4">
                <label for="password_confirmation" class="label-text text-slate-300 text-sm font-medium mb-2 block">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="input input-bordered w-full bg-base-300/60 text-white" required>
            </div>

            <button type="submit" class="btn btn-primary w-full mt-4">Reset Password</button>
        </form>
    </div>
@endsection
