@if ($paginator->hasPages())
    <div class="flexcenter">
        <div style="display: flex;">
            @if ($paginator->onFirstPage())
                <button class="btn btn-pink" disabled="">上一頁</button>
            @else
                <button class="btn btn-pink">
                    <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" aria-label="previous">&laquo; 上一頁</a>
                </button>
            @endif

            <div class="mx-5 flexcenter">第{{ $paginator->currentPage() }}/{{ $paginator->lastPage() }}頁</div>

            @if ($paginator->hasMorePages())
                <button class="btn btn-pink">
                    <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" aria-label="next">下一頁 &raquo;</a>
                </button>
            @else
                <button class="btn btn-pink" disabled="">下一頁</button>
            @endif
        </div>
    </div>
@endif
