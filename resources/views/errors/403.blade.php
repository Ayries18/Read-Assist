@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center py-20 text-center">
    <span class="text-8xl font-extrabold text-red-400/30 mb-6">403</span>
    <h1 class="text-3xl font-bold text-white mb-3">Akses Dilarang</h1>
    <p class="text-slate-400 max-w-md mb-8">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
</div>
@endsection
