@extends('layouts.app')

@section('content')
    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8 text-center">
        <h2 class="text-2xl font-bold mb-4">Permintaan Reset Password</h2>

        <p class="text-slate-300 text-sm mb-4">
            Instruksi pemulihan kata sandi telah diproses untuk akun <strong>{{ $validated['email'] }}</strong>.
        </p>

        @if(!empty($resetUrl))
            <div class="bg-amber-950/40 border border-amber-500/30 rounded-lg p-4 mb-6 break-all text-left">
                <p class="text-xs font-semibold text-amber-300 mb-2">Mode Pengembang (Local / Debug):</p>
                <a href="{{ $resetUrl }}" class="text-amber-400 underline text-sm font-mono">{{ $resetUrl }}</a>
            </div>

            <a href="{{ $resetUrl }}" class="btn btn-primary">Buka Form Reset Password</a>
        @else
            <div class="bg-base-300/80 border border-white/10 rounded-lg p-4 mb-6 text-sm text-slate-300">
                Silakan periksa kotak masuk atau folder spam email Anda untuk tautan verifikasi.
            </div>
        @endif

        <p class="text-sm text-slate-400 mt-6">
            <a href="{{ route('login') }}" class="text-indigo-400 font-bold underline hover:text-indigo-300">Kembali ke Login</a>
        </p>
    </div>
@endsection
