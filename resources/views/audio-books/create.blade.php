@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 1.5rem;">
        <a href="/katalog-audio" style="color: var(--text-muted); font-weight: 500; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Kembali ke Katalog
        </a>
    </div>

    <div class="form-card">
        <h2 class="form-title">Tambah Buku Baru</h2>

        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <span>✗</span> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ session('auth_role') === 'user' ? '/user/tambah-buku' : '/katalog-audio' }}" enctype="multipart/form-data">
            @csrf

            <!-- Styled File Upload Area -->
            <div class="form-group">
                <span class="form-label">File Buku (PDF/EPUB)</span>
                <label for="book_file" class="file-dropzone" id="dropzone">
                    <span class="file-icon">📁</span>
                    <p style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.2rem;" id="file-status-title">Pilih file PDF atau EPUB</p>
                    <p style="color: var(--text-muted); font-size: 0.78rem;">Maksimal file 10MB. Deskripsi akan diekstrak otomatis.</p>
                    <input type="file" id="book_file" name="book_file" accept=".pdf,.epub,application/pdf,application/epub+zip" required>
                </label>
            </div>

            <!-- Title field -->
            <div class="form-group">
                <label for="title" class="form-label">Judul Buku</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" class="form-control" placeholder="Biarkan kosong untuk menggunakan nama file otomatis">
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
                💾 Simpan Buku & Ekstrak Suara
            </button>
        </form>
    </div>

    <script>
        const bookFile = document.getElementById('book_file');
        const title = document.getElementById('title');
        const dropzone = document.getElementById('dropzone');
        const fileStatusTitle = document.getElementById('file-status-title');

        bookFile.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                fileStatusTitle.innerText = 'Pilih file PDF atau EPUB';
                dropzone.style.borderColor = 'var(--border-glass)';
                title.value = '';
                return;
            }

            const fileName = file.name.toLowerCase();

            if (!fileName.endsWith('.pdf') && !fileName.endsWith('.epub')) {
                alert('File buku harus berformat PDF atau EPUB.');
                this.value = '';
                fileStatusTitle.innerText = 'Pilih file PDF atau EPUB';
                dropzone.style.borderColor = 'var(--border-glass)';
                title.value = '';
                return;
            }

            // Highlight selected file
            fileStatusTitle.innerText = `Terpilih: ${file.name}`;
            dropzone.style.borderColor = 'var(--accent-success)';

            const fileNameWithoutExtension = file.name.replace(/\.[^/.]+$/, '');

            if (!title.value) {
                title.value = fileNameWithoutExtension;
            }
        });
    </script>
@endsection
