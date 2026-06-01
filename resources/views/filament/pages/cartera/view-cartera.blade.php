<x-filament-panels::page>
    @php
        $record   = $this->record;
        $abonos   = $record->abonos()->with('registradoPor')->orderByDesc('fecha_abono')->get();
        $pct      = $record->porcentajePagado();
        $colorBar = $pct >= 100 ? '#22c55e' : ($pct > 0 ? '#3b82f6' : '#ef4444');
        $estadoColor = match($record->estado) {
            'pagado'    => '#22c55e',
            'parcial'   => '#3b82f6',
            'castigada' => '#6b7280',
            default     => '#f59e0b',
        };
        $estadoLabel = match($record->estado) {
            'pagado'    => 'Pagado',
            'parcial'   => 'Pago parcial',
            'castigada' => 'Castigada',
            default     => 'Pendiente',
        };
    @endphp

    {{-- Encabezado con resumen --}}
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px;">
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; text-align:center;">
            <p style="font-size:11px; color:#9ca3af; margin:0 0 6px; text-transform:uppercase; letter-spacing:.05em;">Valor original</p>
            <p style="font-size:22px; font-weight:700; color:#111827; margin:0;">${{ number_format($record->valor_original, 0, ',', '.') }}</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; text-align:center;">
            <p style="font-size:11px; color:#9ca3af; margin:0 0 6px; text-transform:uppercase; letter-spacing:.05em;">Pagado</p>
            <p style="font-size:22px; font-weight:700; color:#22c55e; margin:0;">${{ number_format($record->valor_pagado, 0, ',', '.') }}</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; text-align:center;">
            <p style="font-size:11px; color:#9ca3af; margin:0 0 6px; text-transform:uppercase; letter-spacing:.05em;">Saldo pendiente</p>
            <p style="font-size:22px; font-weight:700; color:#ef4444; margin:0;">${{ number_format($record->saldo, 0, ',', '.') }}</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; text-align:center;">
            <p style="font-size:11px; color:#9ca3af; margin:0 0 6px; text-transform:uppercase; letter-spacing:.05em;">Estado</p>
            <span style="display:inline-block; padding:4px 14px; border-radius:999px; font-size:13px; font-weight:600; background:{{ $estadoColor }}20; color:{{ $estadoColor }};">
                {{ $estadoLabel }}
            </span>
        </div>
    </div>

    {{-- Barra de progreso --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px; margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
            <span style="font-size:13px; font-weight:600; color:#374151;">Progreso de pago</span>
            <span style="font-size:13px; font-weight:700; color:{{ $colorBar }};">{{ $pct }}%</span>
        </div>
        <div style="background:#f3f4f6; border-radius:999px; height:10px; overflow:hidden;">
            <div style="height:10px; border-radius:999px; background:{{ $colorBar }}; width:{{ $pct }}%; transition:width .4s ease;"></div>
        </div>
    </div>

    {{-- Datos del registro --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
            <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 14px;">Información general</h3>
            <table style="width:100%; font-size:13px; border-collapse:collapse;">
                <tr><td style="color:#9ca3af; padding:4px 0; width:140px;">N° cuenta:</td><td style="font-weight:600;">{{ $record->numero }}</td></tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Tipo:</td><td>{{ match($record->tipo){ 'deposito_arriendo'=>'Depósito arriendo','mora'=>'Mora','dano'=>'Daño inmueble',default=>ucfirst($record->tipo)} }}</td></tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Concepto:</td><td>{{ $record->concepto }}</td></tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Deudor:</td><td>{{ $record->third?->nombre_completo ?? '—' }}</td></tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Inmueble:</td><td>{{ $record->property?->direccion ?? '—' }}</td></tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Contrato:</td><td>{{ $record->rentalContract?->numero_contrato ?? '—' }}</td></tr>
            </table>
        </div>
        <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
            <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 14px;">Fechas</h3>
            <table style="width:100%; font-size:13px; border-collapse:collapse;">
                <tr><td style="color:#9ca3af; padding:4px 0; width:140px;">Origen:</td><td>{{ $record->fecha_origen?->format('d/m/Y') ?? '—' }}</td></tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Vencimiento:</td>
                    <td style="color:{{ $record->fecha_vencimiento?->isPast() && $record->estado !== 'pagado' ? '#ef4444' : '#111827' }}; font-weight:600;">
                        {{ $record->fecha_vencimiento?->format('d/m/Y') ?? '—' }}
                        @if($record->fecha_vencimiento?->isPast() && $record->estado !== 'pagado')
                            <span style="font-size:11px; color:#ef4444;"> · vencido</span>
                        @endif
                    </td>
                </tr>
                <tr><td style="color:#9ca3af; padding:4px 0;">Pago total:</td><td>{{ $record->fecha_pago_total?->format('d/m/Y') ?? '—' }}</td></tr>
            </table>
            @if($record->notas)
            <div style="margin-top:12px; padding:10px; background:#f9fafb; border-radius:6px; font-size:12px; color:#6b7280;">
                {{ $record->notas }}
            </div>
            @endif
        </div>
    </div>

    {{-- Historial de abonos --}}
    <div style="background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:20px;">
        <h3 style="font-size:14px; font-weight:700; color:#374151; margin:0 0 14px; display:flex; align-items:center; gap:8px;">
            <svg style="width:16px;height:16px;color:#6366f1;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
            </svg>
            Historial de abonos ({{ $abonos->count() }})
        </h3>

        @if($abonos->isEmpty())
            <p style="text-align:center; color:#9ca3af; padding:32px 0; font-size:13px;">Sin abonos registrados</p>
        @else
            <table style="width:100%; border-collapse:collapse; font-size:13px;">
                <thead>
                    <tr style="border-bottom:2px solid #f3f4f6;">
                        <th style="text-align:left; padding:8px 10px; color:#6b7280; font-weight:600;">Fecha</th>
                        <th style="text-align:right; padding:8px 10px; color:#6b7280; font-weight:600;">Valor</th>
                        <th style="text-align:left; padding:8px 10px; color:#6b7280; font-weight:600;">Forma de pago</th>
                        <th style="text-align:left; padding:8px 10px; color:#6b7280; font-weight:600;">Referencia</th>
                        <th style="text-align:left; padding:8px 10px; color:#6b7280; font-weight:600;">Registrado por</th>
                        <th style="text-align:left; padding:8px 10px; color:#6b7280; font-weight:600;">Notas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($abonos as $abono)
                    <tr style="border-bottom:1px solid #f9fafb;">
                        <td style="padding:10px; color:#374151;">{{ $abono->fecha_abono->format('d/m/Y') }}</td>
                        <td style="padding:10px; text-align:right; font-weight:700; color:#22c55e;">${{ number_format($abono->valor, 0, ',', '.') }}</td>
                        <td style="padding:10px; color:#374151;">{{ ucfirst($abono->forma_pago) }}</td>
                        <td style="padding:10px; color:#6b7280; font-family:monospace; font-size:12px;">{{ $abono->referencia ?: '—' }}</td>
                        <td style="padding:10px; color:#374151;">{{ $abono->registradoPor?->name ?? '—' }}</td>
                        <td style="padding:10px; color:#9ca3af; font-size:12px;">{{ $abono->notas ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f9fafb; border-top:2px solid #e5e7eb;">
                        <td style="padding:10px; font-weight:700; color:#374151;">Total abonado</td>
                        <td style="padding:10px; text-align:right; font-weight:700; color:#22c55e; font-size:15px;">${{ number_format($abonos->sum('valor'), 0, ',', '.') }}</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>
</x-filament-panels::page>
