@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <a href="/katalog-audio/{{ $audioBook->id }}" style="color: var(--text-muted); font-weight: 500; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Kembali ke Detail Buku
        </a>
    </div>

    <div class="form-card">
        <h2 class="form-title">Edit Buku</h2>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <span>✗</span> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/katalog-audio/{{ $audioBook->id }}">
            @csrf
            @method('PUT')

            <!-- Title field -->
            <div class="form-group">
                <label for="title" class="form-label">Judul Buku</label>
                <input id="title" type="text" name="title" value="{{ old('title', $audioBook->judul) }}" class="form-control" required>
            </div>

            <!-- Import description from .txt file -->
            <div class="form-group">
                <span class="form-label">Ganti Deskripsi via File Teks (.txt)</span>
                <label for="description_file" class="file-dropzone" id="dropzone" style="padding: 1.2rem;">
                    <span style="font-size: 1.5rem; margin-bottom: 0.4rem; display: inline-block;">📄</span>
                    <p style="font-weight: 600; font-size: 0.88rem; margin: 0;" id="file-status-title">Pilih file teks .txt jika ada</p>
                    <input type="file" id="description_file" accept=".txt,text/plain">
                </label>
            </div>

            <!-- Description field -->
            <div class="form-group">
                <label for="description" class="form-label">Deskripsi / Isi Buku</label>
                <textarea id="description" name="description" rows="8" class="form-control" placeholder="Tulis atau tempel deskripsi buku di sini...">{{ old('description', $audioBook->deskripsi) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                💾 Simpan Perubahan
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

            reader.readAsText(file);
        });
    </script>
@endsection
