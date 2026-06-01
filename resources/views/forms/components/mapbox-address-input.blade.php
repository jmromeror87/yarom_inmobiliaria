@php
    use Filament\Forms\Components\TextInput\Actions\HidePasswordAction;
    use Filament\Forms\Components\TextInput\Actions\ShowPasswordAction;

    $fieldWrapperView  = $getFieldWrapperView();
    $extraAlpineAttrs  = $getExtraAlpineAttributes();
    $extraAttributeBag = $getExtraAttributeBag();
    $id                = $getId();
    $isDisabled        = $isDisabled();
    $isPrefixInline    = $isPrefixInline();
    $isSuffixInline    = $isSuffixInline();
    $prefixActions     = $getPrefixActions();
    $prefixIcon        = $getPrefixIcon();
    $prefixLabel       = $getPrefixLabel();
    $suffixActions     = $getSuffixActions();
    $suffixIcon        = $getSuffixIcon();
    $suffixLabel       = $getSuffixLabel();
    $statePath         = $getStatePath();
    $placeholder       = $getPlaceholder();
    $latField          = $getLatField();
    $lngField          = $getLngField();
    $apiKey            = config('services.mapbox.key');

    // Calcular statePaths para lat y lng
    $parts    = explode('.', $statePath);
    $prefix   = implode('.', array_slice($parts, 0, -1));
    $latPath  = $latField  ? ($prefix ? $prefix.'.'.$latField  : $latField)  : ($prefix ? $prefix.'.latitud'  : 'latitud');
    $lngPath  = $lngField  ? ($prefix ? $prefix.'.'.$lngField  : $lngField)  : ($prefix ? $prefix.'.longitud' : 'longitud');

    $inputAttributes = $getExtraInputAttributeBag()
        ->merge($extraAlpineAttrs, escape: false)
        ->merge([
            'autocomplete'   => 'off',
            'autofocus'      => $isAutofocused(),
            'disabled'       => $isDisabled,
            'id'             => $id,
            'placeholder'    => filled($placeholder) ? e($placeholder) : null,
            'readonly'       => $isReadOnly(),
            'required'       => $isRequired(),
            'type'           => 'text',
            $applyStateBindingModifiers('wire:model') => $statePath,
            'x-model'        => 'query',
            'x-on:input.debounce.350ms' => 'buscar()',
            'x-on:keydown.arrow-down.prevent' => 'mover(1)',
            'x-on:keydown.arrow-up.prevent'   => 'mover(-1)',
            'x-on:keydown.enter.prevent'      => 'seleccionarActivo()',
            'x-on:keydown.escape'             => 'cerrar()',
            'x-on:focus'                      => 'abrirSiHay()',
            'x-on:blur.debounce.150ms'        => 'cerrar()',
        ], escape: false)
        ->class(['fi-input']);
@endphp

<x-dynamic-component
    :component="$fieldWrapperView"
    :field="$field"
    :inline-label-vertical-alignment="\Filament\Support\Enums\VerticalAlignment::Center"
