<x-filament-panels::page>

<div class="space-y-6">

    {{-- Filtros globales --}}
    <div class="fi-section rounded-xl p-5">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Filtros del reporte</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mes</label>
                <select wire:model.live="mes"
                    class="w-full rounded-lg border border-slate-200 text-sm px-3 py-2 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @foreach($this->getMeses() as $num => $nombre)
                        <option value="{{ $num }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Año</label>
                <select wire:model.live="anio"
                    class="w-full rounded-lg border border-slate-200 text-sm px-3 py-2 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @foreach($this->getAnios() as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Propietario (para liquidaciones)</label>
                <select wire:model.live="propietario_id"
                    class="w-full rounded-lg border border-slate-200 text-sm px-3 py-2 bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">— Todos los propietarios —</option>
                    @foreach($this->getPropietarios() as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Grid de reportes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">

        {{-- 1. Cartera General --}}
        <div class="fi-section rounded-xl p-5 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#0E01A3,#2563EB);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Cartera General</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Todas las facturas pendientes con saldo, estado y mora acumulada.</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="{{ $this->urlReporte('cartera', 'excel') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#217346;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    Excel
                </a>
            </div>
        </div>

        {{-- 2. Recaudo del Mes --}}
        <div class="fi-section rounded-xl p-5 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#059669,#10B981);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Recaudo del Mes</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Facturado vs recaudado con efectividad de cobro por período.</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="{{ $this->urlReporte('recaudo', 'excel') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#217346;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    Excel
                </a>
                <a href="{{ $this->urlReporte('recaudo', 'pdf') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#E11D48;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    PDF
                </a>
            </div>
        </div>

        {{-- 3. Mora Detallada --}}
        <div class="fi-section rounded-xl p-5 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#DC2626,#EF4444);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Mora Detallada</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Facturas en mora ordenadas por días, con valor mora y total a cobrar.</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="{{ $this->urlReporte('mora', 'excel') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#217346;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    Excel
                </a>
            </div>
        </div>

        {{-- 4. Estado del Portafolio --}}
        <div class="fi-section rounded-xl p-5 flex flex-col gap-3">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#7C3AED,#A855F7);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Estado del Portafolio</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Todos los inmuebles: ocupación, arrendatario, canon y vigencia.</p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="{{ $this->urlReporte('portafolio', 'excel') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#217346;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    Excel
                </a>
                <a href="{{ $this->urlReporte('portafolio', 'pdf') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#E11D48;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    PDF
                </a>
            </div>
        </div>

        {{-- 5. Liquidaciones por Propietario --}}
        <div class="fi-section rounded-xl p-5 flex flex-col gap-3 md:col-span-2 xl:col-span-2">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:linear-gradient(135deg,#D97706,#F59E0B);">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm">Liquidaciones por Propietario</h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Giros del período: canon, comisión, IVA, retefuente y total a girar.
                        Filtra por propietario o descarga todos.
                    </p>
                </div>
            </div>
            <div class="flex gap-2 mt-auto">
                <a href="{{ $this->urlReporte('liquidaciones', 'excel') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#217346;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    Excel
                </a>
                <a href="{{ $this->urlReporte('liquidaciones', 'pdf') }}" target="_blank"
                   class="flex-1 flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white"
                   style="background:#E11D48;">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                    PDF
                </a>
            </div>
        </div>

    </div>

    {{-- Nota informativa --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-xs text-blue-700">
            Los reportes <strong>Cartera General</strong> y <strong>Mora Detallada</strong> no dependen del período seleccionado — muestran el estado actual de todas las facturas sin importar el mes.
            Los demás reportes filtran por el mes/año seleccionado arriba.
        </p>
    </div>

</div>

</x-filament-panels::page>
