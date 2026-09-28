@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Navigasi halaman">
        <p class="pagination__summary">
            Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        </p>

        <div class="pagination__list">
            @if ($paginator->onFirstPage())
                <span class="pagination__item pagination__item--disabled"><i class="bi bi-chevron-left"></i></span>
            @else
                <a class="pagination__item" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bi bi-chevron-left"></i></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination__item pagination__item--disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination__item pagination__item--active">{{ $page }}</span>
                        @else
                            <a class="pagination__item" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination__item" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="bi bi-chevron-right"></i></a>
            @else
                <span class="pagination__item pagination__item--disabled"><i class="bi bi-chevron-right"></i></span>
            @endif
        </div>
    </nav>
@endif
