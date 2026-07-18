@if($paginator->lastPage() > 1)
<div style="display:flex;align-items:center;justify-content:center;gap:4px;padding:14px 22px;flex-wrap:wrap;">
    @if($paginator->onFirstPage())
        <span style="padding:6px 12px;border-radius:8px;color:#cbd5e1;font-size:12.5px;font-weight:700;">‹</span>
    @else
        <a href="#" wire:click.prevent="gotoPage({{ $paginator->currentPage() - 1 }}, '{{ $pageName }}')"
           style="padding:6px 12px;border-radius:8px;color:#334155;font-size:12.5px;font-weight:700;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;">‹</a>
    @endif

    @foreach($paginator->getUrlRange(max(1, $paginator->currentPage() - 2), min($paginator->lastPage(), $paginator->currentPage() + 2)) as $page => $url)
        @if($page == $paginator->currentPage())
            <span style="padding:6px 13px;border-radius:8px;background:#E11D48;color:#fff;font-size:12.5px;font-weight:800;">{{ $page }}</span>
        @else
            <a href="#" wire:click.prevent="gotoPage({{ $page }}, '{{ $pageName }}')"
               style="padding:6px 13px;border-radius:8px;color:#334155;font-size:12.5px;font-weight:700;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;">{{ $page }}</a>
        @endif
    @endforeach

    @if($paginator->hasMorePages())
        <a href="#" wire:click.prevent="gotoPage({{ $paginator->currentPage() + 1 }}, '{{ $pageName }}')"
           style="padding:6px 12px;border-radius:8px;color:#334155;font-size:12.5px;font-weight:700;text-decoration:none;background:#f8fafc;border:1px solid #e2e8f0;">›</a>
    @else
        <span style="padding:6px 12px;border-radius:8px;color:#cbd5e1;font-size:12.5px;font-weight:700;">›</span>
    @endif

    <span style="margin-left:10px;font-size:11.5px;color:#94a3b8;font-weight:600;">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
    </span>
</div>
@endif
