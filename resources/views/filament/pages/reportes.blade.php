<x-filament-panels::page>
<div class="fi-page-content grid gap-y-8">

    {{-- Filtros --}}
    <div class="fi-section fi-section-content-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-section-content p-6">
            <p class="fi-section-header-heading text-base font-semibold text-gray-950 dark:text-white mb-4">
                Filtros del reporte
            </p>
            <div style="display:grid; grid-template-columns: repeat(4,1fr); gap:1rem;">
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:4px;">Mes</label>
                    <select wire:model.live="mes" style="width:100%;border-radius:8px;border:1px solid #d1d5db;padding:8px 12px;font-size:0.875rem;background:#fff;color:#111827;">
                        @foreach($this->getMeses() as $num => $nombre)
                            <option value="{{ $num }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:4px;">Año</label>
                    <select wire:model.live="anio" style="width:100%;border-radius:8px;border:1px solid #d1d5db;padding:8px 12px;font-size:0.875rem;background:#fff;color:#111827;">
                        @foreach($this->getAnios() as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column:span 2;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:4px;">Propietario (para liquidaciones)</label>
                    <select wire:model.live="propietario_id" style="width:100%;border-radius:8px;border:1px solid #d1d5db;padding:8px 12px;font-size:0.875rem;background:#fff;color:#111827;">
                        <option value="">— Todos los propietarios —</option>
                        @foreach($this->getPropietarios() as $id => $nombre)
                            <option value="{{ $id }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid de reportes --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">

        @php
        $reportes = [
            ['titulo'=>'Cartera General','desc'=>'Todas las facturas pendientes con saldo, estado y mora acumulada.','color'=>'#1D4ED8','botones'=>[['label'=>'Excel','r'=>'cartera','t'=>'excel','bg'=>'#217346']]],
            ['titulo'=>'Recaudo del Mes','desc'=>'Facturado vs recaudado con efectividad de cobro por período.','color'=>'#059669','botones'=>[['label'=>'Excel','r'=>'recaudo','t'=>'excel','bg'=>'#217346'],['label'=>'PDF','r'=>'recaudo','t'=>'pdf','bg'=>'#E11D48']]],
            ['titulo'=>'Mora Detallada','desc'=>'Facturas en mora ordenadas por días, con valor mora y total a cobrar.','color'=>'#DC2626','botones'=>[['label'=>'Excel','r'=>'mora','t'=>'excel','bg'=>'#217346']]],
            ['titulo'=>'Estado del Portafolio','desc'=>'Todos los inmuebles: ocupación, arrendatario, canon y vigencia.','color'=>'#7C3AED','botones'=>[['label'=>'Excel','r'=>'portafolio','t'=>'excel','bg'=>'#217346'],['label'=>'PDF','r'=>'portafolio','t'=>'pdf','bg'=>'#E11D48']]],
            ['titulo'=>'Liquidaciones por Propietario','desc'=>'Giros del período: canon, comisión, IVA, retefuente y total a girar.','color'=>'#D97706','span'=>2,'botones'=>[['label'=>'Excel','r'=>'liquidaciones','t'=>'excel','bg'=>'#217346'],['label'=>'PDF','r'=>'liquidaciones','t'=>'pdf','bg'=>'#E11D48']]],
        ];
        @endphp

        @foreach($reportes as $r)
        <div style="grid-column:span {{ $r['span'] ?? 1 }};background:#fff;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 1px 3px rgba(0,0,0,.07);padding:20px;display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <div style="width:4px;align-self:stretch;border-radius:4px;background:{{ $r['color'] }};flex-shrink:0;"></div>
                <div>
                    <p style="font-size:0.9rem;font-weight:700;color:#111827;margin:0;">{{ $r['titulo'] }}</p>
                    <p style="font-size:0.78rem;color:#6b7280;margin:4px 0 0;">{{ $r['desc'] }}</p>
                </div>
            </div>
            <div style="display:flex;gap:8px;padding-top:12px;border-top:1px solid #f3f4f6;margin-top:auto;">
                @foreach($r['botones'] as $btn)
                <a href="{{ $this->urlReporte($btn['r'], $btn['t']) }}" target="_blank"
                   style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:8px;padding:8px 12px;font-size:0.78rem;font-weight:700;color:#fff;text-decoration:none;background:{{ $btn['bg'] }};">
                    {{ $btn['label'] }}
                </a>
                @endforeach
            </div>
        </div>
        @endforeach

    </div>

    {{-- Nota --}}
    <div style="border-radius:10px;border:1px solid #bfdbfe;background:#eff6ff;padding:14px 16px;">
        <p style="font-size:0.78rem;color:#1d4ed8;margin:0;">
            <strong>Nota:</strong> Los reportes <strong>Cartera General</strong> y <strong>Mora Detallada</strong> muestran el estado actual sin importar el período seleccionado.
        </p>
    </div>

</div>
</x-filament-panels::page>
