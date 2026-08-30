<div class="card bg-white border border-black/10 shadow-md p-6 mt-6 transition-all duration-300">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-black/10">
        <h3 id="history-section" class="text-base text-black font-semibold flex items-center gap-2">
            <svg style="width: 20px; height: 20px; min-width: 20px; min-height: 20px; flex-shrink: 0;" class="text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Riwayat Analisis Terbaru
        </h3>
        <span class="badge badge-sm text-xs" style="background:#b8860b;color:#000;border-color:#000;">Lokal</span>
    </div>

    <div id="history-list-container" class="space-y-3">
        <!-- Rendered via JS -->
    </div>
</div>

<script>
    (function() {
        var HISTORY_KEY = 'read_assist_history';

        function renderHistory() {
            var container = document.getElementById('history-list-container');
            if (!container) return;
            var list = [];
            try { list = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]'); } catch (e) {}
            container.innerHTML = '';

            if (!list.length) {
                container.innerHTML = '<div class="text-center py-10">' +
                    '<div class="w-16 h-16 bg-black/5 border border-black/10 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">' +
                        '<svg style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">' +
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />' +
                        '</svg>' +
                    '</div>' +
                    '<h4 class="text-sm font-semibold text-black mb-1">Belum ada riwayat analisis</h4>' +
                    '<p class="text-xs text-slate-600">Teks yang Anda proses akan muncul di sini.</p>' +
                '</div>';
                return;
            }

            var recent = list.slice(-5).reverse();
            recent.forEach(function(item) {
                var card = document.createElement('div');
                card.className = 'flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl bg-black/5 border border-black/10 hover:bg-black/10 hover:border-black/20 transition-all duration-200 gap-3 group';

                card.innerHTML = '<div class="flex-1 min-w-0">' +
                    '<div class="text-sm font-semibold text-black truncate group-hover:text-[#b8860b] transition-colors duration-200">' + escapeHtml(item.judul || 'Teks Tanpa Judul') + '</div>' +
                    '<div class="flex items-center gap-3 mt-1.5 text-xs text-slate-600">' +
                        '<span class="flex items-center gap-1">' +
                            '<svg style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />' +
                            '</svg>' +
                            escapeHtml(item.tanggal || '') +
                        '</span>' +
                        '<span class="flex items-center gap-1">' +
                            '<svg style="width: 14px; height: 14px; min-width: 14px; min-height: 14px; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">' +
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />' +
                            '</svg>' +
                            escapeHtml(item.kata || '') +
                        '</span>' +
                    '</div>' +
                '</div>' +
                '<button type="button" data-isi="' + escapeAttr(item.isi || '') + '" onclick="loadHistoryToInput(this.dataset.isi)" ' +
                    'class="btn btn-sm border-2 border-black text-xs px-3 hover:border-[#b8860b] hover:bg-[#b8860b] hover:text-white transition-all duration-200">' +
                    'Muat Teks' +
                '</button>';

                container.appendChild(card);
            });
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str || ''));
            return div.innerHTML;
        }

        function escapeAttr(str) {
            return (str || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        window.loadHistoryToInput = function(text) {
            var textEl = document.getElementById('text');
            if (textEl) {
                textEl.value = text || '';
                textEl.focus();
                window.scrollTo({ top: textEl.getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth' });
                var toast = document.createElement('div');
                toast.className = 'fixed bottom-4 right-4 z-50 p-4 rounded-xl text-xs font-semibold shadow-lg transition-all duration-300 transform translate-y-10 opacity-0';
                toast.style.background = '#b8860b';
                toast.style.color = '#fff';
                toast.innerText = 'Teks riwayat berhasil dimuat ke editor!';
                document.body.appendChild(toast);
                setTimeout(function() { toast.classList.remove('translate-y-10', 'opacity-0'); }, 100);
                setTimeout(function() {
                    toast.classList.add('translate-y-10', 'opacity-0');
                    setTimeout(function() { document.body.removeChild(toast); }, 300);
                }, 3000);
            }
        };

        renderHistory();
    })();
</script>
