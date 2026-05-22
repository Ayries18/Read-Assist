@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <a href="/katalog-audio/{{ $audioBook->id }}" class="text-slate-400 font-medium text-sm inline-flex items-center gap-2">
            ← Kembali ke Detail Buku
        </a>
    </div>

    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Edit Buku</h2>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/katalog-audio/{{ $audioBook->id }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Title field -->
            <div class="form-control w-full mb-4">
                <label for="title" class="label-text text-slate-300 text-sm font-medium mb-2 block">Judul Buku</label>
                <input id="title" type="text" name="title" value="{{ old('title', $audioBook->judul) }}" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500" required>
            </div>

            <!-- Import description from .txt file -->
            <div class="form-control w-full mb-4">
                <span class="label-text text-slate-300 text-sm font-medium mb-2 block">Ganti Deskripsi via File Teks (.txt)</span>
                <label for="description_file" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-white/10 rounded-xl p-8 sm:p-10 text-center bg-base-300/30 cursor-pointer hover:border-indigo-500 hover:bg-indigo-500/5 transition-all duration-200 w-full" id="dropzone" style="padding: 1.2rem;">
                    <span class="text-indigo-400 mb-1 inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                    </span>
                    <p class="font-semibold text-sm m-0" id="file-status-title">Pilih file teks .txt jika ada</p>
                    <input type="file" id="description_file" accept=".txt,text/plain" class="hidden">
                </label>
            </div>

            <!-- Description field -->
            <div class="form-control w-full mb-4">
                <label for="description" class="label-text text-slate-300 text-sm font-medium mb-2 block">Deskripsi / Isi Buku</label>
                <textarea id="description" name="description" rows="8" class="textarea textarea-bordered w-full bg-base-300/60 text-white" placeholder="Tulis atau tempel deskripsi buku di sini...">{{ old('description', $audioBook->deskripsi) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-4">
                Simpan Perubahan
            </button>
        </form>
    </div>

    <script>
        const descriptionFile = document.getElementById('description_file');
        const description = document.getElementById('description');
        const dropzone = document.getElementById('dropzone');
        const fileStatusTitle = document.getElementById('file-status-title');

        descriptionFile.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                fileStatusTitle.innerText = 'Pilih file teks .txt jika ada';
                dropzone.style.borderColor = 'var(--border-glass)';
                return;
            }

            if (file.type !== 'text/plain' && !file.name.toLowerCase().endsWith('.txt')) {
                alert('File belum bisa dibaca. Gunakan file .txt untuk mengisi deskripsi otomatis.');
                this.value = '';
                fileStatusTitle.innerText = 'Pilih file teks .txt jika ada';
                dropzone.style.borderColor = 'var(--border-glass)';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                description.value = event.target.result;
                fileStatusTitle.innerText = `Berhasil diimpor: ${file.name}`;
                dropzone.style.borderColor = 'var(--accent-success)';
            };

            reader.onerror = function () {
                alert('File gagal dibaca. Coba pilih file teks lain.');
                fileStatusTitle.innerText = 'Pilih file teks .txt jika ada';
                dropzone.style.borderColor = 'var(--border-glass)';
            };

        });
    </script>
@endsection
