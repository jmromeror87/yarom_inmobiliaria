<x-filament-panels::page>
    @php
        $resumen   = $this->getResumen();
        $vencidas  = $this->getObligacionesVencidas();
        $proximas  = $this->getObligacionesProximas();
        $porTipo   = $this->getObligacionesAnio();

        $colorEstado = fn($estado) => match($estado) {
            'pagada'     => '#16a34a',
            'presentada' => '#2563eb',
            'en_proceso' => '#d97706',
            'pendiente'  => '#64748b',
            'no_aplica'  => '#94a3b8',
            default      => '#64748b',
        };
        $iconEstado = fn($estado) => match($estado) {
            'pagada'     => '✅',
            'presentada' => '📋',
            'en_proceso' => '🔄',
            'pendiente'  => '⏳',
            'no_aplica'  => '—',
            default      => '⏳',
        };
    @endphp

    {{-- ── KPIs ──────────────────────────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">

        {{-- Vencidas --}}
        <div style="background:#FFF1F2;border:1.5px solid #FECDD3;border-radius:16px;padding:20px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#9F1239;">Vencidas</span>
                <span style="background:#FEE2E2;color:#DC2626;border-radius:8px;padding:4px 10px;font-size:18px;">🚨</span>
            </div>
            <div style="font-size:36px;font-weight:900;color:#DC2626;line-height:1;">{{ $resumen['vencidas'] }}</div>
            <div style="font-size:12px;color:#9F1239;margin-top:6px;font-weight:600;">Requieren atención inmediata</div>
        </div>

        {{-- Urgentes --}}
        <div style="background:#FEFCE8;border:1.5px solid #FDE68A;border-radius:16px;padding:20px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#92400E;">Urgentes (≤5 días)</span>
                <span style="background:#FEF3C7;color:#D97706;border-radius:8px;padding:4px 10px;font-size:18px;">⚠️</span>
            </div>
            <div style="font-size:36px;font-weight:900;color:#D97706;line-height:1;">{{ $resumen['urgentes'] }}</div>
            <div style="font-size:12px;color:#92400E;margin-top:6px;font-weight:600;">Próximas a vencer</div>
        </div>

        {{-- Pendientes --}}
        <div style="background:#F0F9FF;border:1.5px solid #BAE6FD;border-radius:16px;padding:20px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#0C4A6E;">Pendientes</span>
                <span style="background:#E0F2FE;color:#0284C7;border-radius:8px;padding:4px 10px;font-size:18px;">📋</span>
            </div>
            <div style="font-size:36px;font-weight:900;color:#0284C7;line-height:1;">{{ $resumen['pendientes'] }}</div>
            <div style="font-size:12px;color:#0C4A6E;margin-top:6px;font-weight:600;">En el año {{ $this->anio }}</div>
        </div>

        {{-- Valor pendiente --}}
        <div style="background:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:16px;padding:20px 22px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#14532D;">Valor pendiente</span>
                <span style="background:#DCFCE7;color:#16A34A;border-radius:8px;padding:4px 10px;font-size:18px;">💰</span>
            </div>
            <div style="font-size:26px;font-weight:900;color:#16A34A;line-height:1;">${{ number_format($resumen['valor_pendiente'],0,',','.') }}</div>
            <div style="font-size:12px;color:#14532D;margin-top:6px;font-weight:600;">Por declarar/pagar</div>
        </div>

    </div>

    {{-- ── Alertas vencidas ─────────────────────────────────────────────── --}}
    @if($vencidas->isNotEmpty())
    <div style="background:#FFF1F2;border:1.5px solid #FECDD3;border-radius:14px;padding:20px;margin-bottom:20px;">
        <div style="font-size:13px;font-weight:900;color:#9F1239;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <span style="background:#FEE2E2;padding:4px 10px;border-radius:8px;">🚨</span>
            OBLIGACIONES VENCIDAS — {{ $vencidas->count() }} en total
        </div>
        @foreach($vencidas as $d)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 14px;background:#fff;border-radius:10px;margin-bottom:8px;border-left:4px solid #FDA4AF;box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <div>
                <div style="font-weight:700;font-size:13px;color:#0F172A;">{{ $d->obligationType?->nombre }}</div>
                <div style="font-size:12px;color:#94A3B8;margin-top:2px;">
                    {{ $d->periodo_label }} &nbsp;·&nbsp; Venció el {{ $d->fecha_vencimiento->format('d/m/Y') }}
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:900;color:#DC2626;font-size:13px;">{{ abs($d->dias_para_vencer) }} días</div>
                @if($d->valor_a_pagar > 0)
                <div style="font-size:12px;color:#16A34A;font-weight:700;">${{ number_format($d->valor_a_pagar, 0, ',', '.') }}</div>
                @else
                <div style="font-size:11px;color:#94A3B8;">Sin calcular</div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Próximas 60 días ─────────────────────────────────────────────── --}}
    @if($proximas->isNotEmpty())
    <div style="background:#FEFCE8;border:1.5px solid #FDE68A;border-radius:14px;padding:20px;margin-bottom:24px;">
        <div style="font-size:13px;font-weight:800;color:#78350F;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
            <span style="background:#FEF3C7;padding:4px 10px;border-radius:8px;">📅</span>
            PRÓXIMAS 60 DÍAS
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;">
            @foreach($proximas as $d)
            @php $urgente = $d->es_urgenta; @endphp
            <div style="background:#fff;border-radius:12px;padding:16px;border-top:3px solid {{ $urgente ? '#FBBF24' : '#7DD3FC' }};box-shadow:0 1px 6px rgba(0,0,0,.05);">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                    <span style="background:{{ $urgente ? '#FEF3C7' : '#E0F2FE' }};color:{{ $urgente ? '#D97706' : '#0284C7' }};font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;">
                        {{ $d->obligationType?->formulario ?? 'FORM.' }}
                    </span>
                    <span style="font-size:13px;font-weight:900;color:{{ $urgente ? '#D97706' : '#0284C7' }};">
                        {{ $d->dias_para_vencer }}d
                    </span>
                </div>
                <div style="font-weight:700;font-size:13px;color:#0F172A;margin-bottom:4px;">{{ $d->obligationType?->nombre }}</div>
                <div style="font-size:12px;color:#94A3B8;">{{ $d->periodo_label }}</div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:10px;border-top:1px solid #F1F5F9;">
                    <span style="font-size:12px;color:#475569;font-weight:600;">Vence {{ $d->fecha_vencimiento->format('d/m/Y') }}</span>
                    @if($d->valor_a_pagar > 0)
                    <span style="font-size:12px;color:#16A34A;font-weight:800;">${{ number_format($d->valor_a_pagar, 0, ',', '.') }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Calendario anual por tipo ─────────────────────────────────────── --}}
    <div style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden;">
        <div style="background:#0f172a;color:#fff;padding:16px 24px;font-size:14px;font-weight:800;letter-spacing:.04em;">
            📆 CALENDARIO TRIBUTARIO {{ $this->anio }}
        </div>

        @forelse($porTipo as $codigo => $declaraciones)
        @php $tipo = $declaraciones->first()->obligationType; @endphp
        <div style="border-bottom:1px solid #f1f5f9;padding:16px 24px;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                <div style="background:#1e3a8a;color:#fff;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:800;letter-spacing:.06em;">
                    {{ $tipo?->formulario ?? 'FORM.' }}
                </div>
                <div style="font-weight:800;font-size:14px;color:#0f172a;">{{ $tipo?->nombre }}</div>
                <div style="font-size:11px;color:#64748b;background:#f8fafc;padding:2px 10px;border-radius:10px;">
                    {{ $tipo?->periodicidad_label }}
                </div>
            </div>

            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                @foreach($declaraciones as $d)
                @php
                    $color = $colorEstado($d->estado);
                    $icon  = $iconEstado($d->estado);
                    $esVenc = $d->esta_vencida;
                @endphp
                <div style="border:2px solid {{ $esVenc ? '#dc2626' : $color }};border-radius:8px;padding:8px 14px;min-width:140px;background:{{ $esVenc ? '#fef2f2' : '#f8fafc' }};">
                    <div style="font-size:10px;color:#64748b;font-weight:600;">{{ $d->periodo_label }}</div>
                    <div style="font-size:11px;color:#0f172a;margin-top:2px;">
                        📅 {{ $d->fecha_vencimiento->format('d/m/Y') }}
                    </div>
                    <div style="margin-top:6px;display:flex;align-items:center;gap:4px;">
                        <span style="font-size:12px;">{{ $icon }}</span>
                        <span style="font-size:11px;font-weight:700;color:{{ $color }};">{{ $d->estado_label }}</span>
                    </div>
                    @if($d->valor_a_pagar > 0)
                    <div style="font-size:11px;color:#16a34a;font-weight:700;margin-top:2px;">
                        ${{ number_format($d->valor_a_pagar, 0, ',', '.') }}
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Descripción --}}
            @if($tipo?->descripcion)
            <div style="font-size:11px;color:#94a3b8;margin-top:10px;font-style:italic;">
                {{ $tipo->descripcion }}
            </div>
            @endif
        </div>
        @empty
        <div style="padding:48px;text-align:center;color:#94a3b8;">
            <div style="font-size:36px;margin-bottom:12px;">📭</div>
            <div style="font-size:14px;font-weight:700;">No hay períodos generados para {{ $this->anio }}</div>
            <div style="font-size:13px;margin-top:4px;">Use el botón "Generar períodos" arriba.</div>
        </div>
        @endforelse
    </div>
</x-filament-panels::page>
