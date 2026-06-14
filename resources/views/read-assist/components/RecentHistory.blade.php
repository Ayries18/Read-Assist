@php
    $dummyHistory = [
        [
            'judul' => 'Analisis Artikel Biologi Sel & Jaringan Hewan',
            'tanggal' => '12 Juni 2026, 14:32',
            'kata' => '542 kata',
            'isi' => "Sel hewan berbeda dengan sel tumbuhan karena tidak memiliki dinding sel dan kloroplas. Sel hewan biasanya memiliki vakuola kecil atau tidak ada sama sekali. Organel sel hewan mencakup mitokondria, ribosom, lisosom, aparatus golgi, retikulum endoplasma, dan nukleus. Jaringan pada hewan meliputi jaringan epitel, otot, saraf, dan ikat."
        ],
        [
            'judul' => 'Pengenalan Kecerdasan Buatan dan Dampak Sosial',
            'tanggal' => '10 Juni 2026, 09:15',
            'kata' => '821 kata',
            'isi' => "Kecerdasan buatan (AI) adalah simulasi proses kecerdasan manusia oleh mesin, khususnya sistem komputer. Dampak AI terhadap dunia kerja sangat signifikan, dengan beberapa profesi mulai terotomatisasi. Namun, kecerdasan buatan juga menciptakan peluang baru di bidang teknologi informasi dan analisis data."
        ],
        [
            'judul' => 'Ringkasan Dongeng Nusantara Si Kancil dan Buaya',
            'tanggal' => '05 Juni 2026, 17:45',
            'kata' => '310 kata',
            'isi' => "Pada suatu hari Kancil merasa sangat lapar dan ingin menyeberangi sungai untuk memakan buah-buahan segar di seberang sungai. Namun sungai itu dipenuhi buaya lapar. Kancil menipu buaya dengan menyuruh mereka berbaris agar kancil bisa menghitung jumlah mereka atas perintah raja hutan."
        ]
    ];
@endphp

<div class="card bg-base-300/50 border border-white/10 shadow-md p-6 mt-6 transition-all duration-300">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/5">
        <h3 id="history-section" class="text-base text-white font-semibold flex items-center gap-2">
            <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" class="text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Riwayat Analisis Terbaru
        </h3>
        <span class="badge badge-indigo text-xs">Simulasi Data</span>
    </div>

    <!-- Container untuk List / Empty State -->
    <div id="history-list-container" class="space-y-3">
        @if(count($dummyHistory) > 0)
            @foreach($dummyHistory as $index => $item)
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl bg-white/[0.02] border border-white/5 hover:bg-white/[0.05] hover:border-white/10 transition-all duration-200 gap-3 group">
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold text-white truncate group-hover:text-indigo-400 transition-colors duration-200">{{ $item['judul'] }}</div>
                        <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-400">
                            <span class="flex items-center gap-1">
                                <svg style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                {{ $item['tanggal'] }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $item['kata'] }}
                            </span>
                        </div>
                    </div>
                    <button type="button" 
                            data-isi="{{ $item['isi'] }}"
                            onclick="loadHistoryToInput(this.dataset.isi)" 
                            class="btn btn-sm btn-outline btn-indigo border-white/10 text-xs px-3 hover:bg-indigo-600 hover:border-indigo-600 hover:text-white transition-all duration-200">
                        Lihat Hasil
                    </button>
                </div>
            @endforeach
        @else
            <!-- Empty State -->
            <div class="text-center py-10">
                <div class="w-16 h-16 bg-white/[0.02] border border-white/10 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                    <svg style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h4 class="text-sm font-semibold text-white mb-1">Belum ada riwayat analisis</h4>
                <p class="text-xs text-slate-500">Teks yang Anda proses akan muncul di sini.</p>
            </div>
        @endif
    </div>
</div>

<script>
    function loadHistoryToInput(text) {
        const textEl = document.getElementById('text');
        if (textEl) {
            textEl.value = text;
            textEl.focus();
            // Smooth scroll to top of page / text area
            window.scrollTo({
                top: textEl.getBoundingClientRect().top + window.scrollY - 100,
                behavior: 'smooth'
            });
            
            // Show a quick visual toast notification
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-4 right-4 z-50 p-4 rounded-xl bg-indigo-600 text-white text-xs font-semibold shadow-lg transition-all duration-300 transform translate-y-10 opacity-0';
            toast.innerText = 'Teks riwayat berhasil dimuat ke editor!';
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
    }
</script>
