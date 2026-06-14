@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <a href="/katalog-audio" class="text-slate-400 font-medium text-sm inline-flex items-center gap-2">
            ← Kembali ke Katalog
        </a>
    </div>

    <div class="card bg-base-300/50 border border-white/10 shadow-xl max-w-lg mx-auto p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-white mb-6">Tambah Buku Baru</h2>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6">
{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ session('auth_role') === 'user' ? '/user/tambah-buku' : '/katalog-audio' }}" enctype="multipart/form-data">
            @csrf

            <!-- Styled File Upload Area -->
            <div class="form-control w-full mb-4">
                <span class="label-text text-slate-300 text-sm font-medium mb-2 block">File Buku (PDF/EPUB)</span>
                <label for="book_file" class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-white/10 rounded-xl p-8 sm:p-10 text-center bg-base-300/30 cursor-pointer hover:border-indigo-500 hover:bg-indigo-500/5 transition-all duration-200 w-full" id="dropzone">
                    <span class="text-indigo-400 mb-1 inline-block">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    </span>
                    <p class="font-semibold text-sm mb-1" id="file-status-title">Pilih file PDF atau EPUB</p>
                    <p class="text-slate-400 text-xs">Deskripsi akan diekstrak otomatis.</p>
                    <input type="file" id="book_file" name="book_file" accept=".pdf,.epub,application/pdf,application/epub+zip" required>
                </label>
            </div>

            <!-- Title field -->
            <div class="form-control w-full mb-4">
                <label for="title" class="label-text text-slate-300 text-sm font-medium mb-2 block">Judul Buku</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" class="input input-bordered w-full bg-base-300/60 text-white placeholder:text-slate-500" placeholder="Biarkan kosong untuk menggunakan nama file otomatis">
            </div>

            <button type="submit" class="btn btn-primary mt-4">
                Simpan Buku & Ekstrak Suara
            </button>
        </form>
    </div>

    <!-- Processing Overlay (Automated Feedback) -->
    <div id="processing-overlay" style="display: none; position: fixed; inset: 0; background: rgba(9, 9, 11, 0.9); z-index: 99999; align-items: center; justify-content: center; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);">
        <div class="card bg-base-300/50 border border-indigo-500/30 shadow-indigo-500/20 shadow-lg" style="max-width: 480px; width: 90%; padding: 2.5rem; text-align: center;">
                <!-- Spinner -->
                <div style="width: 60px; height: 60px; border: 4px solid rgba(255, 255, 255, 0.1); border-top-color: var(--accent-primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1.5rem auto;"></div>
                
                <h3 class="text-gradient" style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">Memproses Buku Otomatis</h3>
                <p class="text-slate-300" style="font-size: 0.88rem; margin-bottom: 1.8rem;">Mohon tunggu, sistem sedang mengolah dokumen Anda.</p>
                
                <!-- Steps List -->
                <div class="text-left bg-white/5 border border-white/10 rounded-lg p-5 flex flex-col gap-3 text-sm">
                    <div id="step-upload" class="flex items-center gap-3 text-slate-400" style="transition: color 0.3s;">
                        <span id="icon-upload" class="step-icon" style="display:inline-flex;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="m8 12 4-4 4 4"/></svg>
                        </span>
                        <span>Mengunggah file buku digital ke server...</span>
                    </div>
                    <div id="step-extract" class="flex items-center gap-3 text-slate-400" style="transition: color 0.3s;">
                        <span id="icon-extract" class="step-icon" style="display:inline-flex;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="m8 12 4-4 4 4"/></svg>
                        </span>
                        <span>Mengekstrak teks halaman dokumen (PDF/EPUB)...</span>
                    </div>
                    <div id="step-audio" class="flex items-center gap-3 text-slate-400" style="transition: color 0.3s;">
                        <span id="icon-audio" class="step-icon" style="display:inline-flex;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="m8 12 4-4 4 4"/></svg>
                        </span>
                        <span>Menghubungkan pemutar audio sintesis...</span>
                    </div>
                    <div id="step-qr" class="flex items-center gap-3 text-slate-400" style="transition: color 0.3s;">
                        <span id="icon-qr" class="step-icon" style="display:inline-flex;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="m8 12 4-4 4 4"/></svg>
                        </span>
                        <span>Membangun QR Code unik otomatis...</span>
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

            const spinnerSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="step-spinner" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" stroke-opacity="0.3"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>';
            const checkSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';

            // Step 1: Active
            stepUpload.style.color = 'var(--text-primary)';
            iconUpload.innerHTML = spinnerSvg;

            // Step 2
            setTimeout(() => {
                stepUpload.style.color = 'var(--accent-success)';
                iconUpload.innerHTML = checkSvg;
                stepExtract.style.color = 'var(--text-primary)';
                iconExtract.innerHTML = spinnerSvg;
            }, 1800);

            // Step 3
            setTimeout(() => {
                stepExtract.style.color = 'var(--accent-success)';
                iconExtract.innerHTML = checkSvg;
                stepAudio.style.color = 'var(--text-primary)';
                iconAudio.innerHTML = spinnerSvg;
            }, 3600);

            // Step 4
            setTimeout(() => {
                stepAudio.style.color = 'var(--accent-success)';
                iconAudio.innerHTML = checkSvg;
                stepQr.style.color = 'var(--text-primary)';
                iconQr.innerHTML = spinnerSvg;
            }, 5400);
        });
    </script>
@endsection
