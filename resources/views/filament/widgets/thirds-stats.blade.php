@php
    $cards = [
        [
            'label'  => 'Total Terceros',
            'sub'    => 'Registrados en el sistema',
            'value'  => $total,
            'color'  => '#2563EB',
            'bg'     => '#eff6ff',
            'action' => 'clear',
            'icon'   => '<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>',
        ],
        [
            'label'  => 'Propietarios',
            'sub'    => $proveedores . ' proveedores',
            'value'  => $propietarios,
            'color'  => '#16a34a',
            'bg'     => '#f0fdf4',
            'action' => 'filter:es_propietario:1',
            'icon'   => '<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>',
        ],
        [
            'label'  => 'Compradores',
            'sub'    => $fiadores . ' fiadores',
            'value'  => $compradores,
            'color'  => '#7c3aed',
            'bg'     => '#fdf4ff',
            'action' => 'filter:es_cliente_compra:1',
            'icon'   => '<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>',
        ],
        [
            'label'  => 'Arrendatarios',
            'sub'    => $aprobados . ' con crédito aprobado',
            'value'  => $arrendatarios,
            'color'  => '#E11D48',
            'bg'     => '#fef2f2',
            'action' => 'filter:es_arrendatario:1',
            'icon'   => '<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21M3 9l9-6 9 6v10.125c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 19.125V9z"/></svg>',
        ],
        [
            'label'  => 'Activos',
            'sub'    => $inactivos . ' inactivos',
            'value'  => $activos,
            'color'  => '#d97706',
            'bg'     => '#fffbeb',
            'action' => 'filter:is_active:1',
            'icon'   => '<svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>',
        ],
    ];
@endphp

<div>
    {{-- KIPs --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;width:100%;margin-bottom:10px;">
        @foreach($cards as $card)
            @php
                [$type, $filter, $value] = array_pad(explode(':', $card['action']), 3, null);
                $isFilter = $type === 'filter';
            @endphp
            <div
                wire:click="{{ $isFilter ? 'filterTable(\'' . $filter . '\', \'' . $value . '\')' : 'clearFilter()' }}"
                style="
                    background:#fff;
                    border-radius:1rem;
                    border-left:5px solid {{ $card['color'] }};
                    border-top:1px solid #e5e7eb;
                    border-right:1px solid #e5e7eb;
                    border-bottom:1px solid #e5e7eb;
                    padding:20px 20px 18px;
                    height:148px;
                    box-sizing:border-box;
                    display:flex;flex-direction:column;justify-content:space-between;
                    box-shadow:0 1px 4px rgba(0,0,0,.07);
                    cursor:pointer;
                    transition:transform .12s,box-shadow .12s;
                "
                onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,.12)'"
                onmouseout="this.style.transform='';this.style.boxShadow='0 1px 4px rgba(0,0,0,.07)'">

                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <div style="width:40px;height:40px;border-radius:10px;background:{{ $card['bg'] }};color:{{ $card['color'] }};display:flex;align-items:center;justify-content:center;">
                        {!! $card['icon'] !!}
                    </div>
                </div>
                <div>
                    <div style="font-size:46px;font-weight:900;color:#0f172a;line-height:1;letter-spacing:-.03em;">{{ $card['value'] }}</div>
                    <div style="font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;margin-top:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $card['label'] }}</div>
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $card['sub'] }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Habeas Data barra --}}
    <div style="width:100%;background:#eff6ff;border-radius:.875rem;border:1px solid #bfdbfe;border-left:4px solid #2563EB;padding:12px 20px;display:flex;align-items:center;gap:14px;box-shadow:0 1px 4px rgba(37,99,235,.08);margin-bottom:4px;">
        <div style="width:32px;height:32px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#2563EB" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span style="font-size:10.5px;font-weight:800;color:#1d4ed8;text-transform:uppercase;letter-spacing:.07em;white-space:nowrap;">Habeas Data —</span>
            <span style="font-size:11.5px;color:#3b5a9a;">Los datos personales son tratados bajo la <strong style="color:#1e40af;">Ley 1581 de 2012</strong> y el Decreto 1377 de 2013. Serviarrendar S.A.S. garantiza su protección y uso exclusivo para fines inmobiliarios.</span>
        </div>
    </div>
</div>
