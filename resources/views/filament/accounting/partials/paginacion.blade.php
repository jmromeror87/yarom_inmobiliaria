@if($entries->lastPage() > 1)
<div class="acc-pagination">
    @if($entries->onFirstPage())
        <span class="acc-page-disabled">‹</span>
    @else
        <a href="{{ $entries->previousPageUrl() }}" wire:click.prevent="gotoPage({{ $entries->currentPage() - 1 }})">‹</a>
    @endif

    @foreach($entries->getUrlRange(max(1, $entries->currentPage() - 2), min($entries->lastPage(), $entries->currentPage() + 2)) as $page => $url)
        @if($page == $entries->currentPage())
            <span class="acc-page-current">{{ $page }}</span>
        @else
            <a href="{{ $url }}" wire:click.prevent="gotoPage({{ $page }})">{{ $page }}</a>
        @endif
    @endforeach

    @if($entries->hasMorePages())
        <a href="{{ $entries->nextPageUrl() }}" wire:click.prevent="gotoPage({{ $entries->currentPage() + 1 }})">›</a>
    @else
        <span class="acc-page-disabled">›</span>
    @endif

    <span class="acc-page-info">
        {{ $entries->firstItem() }}–{{ $entries->lastItem() }} de {{ $entries->total() }}
    </span>
</div>
@endif
