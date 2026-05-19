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
                    <p style="color: var(--text-muted); font-size: 0.78rem;">Deskripsi akan diekstrak otomatis.</p>
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

    <!-- ⚙️ Processing Overlay (Automated Feedback) -->
    <div id="processing-overlay" style="display: none; position: fixed; inset: 0; background: rgba(9, 9, 11, 0.9); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="card" style="max-width: 480px; width: 90%; padding: 2.5rem; text-align: center; border-color: var(--accent-primary); box-shadow: 0 0 30px rgba(99, 102, 241, 0.25);">
            <div class="card-content">
                <!-- Spinner -->
                <div style="width: 60px; height: 60px; border: 4px solid rgba(255, 255, 255, 0.1); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1.5rem auto;"></div>
                
                <h3 class="text-gradient" style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">Memproses Buku Otomatis</h3>
                <p style="color: var(--text-secondary); font-size: 0.88rem; margin-bottom: 1.8rem;">Mohon tunggu, sistem sedang mengolah dokumen Anda.</p>
                
                <!-- Steps List -->
                <div style="text-align: left; background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border-glass); border-radius: 8px; padding: 1.2rem; display: flex; flex-direction: column; gap: 0.8rem; font-size: 0.88rem;">
                    <div id="step-upload" style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-muted); transition: color 0.3s;">
                        <span id="icon-upload">⏳</span>
                        <span>Mengunggah file buku digital ke server...</span>
                    </div>
                    <div id="step-extract" style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-muted); transition: color 0.3s;">
                        <span id="icon-extract">⏳</span>
                        <span>Mengekstrak teks halaman dokumen (PDF/EPUB)...</span>
                    </div>
                    <div id="step-audio" style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-muted); transition: color 0.3s;">
                        <span id="icon-audio">⏳</span>
                        <span>Menghubungkan pemutar audio sintesis...</span>
                    </div>
                    <div id="step-qr" style="display: flex; align-items: center; gap: 0.8rem; color: var(--text-muted); transition: color 0.3s;">
                        <span id="icon-qr">⏳</span>
                        <span>Membangun QR Code unik otomatis...</span>
                    </div>
                </div>
            </div>
        </div>
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

        // Form submission loading indicators
        const formSubmit = document.querySelector('form');
        const overlay = document.getElementById('processing-overlay');
        
        const stepUpload = document.getElementById('step-upload');
        const stepExtract = document.getElementById('step-extract');
        const stepAudio = document.getElementById('step-audio');
        const stepQr = document.getElementById('step-qr');

        const iconUpload = document.getElementById('icon-upload');
        const iconExtract = document.getElementById('icon-extract');
        const iconAudio = document.getElementById('icon-audio');
        const iconQr = document.getElementById('icon-qr');

        formSubmit.addEventListener('submit', function (e) {
            if (!bookFile.files.length) {
                return;
            }
            
            overlay.style.display = 'flex';

            // Step 1: Active
            stepUpload.style.color = 'var(--text-primary)';
            iconUpload.innerText = '⚙️';

            // Step 2
            setTimeout(() => {
                stepUpload.style.color = 'var(--accent-success)';
                iconUpload.innerText = '✓';
                stepExtract.style.color = 'var(--text-primary)';
                iconExtract.innerText = '⚙️';
            }, 1800);

            // Step 3
            setTimeout(() => {
                stepExtract.style.color = 'var(--accent-success)';
                iconExtract.innerText = '✓';
                stepAudio.style.color = 'var(--text-primary)';
                iconAudio.innerText = '⚙️';
            }, 3600);

            // Step 4
            setTimeout(() => {
                stepAudio.style.color = 'var(--accent-success)';
                iconAudio.innerText = '✓';
                stepQr.style.color = 'var(--text-primary)';
                iconQr.innerText = '⚙️';
            }, 5400);
        });
    </script>
@endsection
