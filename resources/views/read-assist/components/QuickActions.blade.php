<div class="card bg-base-300/50 border border-white/10 shadow-md p-5 mb-6 transition-all duration-300">
    <h3 class="text-base text-white font-semibold mb-4 flex items-center gap-2">
        <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" class="text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Aksi Cepat (Quick Actions)
    </h3>
    
    <div class="grid grid-cols-2 gap-3">
        <!-- Hidden file input for uploading text files -->
        <input type="file" id="quick-file-upload" class="hidden" accept=".txt,.md" onchange="handleQuickFileRead(event)">
        
        <!-- Upload File -->
        <button type="button" onclick="triggerQuickFileUpload()" class="flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-indigo-600/10 hover:border-indigo-500/30 transition-all duration-300 group text-center">
            <div class="p-2.5 rounded-lg bg-blue-500/10 text-blue-400 group-hover:bg-blue-500/20 mb-2 transition-all duration-300">
                <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-white">Upload File</span>
            <span class="text-[9px] text-slate-500 mt-0.5">.txt atau .md</span>
        </button>

        <!-- Riwayat -->
        <button type="button" onclick="scrollToHistory()" class="flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-indigo-600/10 hover:border-indigo-500/30 transition-all duration-300 group text-center">
            <div class="p-2.5 rounded-lg bg-indigo-500/10 text-indigo-400 group-hover:bg-indigo-500/20 mb-2 transition-all duration-300">
                <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-white">Riwayat</span>
            <span class="text-[9px] text-slate-500 mt-0.5">Lihat analisis lama</span>
        </button>

        <!-- Pengaturan -->
        <button type="button" onclick="switchToSettingsTab()" class="flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-indigo-600/10 hover:border-indigo-500/30 transition-all duration-300 group text-center">
            <div class="p-2.5 rounded-lg bg-purple-500/10 text-purple-400 group-hover:bg-purple-500/20 mb-2 transition-all duration-300">
                <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-white">Pengaturan</span>
            <span class="text-[9px] text-slate-500 mt-0.5">Konfigurasi sistem</span>
        </button>

        <!-- Bantuan -->
        <button type="button" onclick="showHelpModal()" class="flex flex-col items-center justify-center p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-indigo-600/10 hover:border-indigo-500/30 transition-all duration-300 group text-center">
            <div class="p-2.5 rounded-lg bg-pink-500/10 text-pink-400 group-hover:bg-pink-500/20 mb-2 transition-all duration-300">
                <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="text-xs font-semibold text-white">Bantuan</span>
            <span class="text-[9px] text-slate-500 mt-0.5">Panduan membaca</span>
        </button>
    </div>
</div>

<!-- Modal Bantuan -->
<dialog id="help_modal" class="modal">
    <div class="modal-box bg-slate-900 border border-white/10 rounded-2xl text-white">
        <h3 class="font-bold text-lg text-indigo-400 flex items-center gap-2 mb-4">
            <svg style="width: 24px; height: 24px; min-width: 24px; min-height: 24px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Panduan Menggunakan Read Assist
        </h3>
        <div class="space-y-3 text-sm text-slate-300">
            <p>1. <strong>Masukkan Teks:</strong> Tempelkan artikel atau ketik langsung di kolom input utama, atau klik <strong>Upload File</strong> untuk memuat berkas teks.</p>
            <p>2. <strong>Proses Analisis:</strong> Klik <strong>Proses Teks</strong>. AI akan menguraikan teks, menghitung kata, serta menyusun ringkasan singkat.</p>
            <p>3. <strong>Setelan Pembaca:</strong> Anda bisa memutar suara artikel (TTS) dan menyetel kecepatan baca, ukuran font, serta kontras warna di tab **Pengaturan**.</p>
        </div>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-primary btn-sm rounded-xl">Mengerti</button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function triggerQuickFileUpload() {
        document.getElementById('quick-file-upload').click();
    }

    function handleQuickFileRead(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const textarea = document.getElementById('text');
                if (textarea) {
                    textarea.value = e.target.result;
                    textarea.focus();
                    
                    // Show a quick visual toast notification
                    const toast = document.createElement('div');
                    toast.className = 'fixed bottom-4 right-4 z-50 p-4 rounded-xl bg-green-600 text-white text-xs font-semibold shadow-lg transition-all duration-300 transform translate-y-10 opacity-0';
                    toast.innerText = 'File berhasil diunggah dan dimuat!';
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.classList.remove('translate-y-10', 'opacity-0');
                    }, 100);
                    
                    setTimeout(() => {
                        toast.classList.add('translate-y-10', 'opacity-0');
                        setTimeout(() => {
                            document.body.removeChild(toast);
                        }, 300);
                    }, 3000);
                }
            };
            reader.readAsText(file);
        }
    }

    function scrollToHistory() {
        const tabDashboard = document.getElementById('tab-btn-dashboard');
        if (tabDashboard) tabDashboard.click();

        setTimeout(() => {
            const el = document.getElementById('history-section');
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 100);
    }

    function switchToSettingsTab() {
        const tabSettings = document.getElementById('tab-btn-settings');
        if (tabSettings) tabSettings.click();
    }

    function showHelpModal() {
        document.getElementById('help_modal').showModal();
    }
</script>
