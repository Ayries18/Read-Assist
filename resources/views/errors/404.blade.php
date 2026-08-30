@extends('layouts.app')

@section('content')
<div class="flex flex-col items-center justify-center py-20 text-center">
    <span class="text-8xl font-extrabold text-[#b8860b]/30 mb-6">404</span>
    <h1 class="text-3xl font-bold text-black mb-3">Halaman Tidak Ditemukan</h1>
    <p class="text-slate-600 max-w-md mb-8">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Kembali ke Beranda</a>
</div>
@endsection
