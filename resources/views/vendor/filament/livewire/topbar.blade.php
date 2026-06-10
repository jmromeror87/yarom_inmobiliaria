@php
    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
    $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
    $hasNavigation = filament()->hasNavigation();
    $hasTenancy = filament()->hasTenancy();
@endphp

<div class="yr-topbar-ctn">
    <nav class="yr-topbar">

        {{-- ══ IZQUIERDA: Logo + colapso ══════════════════════════════════ --}}
        <div class="yr-topbar-left">

            @if ($homeUrl = filament()->getHomeUrl())
                <a href="{{ $homeUrl }}" class="yr-brand">
                    <x-filament-panels::logo />
                </a>
            @else
                <span class="yr-brand">
                    <x-filament-panels::logo />
                </span>
            @endif

            @if ($hasNavigation && ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop))
                <button
                    type="button"
                    class="yr-collapse-btn"
                    x-data="{}"
                    x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
                >
                    {{-- Ícono < cuando está abierto --}}
                    <svg x-show="$store.sidebar.isOpen" x-cloak width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                    {{-- Ícono > cuando está cerrado --}}
                    <svg x-show="! $store.sidebar.isOpen" x-cloak width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START) }}
        </div>

        {{-- ══ ESPACIO FLEXIBLE ════════════════════════════════════════════ --}}
        <div style="flex:1;"></div>

        {{-- ══ DERECHA: Hora · Estado · Buscar · Bell · Avatar ════════════ --}}
        <div
            @if ($hasTenancy)
                x-persist="topbar.end.panel-{{ filament()->getId() }}.tenant-{{ filament()->getTenant()?->getKey() }}"
            @else
                x-persist="topbar.end.panel-{{ filament()->getId() }}"
            @endif
            class="yr-topbar-right"
        >
            {{-- Reloj --}}
            <div class="yr-clock" x-data="{
                time:'--:--', date:'',
                D:['domingo','lunes','martes','miércoles','jueves','viernes','sábado'],
                M:['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'],
                tick(){let n=new Date();this.time=String(n.getHours()).padStart(2,'0')+':'+String(n.getMinutes()).padStart(2,'0');this.date=this.D[n.getDay()]+', '+n.getDate()+' de '+this.M[n.getMonth()];},
                init(){this.tick();setInterval(()=>this.tick(),1000);}
            }">
                <span class="yr-clock-time" x-text="time"></span>
                <span class="yr-clock-date" x-text="date"></span>
            </div>

            <div class="yr-divider"></div>

            {{-- Estado --}}
            <div class="yr-status">
                <span class="yr-status-dot"></span>
                <div>
                    <div class="yr-status-name">Sistema Activo</div>
                    <div class="yr-status-sub">En línea</div>
                </div>
            </div>

            <div class="yr-divider"></div>

            {{-- Buscador --}}
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}
            @if (filament()->isGlobalSearchEnabled() && filament()->getGlobalSearchPosition() === \Filament\Enums\GlobalSearchPosition::Topbar)
                @livewire(Filament\Livewire\GlobalSearch::class)
            @endif
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            {{-- Notificaciones --}}
            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications() && filament()->getDatabaseNotificationsPosition() === \Filament\Enums\DatabaseNotificationsPosition::Topbar)
                    @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                @endif

                {{-- Avatar / Usuario --}}
                @if (filament()->hasUserMenu() && filament()->getUserMenuPosition() === \Filament\Enums\UserMenuPosition::Topbar)
                    <x-filament-panels::user-menu />
                @endif
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_END) }}
        </div>

    </nav>

    <x-filament-actions::modals />
</div>
