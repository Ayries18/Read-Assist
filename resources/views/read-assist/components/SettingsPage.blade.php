<div class="card bg-base-300/50 border border-white/10 shadow-md p-6 transition-all duration-300">
    <h3 class="text-lg text-white font-bold mb-5 flex items-center gap-2 pb-3 border-b border-white/5">
        <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        Pengaturan Sistem
    </h3>

    <form onsubmit="saveSystemSettings(event)" class="space-y-5">
        <!-- Dark/Light Mode Toggle & High Contrast -->
        <div class="form-control">
            <label class="label cursor-pointer flex justify-between items-center p-0 mb-1">
                <span class="label-text text-slate-300 font-medium">Mode Tampilan (Terang/Light Mode)</span>
                <input type="checkbox" id="settings-light-mode" class="toggle toggle-primary" onchange="previewSettings()">
            </label>
            <span class="text-[10px] text-slate-500">Matikan untuk menggunakan Tema Gelap (Dark Mode).</span>
        </div>

        <div class="form-control">
            <label class="label cursor-pointer flex justify-between items-center p-0 mb-1">
                <span class="label-text text-slate-300 font-medium">Kontras Tinggi (High Contrast)</span>
                <input type="checkbox" id="settings-high-contrast" class="toggle toggle-secondary" onchange="previewSettings()">
            </label>
            <span class="text-[10px] text-slate-500">Sangat direkomendasikan untuk visibilitas optimal tunanetra/low vision.</span>
        </div>

        <!-- Ukuran Font -->
        <div class="form-control w-full">
            <label class="label-text text-slate-300 font-medium mb-2 block">Ukuran Font</label>
            <select id="settings-page-font-size" onchange="previewSettings()" class="select select-bordered select-md w-full bg-base-300/60 text-white border-white/10">
                <option value="small">Kecil (Small)</option>
                <option value="medium" selected>Sedang (Medium)</option>
                <option value="large">Besar (Large)</option>
                <option value="xlarge">Sangat Besar (X-Large)</option>
            </select>
        </div>

        <!-- Bahasa Sistem -->
        <div class="form-control w-full">
            <label class="label-text text-slate-300 font-medium mb-2 block">Bahasa Sistem</label>
            <select id="settings-system-lang" class="select select-bordered select-md w-full bg-base-300/60 text-white border-white/10">
                <option value="id" selected>Bahasa Indonesia</option>
                <option value="en">English (Inggris)</option>
            </select>
        </div>

        <!-- Simpan Riwayat Analisis Toggle -->
        <div class="form-control">
            <label class="label cursor-pointer flex justify-between items-center p-0 mb-1">
                <span class="label-text text-slate-300 font-medium">Simpan Riwayat Analisis</span>
                <input type="checkbox" id="settings-save-history" checked class="toggle toggle-accent">
            </label>
            <span class="text-[10px] text-slate-500">Riwayat analisis akan tersimpan secara lokal di browser Anda.</span>
        </div>

        <!-- Text-to-Speech Speed Slider -->
        <div class="form-control w-full">
            <div class="flex justify-between items-center mb-1">
                <label class="label-text text-slate-300 font-medium">Kecepatan Suara (TTS Rate)</label>
                <span id="tts-rate-value" class="text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-lg border border-indigo-500/20">1.0x</span>
            </div>
            <input type="range" id="settings-tts-slider" min="0.75" max="2.0" step="0.25" value="1.0" oninput="updateTTSValueLabel(this.value)" class="range range-xs range-primary w-full">
            <div class="w-full flex justify-between text-[10px] px-1 text-slate-500 mt-1">
                <span>0.75x</span>
                <span>1.0x</span>
                <span>1.25x</span>
                <span>1.5x</span>
                <span>1.75x</span>
                <span>2.0x</span>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4">
            <button type="submit" class="btn btn-primary w-full hover:-translate-y-0.5 transition-transform duration-200 shadow-lg shadow-indigo-600/20">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<script>
    function updateTTSValueLabel(value) {
        document.getElementById('tts-rate-value').innerText = value + 'x';
    }

    function previewSettings() {
        const isLight = document.getElementById('settings-light-mode').checked;
        const isHighContrast = document.getElementById('settings-high-contrast').checked;
        const fontSize = document.getElementById('settings-page-font-size').value;

        // Apply contrast theme immediately to container
        const container = document.getElementById('read-assist-container');
        if (container) {
            container.classList.remove('light-mode', 'high-contrast-mode');
            if (isHighContrast) {
                container.classList.add('high-contrast-mode');
            } else if (isLight) {
                container.classList.add('light-mode');
            }
        }

        // Apply font size preview to text area
        const textarea = document.getElementById('text');
        if (textarea) {
            textarea.classList.remove('text-xs', 'text-sm', 'text-lg', 'text-2xl');
            if (fontSize === 'small') {
                textarea.classList.add('text-xs');
            } else if (fontSize === 'medium') {
                textarea.classList.add('text-sm');
            } else if (fontSize === 'large') {
                textarea.classList.add('text-lg');
            } else if (fontSize === 'xlarge') {
                textarea.classList.add('text-2xl');
            }
        }
    }

    function saveSystemSettings(event) {
        if (event) event.preventDefault();
        
        const isLight = document.getElementById('settings-light-mode').checked;
        const isHighContrast = document.getElementById('settings-high-contrast').checked;
        const fontSize = document.getElementById('settings-page-font-size').value;
        const language = document.getElementById('settings-system-lang').value;
        const saveHistory = document.getElementById('settings-save-history').checked;
        const ttsRate = document.getElementById('settings-tts-slider').value;

        // Save to LocalStorage (keys matching other pages too)
        localStorage.setItem('read_assist_tts_rate', ttsRate);
        localStorage.setItem('read_assist_font_size', fontSize);
        
        let contrastVal = 'normal';
        if (isHighContrast) contrastVal = 'high-contrast';
        else if (isLight) contrastVal = 'light';
        localStorage.setItem('read_assist_contrast', contrastVal);

        localStorage.setItem('read_assist_lang', language);
        localStorage.setItem('read_assist_save_history', saveHistory);

        // Flash toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 z-50 p-4 rounded-xl bg-green-600 text-white text-xs font-semibold shadow-lg transition-all duration-300 transform translate-y-10 opacity-0';
        toast.innerText = 'Pengaturan berhasil disimpan!';
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

    function loadSettingsIntoFields() {
        const rate = localStorage.getItem('read_assist_tts_rate') || '1.0';
        const fontSize = localStorage.getItem('read_assist_font_size') || 'medium';
        const contrast = localStorage.getItem('read_assist_contrast') || 'normal';
        const lang = localStorage.getItem('read_assist_lang') || 'id';
        const saveHistory = localStorage.getItem('read_assist_save_history') !== 'false';

        const lightModeEl = document.getElementById('settings-light-mode');
        const highContrastEl = document.getElementById('settings-high-contrast');
        const sizeEl = document.getElementById('settings-page-font-size');
        const langEl = document.getElementById('settings-system-lang');
        const saveHistEl = document.getElementById('settings-save-history');
        const rateEl = document.getElementById('settings-tts-slider');

        if (lightModeEl) lightModeEl.checked = (contrast === 'light');
        if (highContrastEl) highContrastEl.checked = (contrast === 'high-contrast');
        if (sizeEl) sizeEl.value = fontSize;
        if (langEl) langEl.value = lang;
        if (saveHistEl) saveHistEl.checked = saveHistory;
        if (rateEl) {
            rateEl.value = rate;
            updateTTSValueLabel(rate);
        }

        previewSettings();
    }

    document.addEventListener('DOMContentLoaded', function() {
        loadSettingsIntoFields();
    });
</script>
