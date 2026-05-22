@extends('layouts.app')

@section('content')
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold mb-2">Read Assist</h1>
        <p class="text-slate-400 mb-6">Sistem bantuan membaca untuk memahami teks dengan lebih mudah.</p>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('read.process') }}" class="card bg-base-300/50 border border-white/10 shadow-md p-6">
            @csrf

            <div class="form-control w-full mb-4">
                <label for="text" class="label-text text-slate-300 text-sm font-medium mb-2 block">Masukkan teks bacaan:</label>
                <textarea id="text" name="text" rows="10" class="textarea textarea-bordered w-full bg-base-300/60 text-white">{{ old('text', $text ?? '') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Proses Teks</button>
        </form>

        @isset($result)
            <div class="card bg-base-300/50 border border-white/10 shadow-md p-6 mt-8">
                <h2 class="text-2xl font-bold mb-4">Hasil Analisis</h2>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-base-300/50 border border-white/5 rounded-lg p-4 text-center">
                        <span class="text-3xl font-extrabold text-blue-400 block">{{ $result['word_count'] }}</span>
                        <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Jumlah Kata</span>
                    </div>
                    <div class="bg-base-300/50 border border-white/5 rounded-lg p-4 text-center">
                        <span class="text-3xl font-extrabold text-indigo-400 block">{{ $result['sentence_count'] }}</span>
                        <span class="text-xs uppercase text-slate-400 font-semibold tracking-wider">Jumlah Kalimat</span>
                    </div>
                </div>

                <h3 class="text-lg font-bold mb-2 text-indigo-400">Ringkasan</h3>
                <p class="text-slate-300 mb-6">{{ $result['summary'] ?: 'Ringkasan belum tersedia.' }}</p>

                <h3 class="text-lg font-bold mb-2 text-indigo-400">Kata Kunci</h3>
                @if ($result['keywords']->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach ($result['keywords'] as $keyword)
                            <span class="badge badge-outline badge-primary text-xs">{{ $keyword }}</span>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400">Belum ada kata kunci.</p>
                @endif
            </div>
        @endisset
    </div>
@endsection
