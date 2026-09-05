@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation">
        {{-- Mobile view --}}
        <div class="flex items-center justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center gap-1 px-4 py-2.5 text-sm font-semibold text-slate-400 bg-[#b8860b]/10 border border-[#b8860b]/20 rounded-xl cursor-not-allowed opacity-60">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-black/10 rounded-xl transition-all duration-200 hover:border-[#b8860b]/40 hover:text-[#b8860b] active:scale-[0.97]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Sebelumnya
                </a>
            @endif

            <span class="text-sm font-medium text-slate-500">
                Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-black/10 rounded-xl transition-all duration-200 hover:border-[#b8860b]/40 hover:text-[#b8860b] active:scale-[0.97]">
                    Selanjutnya
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            @else
                <span class="inline-flex items-center gap-1 px-4 py-2.5 text-sm font-semibold text-slate-400 bg-[#b8860b]/10 border border-[#b8860b]/20 rounded-xl cursor-not-allowed opacity-60">
                    Selanjutnya
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </span>
            @endif
        </div>

        {{-- Desktop view --}}
        <div class="hidden sm:flex sm:items-center sm:justify-center">
            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 border border-black/10 bg-[#b8860b]/10 cursor-not-allowed opacity-50" aria-disabled="true" aria-label="Sebelumnya">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 border border-black/10 bg-white transition-all duration-200 hover:border-[#b8860b]/40 hover:text-[#b8860b] hover:shadow-sm active:scale-[0.95]" aria-label="Sebelumnya">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 text-sm font-medium border border-transparent" aria-disabled="true">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page">
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-bold text-black border border-[#b8860b] pagination-active">
                                        {{ $page }}
                                    </span>
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-sm font-medium text-slate-600 border border-black/10 bg-white transition-all duration-200 hover:border-[#b8860b]/40 hover:text-[#b8860b] hover:shadow-sm active:scale-[0.95]" aria-label="Ke halaman {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-600 border border-black/10 bg-white transition-all duration-200 hover:border-[#b8860b]/40 hover:text-[#b8860b] hover:shadow-sm active:scale-[0.95]" aria-label="Selanjutnya">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-400 border border-black/10 bg-[#b8860b]/10 cursor-not-allowed opacity-50" aria-disabled="true" aria-label="Selanjutnya">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
