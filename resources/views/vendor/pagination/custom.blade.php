@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="custom-pagination">
        @if ($paginator->onFirstPage())
            <span class="disabled">&laquo; Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" wire:navigate rel="prev">&laquo; Anterior</a>
        @endif

        <div class="pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" wire:navigate>{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" wire:navigate rel="next">Próximo &raquo;</a>
        @else
            <span class="disabled">Próximo &raquo;</span>
        @endif
    </nav>
@endif