>
    {{-- Contenedor con Alpine que gestiona el autocomplete --}}
    <div
        x-data="mapboxAddr('{{ $apiKey }}', '{{ $statePath }}', '{{ $latPath }}', '{{ $lngPath }}')"
        style="position:relative; width:100%;"
        x-on:mapbox-picked.window="onPick($event.detail)"
    >
        <x-filament::input.wrapper
            :disabled="$isDisabled"
            :inline-prefix="$isPrefixInline"
            :inline-suffix="$isSuffixInline"
            :prefix="$prefixLabel"
            :prefix-actions="$prefixActions"
            :prefix-icon="$prefixIcon"
            :suffix="$suffixLabel"
            :suffix-actions="$suffixActions"
            :suffix-icon="$suffixIcon"
            :valid="! $errors->has($statePath)"
            x-data="{}"
            x-on:focus-input.stop="$el.querySelector('input')?.focus()"
            :attributes="\Filament\Support\prepare_inherited_attributes($extraAttributeBag)->class(['fi-fo-text-input'])"
        >
            <input {{ $inputAttributes }} />
        </x-filament::input.wrapper>

        {{-- Dropdown sugerencias --}}
        <div
            x-show="abierto && sugs.length > 0"
            x-cloak
            style="position:absolute; z-index:9999; left:0; right:0; top:100%;
                   background:#fff; border:1px solid #6366f1; border-top:none;
                   border-radius:0 0 8px 8px; box-shadow:0 8px 24px rgba(0,0,0,.14);
                   max-height:260px; overflow-y:auto;"
        >
            <template x-for="(s, i) in sugs" :key="s.id">
                <div
                    x-on:mousedown.prevent="elegir(s)"
                    x-on:mouseenter="activo = i"
                    x-bind:style="i===activo ? 'background:#eef2ff' : ''"
                    style="padding:9px 14px; cursor:pointer; border-bottom:1px solid #f3f4f6;
                           display:flex; align-items:flex-start; gap:9px;"
                >
                    <svg style="width:14px;height:14px;color:#6366f1;flex-shrink:0;margin-top:3px;"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                    </svg>
                    <div>
                        <p style="font-size:13px;font-weight:600;color:#111827;margin:0;" x-text="s.text"></p>
                        <p style="font-size:11px;color:#9ca3af;margin:2px 0 0;" x-text="s.place"></p>
                    </div>
                </div>
            </template>

            <div x-on:mousedown.prevent="cerrar()"
                 style="padding:8px 14px;font-size:11px;color:#9ca3af;border-top:1px solid #f3f4f6;
                        display:flex;align-items:center;gap:6px;cursor:default;">
                <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/>
                </svg>
                Escribir manualmente — cierra sugerencias
            </div>
        </div>

        {{-- Confirmación coordenadas --}}
        <p x-show="lat" style="margin:4px 0 0;font-size:11px;color:#6b7280;display:flex;align-items:center;gap:4px;">
            <svg style="width:11px;height:11px;color:#22c55e;" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            Ubicación confirmada &nbsp;·&nbsp;
            <span x-text="lat + ', ' + lng" style="font-family:monospace;"></span>
        </p>
    </div>
</x-dynamic-component>

@once
<script>
window.mapboxAddr = function(apiKey, statePath, latPath, lngPath) {
    return {
        query:   '',
        sugs:    [],
        abierto: false,
        activo:  -1,
        lat:     '',
        lng:     '',
        _c:      null,

        init() {
            // Leer valor inicial del input que Filament ya hidrata
            this.$nextTick(() => {
                const inp = this.$el.querySelector('input');
                if (inp && inp.value) this.query = inp.value;
            });
        },

        async buscar() {
            const q = this.query.trim();
            if (q.length < 3) { this.sugs = []; this.abierto = false; return; }
            if (this._c) this._c.abort();
            this._c = new AbortController();
            try {
                const r = await fetch(
                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(q)}.json`
                  + `?access_token=${apiKey}&country=CO&language=es&types=address,place&limit=5`,
                    { signal: this._c.signal }
                );
                const d = await r.json();
                this.sugs   = (d.features || []).map(f => ({
                    id:    f.id,
                    text:  f.text || f.place_name.split(',')[0],
                    place: f.place_name,
                    lat:   f.center[1],
                    lng:   f.center[0],
                }));
                this.abierto = this.sugs.length > 0;
                this.activo  = -1;
            } catch(e) { if (e.name !== 'AbortError') console.warn(e); }
        },

        elegir(s) {
            this.query   = s.place;
            this.lat     = s.lat.toFixed(7);
            this.lng     = s.lng.toFixed(7);
            this.abierto = false;
            this.sugs    = [];

            // Actualizar el input visible
            const inp = this.$el.querySelector('input');
            if (inp) {
                inp.value = s.place;
                inp.dispatchEvent(new Event('input',  { bubbles: true }));
                inp.dispatchEvent(new Event('change', { bubbles: true }));
            }

            // Actualizar lat/lng via Livewire
            this._updateField(latPath, this.lat);
            this._updateField(lngPath, this.lng);
        },

        _updateField(path, val) {
            const name = path.split('.').pop();
            const sel  = `[wire\\:model="${path}"], [wire\\:model\\.live="${path}"], [name="${name}"]`;
            const el   = document.querySelector(sel);
            if (el) {
                el.value = val;
                el.dispatchEvent(new Event('input',  { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },

        onPick(d) {
            if (d.statePath === statePath) this.elegir(d);
        },

        cerrar()    { this.abierto = false; },
        abrirSiHay() { if (this.sugs.length) this.abierto = true; },
        mover(d)    { this.activo = Math.max(-1, Math.min(this.sugs.length-1, this.activo+d)); },
        seleccionarActivo() { if (this.activo >= 0) this.elegir(this.sugs[this.activo]); },
    };
};
</script>
@endonce
