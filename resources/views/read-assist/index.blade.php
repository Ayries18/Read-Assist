@extends('layouts.app')

@section('content')
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }

        /* Reading Settings Classes */
        .high-contrast-mode {
            background: #000000 !important;
            color: #ffff00 !important;
            border-color: #ffff00 !important;
        }
        .high-contrast-mode h1,
        .high-contrast-mode h2,
        .high-contrast-mode h3,
        .high-contrast-mode h4,
        .high-contrast-mode p,
        .high-contrast-mode span,
        .high-contrast-mode label,
        .high-contrast-mode strong,
        .high-contrast-mode a {
            color: #ffff00 !important;
        }
        .high-contrast-mode button,
        .high-contrast-mode select,
        .high-contrast-mode input,
        .high-contrast-mode textarea {
            background: #000 !important;
            color: #ffff00 !important;
            border: 2px solid #ffff00 !important;
        }
        
        .light-mode {
            background: #ffffff !important;
            color: #1f2937 !important;
            border-color: #e5e7eb !important;
        }
        .light-mode h1,
        .light-mode h2,
        .light-mode h3,
        .light-mode h4,
        .light-mode p,
        .light-mode span,
        .light-mode label,
        .light-mode strong,
        .light-mode a {
            color: #1f2937 !important;
        }
        .light-mode button,
        .light-mode select,
        .light-mode input,
        .light-mode textarea {
            background: #f3f4f6 !important;
            color: #1f2937 !important;
            border-color: #d1d5db !important;
        }

        /* Font Sizing */
        .font-size-small { font-size: 0.875rem !important; }
        .font-size-medium { font-size: 1rem !important; }
        .font-size-large { font-size: 1.25rem !important; }
        .font-size-xlarge { font-size: 1.5rem !important; }
    </style>

    <div id="read-assist-container" class="max-w-3xl mx-auto p-4 sm:p-6 rounded-2xl transition-all duration-300 animate-fade-in">
        <!-- Header Section -->
        <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-white/5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo-horizontal.svg') }}" alt="ReadAssist" class="h-8 w-auto object-contain">
                <span class="text-xl font-bold text-white tracking-tight hidden sm:inline-block">Read Assist</span>
            </div>

            <!-- Profile and Settings Gear -->
            <div class="flex items-center gap-2">
                <!-- Compact Profile Card -->
                <div class="flex items-center gap-2 bg-white/[0.02] border border-white/5 pl-1.5 pr-2.5 py-1 rounded-xl text-xs">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center font-bold text-[10px] text-white shadow-inner
                        @if(session('auth_role') === 'admin')
                            bg-red-500/20 text-red-400 border border-red-500/30
                        @elseif(session('auth_role') === 'user')
                            bg-indigo-500/20 text-indigo-400 border border-indigo-500/30
                        @else
                            bg-slate-500/20 text-slate-400 border border-slate-500/30
                        @endif">
                        @if(session('auth_role') === 'admin')
                            AD
                        @elseif(session('auth_role') === 'user')
                            {{ strtoupper(substr(session('auth_name', 'U'), 0, 2)) }}
                        @else
                            GS
                        @endif
                    </div>
                    <div class="flex flex-col">
                        <span class="text-slate-300 font-semibold leading-none">{{ session('auth_name', 'Tamu (Guest)') }}</span>
                        <span class="text-[9px] text-slate-500 font-medium leading-none mt-0.5">{{ ucfirst(session('auth_role', 'guest')) }}</span>
                    </div>
                </div>

                <!-- Settings Gear Icon -->
                <button type="button" onclick="document.getElementById('settings_modal').showModal()" class="btn btn-square btn-sm btn-ghost hover:bg-white/5 text-slate-400 hover:text-white transition-all duration-150 rounded-xl" title="Pengaturan Membaca">
                    <svg style="width: 18px; height: 18px; min-width: 18px; min-height: 18px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- App Description -->
        <div class="mb-6">
            <h2 class="text-xl font-bold text-white mb-1">Asisten Baca Cerdik</h2>
            <p class="text-slate-400 text-sm">Gunakan editor di bawah untuk memproses teks bacaan Anda. Dapatkan ringkasan instan, jumlah kata, dan ekstraksi kata kunci secara otomatis.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-error shadow-lg mb-6 rounded-xl text-sm py-3">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Main Text Input Form -->
        <form method="POST" action="{{ route('read.process') }}" class="mb-6">
            @csrf

            <div class="relative flex flex-col bg-white/[0.02] border border-white/10 rounded-2xl p-4 hover:border-purple-500/20 focus-within:border-purple-500/60 focus-within:ring-2 focus-within:ring-purple-500/15 transition-all duration-300">
                <textarea id="text" name="text" rows="11" class="w-full bg-transparent resize-none border-0 p-0 text-white placeholder-slate-500 focus:ring-0 focus:outline-none text-base leading-relaxed" placeholder="Tulis atau tempel teks Anda di sini (artikel, bab buku, atau catatan untuk dianalisis)...">{{ old('text', $text ?? '') }}</textarea>
                
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-white/5">
                    <div class="text-xs text-slate-500 font-medium">
                        <span id="char-count">0</span> karakter
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm bg-purple-600 hover:bg-purple-700 border-none text-white px-5 rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-purple-500/20 flex items-center gap-1.5 font-bold text-xs uppercase tracking-wider">
                        <span>Proses Teks</span>
                        <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>
            </div>
        </form>

        <!-- Analysis Results Section -->
        @isset($result)
            <div class="border border-white/10 bg-white/[0.01] rounded-2xl p-6 transition-all duration-300 animate-fade-in mt-6">
                <!-- Result Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5 pb-4 border-b border-white/5">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg style="width: 18px; height: 18px;" class="text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Hasil Analisis
                    </h2>
                    <div class="flex items-center gap-1.5 bg-white/[0.02] border border-white/5 px-2.5 py-1 rounded-lg text-[10px] text-slate-400 font-semibold uppercase tracking-wider">
                        <span>Dianalisis oleh:</span>
                        @if(session('auth_role') === 'admin')
                            <span class="text-red-400 font-bold">Admin</span>
                        @elseif(session('auth_role') === 'user')
                            <span class="text-indigo-400 font-bold">User</span>
                        @else
                            <span class="text-slate-400 font-bold">Guest</span>
                        @endif
                    </div>
                </div>

                <!-- Word & Sentence count cards -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="border border-white/5 bg-white/[0.01] rounded-xl p-4 hover:border-purple-500/10 transition-all duration-200">
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Jumlah Kata</span>
                        <span class="text-2xl font-extrabold text-purple-400 block mt-1">{{ $result['word_count'] }}</span>
                    </div>
                    <div class="border border-white/5 bg-white/[0.01] rounded-xl p-4 hover:border-purple-500/10 transition-all duration-200">
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider block">Jumlah Kalimat</span>
                        <span class="text-2xl font-extrabold text-indigo-400 block mt-1">{{ $result['sentence_count'] }}</span>
                    </div>
                </div>

                <!-- Summary -->
                <div class="mb-6">
                    <h3 class="text-xs font-bold text-purple-400 mb-2 tracking-wide uppercase">Ringkasan</h3>
                    <div id="display-summary" class="text-slate-300 leading-relaxed text-sm bg-white/[0.01] border border-white/5 rounded-xl p-4">
                        {{ $result['summary'] ?: 'Ringkasan belum tersedia.' }}
                    </div>
                </div>

                <!-- Keywords -->
                <div>
                    <h3 class="text-xs font-bold text-purple-400 mb-2 tracking-wide uppercase">Kata Kunci</h3>
                    @if ($result['keywords']->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach ($result['keywords'] as $keyword)
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-500/5 text-purple-300 border border-purple-500/10 hover:bg-purple-500/15 transition-all duration-150 cursor-default">
                                    {{ $keyword }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-slate-500 italic">Belum ada kata kunci yang terdeteksi.</p>
                    @endif
                </div>
            </div>
        @endisset
    </div>

    <!-- Settings Dialog Modal (Hidden/Sleek) -->
    <dialog id="settings_modal" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-[#0d0e12] border border-white/10 rounded-2xl shadow-2xl p-6 max-w-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-base text-white flex items-center gap-2">
                    <svg style="width: 18px; height: 18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                    Pengaturan Membaca
                </h3>
                <form method="dialog">
                    <button class="btn btn-sm btn-circle btn-ghost text-slate-400 hover:text-white">✕</button>
                </form>
            </div>

            <div class="space-y-4">
                <!-- Kecepatan TTS -->
                <div class="form-control">
                    <label class="label pt-0 pb-1">
                        <span class="label-text text-slate-400 font-bold text-[10px] uppercase tracking-wider">Kecepatan Suara (TTS)</span>
                    </label>
                    <select id="setting-tts-rate" onchange="saveReaderSetting('rate', this.value)" class="select select-bordered bg-base-300/40 text-white text-sm focus:border-purple-500 w-full rounded-xl">
                        <option value="0.75">Lambat (0.75x)</option>
                        <option value="1.0">Normal (1.0x)</option>
                        <option value="1.25">Sedikit Cepat (1.25x)</option>
                        <option value="1.5">Cepat (1.5x)</option>
                        <option value="2.0">Sangat Cepat (2.0x)</option>
                    </select>
                </div>

                <!-- Ukuran Huruf -->
                <div class="form-control">
                    <label class="label pt-0 pb-1">
                        <span class="label-text text-slate-400 font-bold text-[10px] uppercase tracking-wider">Ukuran Teks Hasil</span>
                    </label>
                    <select id="setting-font-size" onchange="saveReaderSetting('fontSize', this.value)" class="select select-bordered bg-base-300/40 text-white text-sm focus:border-purple-500 w-full rounded-xl">
                        <option value="small">Kecil</option>
                        <option value="medium">Sedang</option>
                        <option value="large">Besar</option>
                        <option value="xlarge">Sangat Besar</option>
                    </select>
                </div>

                <!-- Tema Kontras -->
                <div class="form-control">
                    <label class="label pt-0 pb-1">
                        <span class="label-text text-slate-400 font-bold text-[10px] uppercase tracking-wider">Tema Kontras</span>
                    </label>
                    <select id="setting-contrast" onchange="saveReaderSetting('contrast', this.value)" class="select select-bordered bg-base-300/40 text-white text-sm focus:border-purple-500 w-full rounded-xl">
                        <option value="normal">Normal Dark</option>
                        <option value="light">Light Mode</option>
                        <option value="high-contrast">Kontras Tinggi (Kuning-Hitam)</option>
                    </select>
                </div>
            </div>
        </div>
    </dialog>

    <script>
        // Character counter
        const textarea = document.getElementById('text');
        const charCountSpan = document.getElementById('char-count');

        if (textarea && charCountSpan) {
            const updateCount = () => {
                charCountSpan.textContent = textarea.value.length;
            };
            textarea.addEventListener('input', updateCount);
            // Run initially
            updateCount();
        }

        // Settings managers
        function saveReaderSetting(key, value) {
            if (key === 'rate') {
                localStorage.setItem('read_assist_tts_rate', value);
            } else if (key === 'fontSize') {
                localStorage.setItem('read_assist_font_size', value);
            } else if (key === 'contrast') {
                localStorage.setItem('read_assist_theme', value);
            }
            applyReaderSettings();
        }

        function applyReaderSettings() {
            const rate = localStorage.getItem('read_assist_tts_rate') || '1.0';
            const fontSize = localStorage.getItem('read_assist_font_size') || 'medium';
            const contrast = localStorage.getItem('read_assist_theme') || 'normal';

            // Sync select menus
            const selectRate = document.getElementById('setting-tts-rate');
            const selectFontSize = document.getElementById('setting-font-size');
            const selectContrast = document.getElementById('setting-contrast');

            if (selectRate) selectRate.value = rate;
            if (selectFontSize) selectFontSize.value = fontSize;
            if (selectContrast) selectContrast.value = contrast;

            // Apply contrast mode classes
            const container = document.getElementById('read-assist-container');
            if (container) {
                container.classList.remove('high-contrast-mode', 'light-mode');
                if (contrast === 'high-contrast') {
                    container.classList.add('high-contrast-mode');
                } else if (contrast === 'light') {
                    container.classList.add('light-mode');
                }
            }

            // Apply font size class to summary display if it exists
            const summaryDiv = document.getElementById('display-summary');
            if (summaryDiv) {
                summaryDiv.classList.remove('font-size-small', 'font-size-medium', 'font-size-large', 'font-size-xlarge');
                summaryDiv.classList.add('font-size-' + fontSize);
            }
        }

        document.addEventListener('DOMContentLoaded', applyReaderSettings);
    </script>
@endsection
