@php
    $debounce = filament()->getGlobalSearchDebounce();
    $keyBindings = filament()->getGlobalSearchKeyBindings();
@endphp

<div class="yr-search-ctn" x-data="{}">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_START) }}

    <div
        x-on:focus-first-global-search-result.stop="$el.querySelector('.fi-global-search-result-link')?.focus()"
        class="yr-search-wrap"
    >
        {{-- Input limpio, sin wrapper de Filament --}}
        <div class="yr-search-field" x-id="['search']">
            <svg class="yr-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
                type="search"
                autocomplete="off"
                maxlength="1000"
                placeholder="{{ __('filament-panels::global-search.field.placeholder') }}"
                wire:model.live.debounce.{{ $debounce }}="search"
                wire:key="global-search.field.input"
                x-bind:id="$id('search')"
                x-on:keydown.down.prevent.stop="$dispatch('focus-first-global-search-result')"
                @if(count($keyBindings ?? []))
                x-mousetrap.global.{{ collect($keyBindings)->map(fn ($k) => str_replace('+', '-', $k))->implode('.') }}="document.getElementById($id('search'))?.focus()"
                @endif
                class="yr-search-input"
            />
        </div>

        {{-- Resultados --}}
        @if ($results !== null)
            <div
                x-data="{ isOpen: false }"
                x-init="$nextTick(() => isOpen = true)"
                x-on:click.away="isOpen = false"
                x-on:keydown.escape.window="isOpen = false"
                x-on:keydown.up.prevent="$focus.wrap().previous()"
                x-on:keydown.down.prevent="$focus.wrap().next()"
                x-on:open-global-search-results.window="$nextTick(() => isOpen = true)"
                x-show="isOpen"
                class="fi-global-search-results-ctn"
            >
                @if ($results->getCategories()->isEmpty())
                    <p class="fi-global-search-no-results-message">
                        {{ __('filament-panels::global-search.no_results_message') }}
                    </p>
                @else
                    <ul class="fi-global-search-results">
                        @foreach ($results->getCategories() as $group => $groupedResults)
                            <li class="fi-global-search-result-group">
                                <h3 class="fi-global-search-result-group-header">{{ $group }}</h3>
                                <ul class="fi-global-search-result-group-results">
                                    @foreach ($groupedResults as $result)
                                        <li class="fi-global-search-result">
                                            <a {{ \Filament\Support\generate_href_html($result->url) }} x-on:click="isOpen = false" class="fi-global-search-result-link">
                                                <h4 class="fi-global-search-result-heading">{{ $result->title }}</h4>
                                                @if ($result->details)
                                                    <dl class="fi-global-search-result-details">
                                                        @foreach ($result->details as $label => $value)
                                                            <div class="fi-global-search-result-detail">
                                                                <dd class="fi-global-search-result-detail-value">{{ $value }}</dd>
                                                            </div>
                                                        @endforeach
                                                    </dl>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_END) }}
</div>
