<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Propietario — {{ $company?->nombre_comercial ?? $company?->razon_social ?? 'Inmobiliaria' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; color: #111827; }
        a { color: inherit; text-decoration: none; }
    </style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────────────── --}}
<div style="background: {{ $company?->color_primario ?? '#E11D48' }}; padding: 0;">
    <div style="max-width: 900px; margin: 0 auto; padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            @if($company?->logo_path)
                <img src="{{ asset('storage/' . $company->logo_path) }}" alt="Logo" style="height: 44px; border-radius: 6px; background: #fff; padding: 4px;">
            @endif
            <div>
                <p style="font-size: 18px; font-weight: 700; color: #fff;">{{ $company?->nombre_comercial ?? $company?->razon_social }}</p>
                <p style="font-size: 12px; color: rgba(255,255,255,.75);">Portal del Propietario</p>
            </div>
        </div>
        <div style="text-align: right;">
            <p style="font-size: 14px; font-weight: 600; color: #fff;">{{ $propietario->nombre_completo }}</p>
            <p style="font-size: 11px; color: rgba(255,255,255,.75);">{{ $propietario->tipo_documento }} {{ $propietario->numero_documento }}</p>
        </div>
    </div>
</div>

<div style="max-width: 900px; margin: 0 auto; padding: 24px;">

    {{-- ── Tarjetas resumen ──────────────────────────────────────── --}}
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 28px;">

        <div style="background: #fff; border-radius: 12px; padding: 20px; border-left: 4px solid #6366f1;">
            <p style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Inmuebles</p>
            <p style="font-size: 28px; font-weight: 700; color: #6366f1;">{{ $propiedades->count() }}</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 4px;">en administración</p>
        </div>

        <div style="background: #fff; border-radius: 12px; padding: 20px; border-left: 4px solid #22c55e;">
            <p style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Total girado</p>
            <p style="font-size: 22px; font-weight: 700; color: #22c55e;">${{ number_format($totalGirado, 0, ',', '.') }}</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 4px;">COP · últimas liquidaciones</p>
        </div>

        <div style="background: #fff; border-radius: 12px; padding: 20px; border-left: 4px solid #f59e0b;">
            <p style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px;">Pendiente de pago</p>
            <p style="font-size: 22px; font-weight: 700; color: #f59e0b;">${{ number_format($pendientePago, 0, ',', '.') }}</p>
            <p style="font-size: 12px; color: #9ca3af; margin-top: 4px;">COP · por girar</p>
        </div>

    </div>

    {{-- ── Inmuebles ─────────────────────────────────────────────── --}}
    <div style="background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px;height:18px;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
            Mis inmuebles
        </h2>

        @forelse($propiedades as $propiedad)
        @php
            $contratoActivo = $propiedad->rentalContracts->first();
            $adminActiva    = $propiedad->administrationContracts->first();
            $estadoColor    = match($propiedad->estado) {
                'arrendado'   => '#22c55e',
                'disponible'  => '#3b82f6',
                'en_proceso'  => '#f59e0b',
                default       => '#9ca3af',
            };
        @endphp
        <div style="border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px; margin-bottom: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <span style="font-size: 13px; font-weight: 700; color: #111827;">{{ $propiedad->codigo }}</span>
                        <span style="font-size: 11px; padding: 2px 10px; border-radius: 999px; font-weight: 600; background: {{ $estadoColor }}20; color: {{ $estadoColor }};">
                            {{ ucfirst($propiedad->estado) }}
                        </span>
                    </div>
                    <p style="font-size: 13px; color: #6b7280;">{{ $propiedad->direccion }}</p>
                    @if($propiedad->municipio)
                        <p style="font-size: 12px; color: #9ca3af;">{{ $propiedad->municipio->nombre ?? '' }}</p>
                    @endif
                </div>
                <div style="text-align: right;">
                    @if($contratoActivo)
                        <p style="font-size: 12px; color: #9ca3af;">Canon mensual</p>
                        <p style="font-size: 18px; font-weight: 700; color: #22c55e;">${{ number_format($contratoActivo->canon_mensual, 0, ',', '.') }}</p>
                    @elseif($propiedad->canon_arriendo)
                        <p style="font-size: 12px; color: #9ca3af;">Canon referencia</p>
                        <p style="font-size: 18px; font-weight: 700; color: #6b7280;">${{ number_format($propiedad->canon_arriendo, 0, ',', '.') }}</p>
                    @endif
                </div>
            </div>

            @if($contratoActivo)
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f3f4f6; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                <div>
                    <p style="font-size: 11px; color: #9ca3af;">Arrendatario</p>
                    <p style="font-size: 13px; font-weight: 600; color: #374151;">{{ $contratoActivo->arrendatario?->nombre_completo ?? '—' }}</p>
                </div>
                <div>
                    <p style="font-size: 11px; color: #9ca3af;">Contrato</p>
                    <p style="font-size: 13px; font-weight: 600; color: #374151;">{{ $contratoActivo->numero_contrato }}</p>
                </div>
                <div>
                    <p style="font-size: 11px; color: #9ca3af;">Vence</p>
                    <p style="font-size: 13px; font-weight: 600; color: #374151;">{{ $contratoActivo->fecha_fin?->format('d/m/Y') ?? '—' }}</p>
                </div>
            </div>
            @endif
        </div>
        @empty
        <p style="text-align: center; color: #9ca3af; padding: 32px 0;">No hay inmuebles registrados</p>
        @endforelse
    </div>

    {{-- ── Liquidaciones ─────────────────────────────────────────── --}}
    <div style="background: #fff; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
        <h2 style="font-size: 16px; font-weight: 700; color: #374151; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
            <svg style="width:18px;height:18px;color:#22c55e;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75"/>
            </svg>
            Liquidaciones
        </h2>

        @if($liquidaciones->isEmpty())
            <p style="text-align: center; color: #9ca3af; padding: 32px 0;">Sin liquidaciones registradas</p>
        @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 2px solid #f3f4f6;">
                        <th style="text-align: left; padding: 8px 10px; color: #6b7280; font-weight: 600;">N°</th>
                        <th style="text-align: left; padding: 8px 10px; color: #6b7280; font-weight: 600;">Inmueble</th>
                        <th style="text-align: left; padding: 8px 10px; color: #6b7280; font-weight: 600;">Período</th>
                        <th style="text-align: right; padding: 8px 10px; color: #6b7280; font-weight: 600;">Canon</th>
                        <th style="text-align: right; padding: 8px 10px; color: #6b7280; font-weight: 600;">Comisión</th>
                        <th style="text-align: right; padding: 8px 10px; color: #6b7280; font-weight: 600;">Giro neto</th>
                        <th style="text-align: center; padding: 8px 10px; color: #6b7280; font-weight: 600;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liquidaciones as $liq)
                    @php
                        $liqColor = match($liq->estado) {
                            'girada'    => '#22c55e',
                            'aprobada'  => '#3b82f6',
                            'pendiente' => '#f59e0b',
                            'anulada'   => '#ef4444',
                            default     => '#9ca3af',
                        };
                        $liqLabel = match($liq->estado) {
                            'girada'    => 'Girada',
                            'aprobada'  => 'Aprobada',
                            'pendiente' => 'Pendiente',
                            'anulada'   => 'Anulada',
                            default     => ucfirst($liq->estado),
                        };
                    @endphp
                    <tr style="border-bottom: 1px solid #f9fafb;">
                        <td style="padding: 10px; font-weight: 600; color: #374151;">{{ $liq->numero }}</td>
                        <td style="padding: 10px; color: #6b7280; font-size: 12px;">{{ $liq->property?->codigo ?? '—' }}</td>
                        <td style="padding: 10px; color: #374151;">{{ $liq->periodo_inicio?->format('M Y') ?? '—' }}</td>
                        <td style="padding: 10px; text-align: right; color: #374151;">${{ number_format($liq->canon_cobrado ?? 0, 0, ',', '.') }}</td>
                        <td style="padding: 10px; text-align: right; color: #ef4444;">-${{ number_format($liq->comision_valor ?? 0, 0, ',', '.') }}</td>
                        <td style="padding: 10px; text-align: right; font-weight: 700; color: #22c55e;">${{ number_format($liq->total_giro ?? 0, 0, ',', '.') }}</td>
                        <td style="padding: 10px; text-align: center;">
                            <span style="font-size: 11px; padding: 3px 10px; border-radius: 999px; font-weight: 600; background: {{ $liqColor }}20; color: {{ $liqColor }};">
                                {{ $liqLabel }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Contacto ───────────────────────────────────────────────── --}}
    @if($company?->telefono || $company?->celular || $company?->email)
    <div style="background: #fff; border-radius: 12px; padding: 20px; text-align: center;">
        <p style="font-size: 13px; color: #6b7280; margin-bottom: 10px;">¿Preguntas? Contáctenos</p>
        <div style="display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
            @if($company->celular)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $company->celular) }}"
               style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #22c55e;">
                <svg style="width:16px;height:16px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                {{ $company->celular }}
            </a>
            @endif
            @if($company->email)
            <a href="mailto:{{ $company->email }}"
               style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #6366f1;">
                <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                </svg>
                {{ $company->email }}
            </a>
            @endif
        </div>
    </div>
    @endif

    <p style="text-align: center; font-size: 11px; color: #d1d5db; margin-top: 24px; padding-bottom: 24px;">
        Portal seguro · Este enlace es personal e intransferible
    </p>

</div>
</body>
</html>
