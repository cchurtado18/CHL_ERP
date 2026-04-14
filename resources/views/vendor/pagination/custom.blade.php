@if ($paginator->hasPages())
    <nav class="flex w-full justify-center" role="navigation" aria-label="Paginación">
        <ul class="m-0 flex list-none flex-row flex-wrap items-center justify-center gap-2 p-0 sm:gap-2.5">
            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="flex list-none" aria-disabled="true" aria-label="Anterior">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border-2 border-slate-200 bg-white text-lg text-slate-400">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="flex list-none">
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Anterior"
                       class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border-2 border-[#15537c] bg-white text-lg text-[#15537c] transition hover:bg-[#15537c]/5">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="flex list-none items-center" aria-hidden="true">
                        <span class="inline-flex h-11 min-w-[2.75rem] items-center justify-center px-1 text-slate-500">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="flex list-none" aria-current="page">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border-2 border-[#15537c] bg-[#15537c] text-lg font-bold text-white">{{ $page }}</span>
                            </li>
                        @else
                            <li class="flex list-none">
                                <a href="{{ $url }}" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border-2 border-[#15537c] bg-white text-lg font-bold text-[#15537c] transition hover:bg-[#15537c]/5">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="flex list-none">
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Siguiente"
                       class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border-2 border-[#15537c] bg-white text-lg text-[#15537c] transition hover:bg-[#15537c]/5">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="flex list-none" aria-disabled="true" aria-label="Siguiente">
                    <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[10px] border-2 border-slate-200 bg-white text-lg text-slate-400">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
