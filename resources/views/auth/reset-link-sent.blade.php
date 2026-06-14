@extends('layouts.app')

@section('content')
    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8 text-center">
        <h2 class="text-2xl font-bold mb-4">Tautan Reset Password</h2>

        <p class="text-slate-300 text-sm mb-4">
            Email {{ $validated['email'] }} terdaftar sebagai <strong>{{ ucfirst($validated['role']) }}</strong>.
        </p>
        <p class="text-slate-300 text-sm mb-6">
            Karena server email tidak dikonfigurasi, gunakan tautan di bawah ini untuk mereset password:
        </p>

        <div class="bg-base-300/80 border border-white/10 rounded-lg p-4 mb-6 break-all">
            <a href="{{ $resetUrl }}" class="text-indigo-400 underline text-sm font-semibold">{{ $resetUrl }}</a>
        </div>

        <a href="{{ $resetUrl }}" class="btn btn-primary">Reset Password Sekarang</a>

        <p class="text-sm text-slate-400 mt-6">
            <a href="{{ route('login') }}" class="text-indigo-400 font-bold underline hover:text-indigo-300">Kembali ke Login</a>
        </p>
    </div>
@endsection
