@if ($paginator->hasPages())
<nav aria-label="Pagination" class="aruvi-pagination">
    <ul class="aruvi-pag-list">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="aruvi-pag-item disabled">
                <span class="aruvi-pag-btn" aria-disabled="true">
                    <i class="bi bi-chevron-left"></i>
                </span>
            </li>
        @else
            <li class="aruvi-pag-item">
                <a class="aruvi-pag-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="aruvi-pag-item disabled">
                    <span class="aruvi-pag-btn aruvi-pag-dots">{{ $element }}</span>
                </li>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="aruvi-pag-item active">
                            <span class="aruvi-pag-btn" aria-current="page">{{ $page }}</span>
                        </li>
                    @else
                        <li class="aruvi-pag-item">
                            <a class="aruvi-pag-btn" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="aruvi-pag-item">
                <a class="aruvi-pag-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        @else
            <li class="aruvi-pag-item disabled">
                <span class="aruvi-pag-btn" aria-disabled="true">
                    <i class="bi bi-chevron-right"></i>
                </span>
            </li>
        @endif

    </ul>

    <p class="aruvi-pag-info">
        Showing <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong>
        of <strong>{{ $paginator->total() }}</strong>
    </p>
</nav>
@endif
