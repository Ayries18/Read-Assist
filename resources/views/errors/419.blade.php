@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center py-20 text-center">
    <span class="text-8xl font-extrabold text-orange-400/30 mb-6">419</span>
    <h1 class="text-3xl font-bold text-white mb-3">Sesi Berakhir</h1>
    <p class="text-slate-400 max-w-md mb-8">Sesi Anda telah berakhir. Silakan login kembali.</p>
    <a href="{{ route('login') }}" class="btn btn-primary">Login Kembali</a>
</div>
@endsection
