<div
    x-data="mapboxDireccion({
        apiKey:    '{{ $apiKey }}',
        statePath: '{{ $statePath }}',
        latPath:   '{{ $latPath }}',
        lngPath:   '{{ $lngPath }}',
        initVal:   '{{ $query }}',
    })"
    style="position:relative; width:100%;"
>
    {{-- Input visible --}}
    <div style="position:relative;">
        <input
            x-model="query"
            x-on:input.debounce.350ms="buscar"
            x-on:keydown.escape="cerrar"
            x-on:keydown.arrow-down.prevent="mover(1)"
            x-on:keydown.arrow-up.prevent="mover(-1)"
            x-on:keydown.enter.prevent="seleccionarActivo"
            x-on:focus="abrirSiHayResultados"
            x-on:blur.debounce.200ms="cerrar"
            type="text"
            placeholder="Escribe la dirección del inmueble..."
            autocomplete="off"
            style="width:100%; padding:10px 38px 10px 14px; border:1px solid #d1d5db;
                   border-radius:8px; font-size:14px; color:#111827; background:#fff;
                   outline:none; box-sizing:border-box; transition:border-color .15s;"
            x-bind:style="abierto ? 'border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.15); border-bottom-left-radius:0; border-bottom-right-radius:0;' : ''"
        >

        {{-- Spinner --}}
        <div x-show="cargando" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);">
            <svg style="width:18px;height:18px;animation:mbspin 1s linear infinite;color:#6366f1;"
                 fill="none" viewBox="0 0 24 24">
                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"/>
            </svg>
        </div>
        {{-- Pin icon --}}
        <div x-show="!cargando" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;">
            <svg style="width:18px;height:18px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
            </svg>
        </div>
    </div>

    {{-- Dropdown sugerencias --}}
    <div
        x-show="abierto && resultados.length > 0"
        style="position:absolute;z-index:9999;left:0;right:0;top:100%;background:#fff;
               border:1px solid #6366f1;border-top:none;border-bottom-left-radius:8px;
               border-bottom-right-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);
               max-height:280px;overflow-y:auto;"
    >
        <template x-for="(r, i) in resultados" :key="r.id">
            <div
                x-on:mousedown.prevent="seleccionar(r)"
                x-bind:style="i === activo ? 'background:#eef2ff;' : 'background:#fff;'"
                style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f3f4f6;
                       display:flex;align-items:flex-start;gap:10px;"
                x-on:mouseenter="activo = i"
            >
                <svg style="width:15px;height:15px;color:#6366f1;flex-shrink:0;margin-top:2px;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                </svg>
                <div>
                    <p style="font-size:13px;font-weight:600;color:#111827;margin:0;" x-text="r.nombre"></p>
                    <p style="font-size:11px;color:#9ca3af;margin:2px 0 0;" x-text="r.contexto"></p>
                </div>
            </div>
        </template>

        {{-- Escribir manual --}}
        <div
            x-on:mousedown.prevent="cerrar"
            style="padding:10px 14px;cursor:pointer;display:flex;align-items:center;
                   gap:8px;font-size:12px;color:#6b7280;border-top:1px solid #f3f4f6;"
        >
            <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z"/>
            </svg>
            Escribir dirección manualmente
        </div>
    </div>

    {{-- Coordenadas confirmadas --}}
    <div x-show="latConfirmada" style="margin-top:5px;display:flex;gap:6px;align-items:center;font-size:11px;color:#6b7280;">
        <svg style="width:12px;height:12px;color:#22c55e;" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
        <span>Ubicación confirmada ·</span>
        <span x-text="latConfirmada + ', ' + lngConfirmada" style="font-family:monospace;color:#374151;"></span>
    </div>

    {{-- Inputs ocultos que Filament lee --}}
    <input type="hidden" x-bind:value="query"
           x-on:mapbox-address-selected.window="query = $event.detail.direccion"
           wire:model="{{ $statePath }}" style="display:none;">

    <style>@keyframes mbspin { to { transform:rotate(360deg); } }</style>
    <script>
if (!window._mapboxDireccionDefined) {
    window._mapboxDireccionDefined = true;
    window.mapboxDireccion = function({ apiKey, statePath, latPath, lngPath, initVal }) {
        return {
            query:         initVal || '',
            resultados:    [],
            abierto:       false,
            cargando:      false,
            activo:        -1,
            latConfirmada: '',
            lngConfirmada: '',
            _ctrl:         null,

            async buscar() {
                const q = this.query.trim();
                if (q.length < 3) { this.resultados = []; this.abierto = false; return; }

                if (this._ctrl) this._ctrl.abort();
                this._ctrl = new AbortController();
                this.cargando = true;

                try {
                    const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(q)}.json`
                              + `?access_token=${apiKey}&country=CO&language=es&types=address,place&limit=5`;
                    const res  = await fetch(url, { signal: this._ctrl.signal });
                    const data = await res.json();

                    this.resultados = (data.features || []).map(f => ({
                        id:      f.id,
                        nombre:  f.text || f.place_name.split(',')[0],
                        contexto: f.place_name,
                        lat:     f.center[1],
                        lng:     f.center[0],
                        full:    f.place_name,
                    }));
                    this.abierto = this.resultados.length > 0;
                    this.activo  = -1;
                } catch(e) {
                    if (e.name !== 'AbortError') console.warn('Mapbox:', e);
                } finally {
                    this.cargando = false;
                }
            },

            seleccionar(r) {
                this.query         = r.full;
                this.latConfirmada = r.lat.toFixed(7);
                this.lngConfirmada = r.lng.toFixed(7);
                this.abierto       = false;
                this.resultados    = [];

                // Actualizar campos Filament via Livewire
                this._setLivewire(statePath, r.full);
                if (latPath) this._setLivewire(latPath, this.latConfirmada);
                if (lngPath) this._setLivewire(lngPath, this.lngConfirmada);
            },

            _setLivewire(path, val) {
                // Busca el componente Livewire padre y actualiza el estado
                const el = document.querySelector(`[wire\\:model="${path}"]`)
                        || document.querySelector(`[wire\\:model\\.live="${path}"]`)
                        || document.querySelector(`[name="${path.split('.').pop()}"]`);
                if (el) {
                    el.value = val;
                    el.dispatchEvent(new Event('input',  { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
                // Filament v4 form state update
                window.Livewire?.dispatch('filament-forms::set-state', { statePath: path, state: val });
            },

            cerrar()               { this.abierto = false; },
            abrirSiHayResultados() { if (this.resultados.length > 0) this.abierto = true; },
            mover(d)               { this.activo = Math.max(-1, Math.min(this.resultados.length - 1, this.activo + d)); },
            seleccionarActivo()    { if (this.activo >= 0) this.seleccionar(this.resultados[this.activo]); },
        };
    };
}
    </script>
</div>
