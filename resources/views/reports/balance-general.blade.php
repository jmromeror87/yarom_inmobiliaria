@extends('reports.layout')

@section('content')
@php
$cuadra = $data['ecuacion_cuadra'] ?? false;
function tablaBalance(array $cuentas, string $totalLabel, float $totalValor, string $colorTotal = '#1E3A8A'): string {
    $html = '<table><thead><tr><th style="width:11%">Código</th><th style="width:57%">Cuenta</th><th class="right" style="width:32%">Saldo</th></tr></thead><tbody>';
    foreach ($cuentas as $r) {
        $html .= '<tr><td style="font-family:monospace;font-size:9px;color:#64748B">' . $r['codigo'] . '</td><td>' . $r['nombre'] . '</td><td class="right money" style="font-weight:600">' . number_format($r['saldo'], 0, ',', '.') . '</td></tr>';
    }
    if (empty($cuentas)) {
        $html .= '<tr><td colspan="3" style="text-align:center;color:#94A3B8;padding:10px">Sin movimiento</td></tr>';
    }
    $html .= '</tbody><tfoot><tr class="tr-total"><td colspan="2" style="text-align:right;font-size:10px">' . $totalLabel . '</td><td class="right money" style="color:' . $colorTotal . '">' . '$' . number_format($totalValor, 0, ',', '.') . '</td></tr></tfoot></table>';
    return $html;
}
@endphp

<div style="display:flex;gap:16px;margin-top:0">
    {{-- COLUMNA IZQUIERDA: ACTIVOS --}}
    <div style="flex:1">
        {{-- Activos corrientes --}}
        <div class="section-title section-title-blue">💵 &nbsp; ACTIVOS CORRIENTES</div>
        {!! tablaBalance($data['activos_corrientes'] ?? [], 'SUBTOTAL ACTIVOS CORRIENTES', $data['total_activos_corrientes'] ?? 0, '#0284C7') !!}

        {{-- Activos no corrientes --}}
        <div class="section-title section-title-blue" style="margin-top:10px">🏢 &nbsp; ACTIVOS NO CORRIENTES</div>
        @php $totalNoCte = ($data['total_activos'] ?? 0) - ($data['total_activos_corrientes'] ?? 0); @endphp
        {!! tablaBalance($data['activos_no_corrientes'] ?? [], 'SUBTOTAL ACTIVOS NO CORRIENTES', $totalNoCte, '#1D4ED8') !!}

        {{-- Total activos --}}
        <table style="margin-top:4px">
            <tr style="background:#0F172A;height:32px">
                <td style="color:#fff;font-weight:900;font-size:12px;padding:0 10px;text-align:right" colspan="2">TOTAL ACTIVOS</td>
                <td class="right money" style="color:#60A5FA;font-size:14px;font-weight:900;padding:0 10px">${{ number_format($data['total_activos'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    {{-- COLUMNA DERECHA: PASIVOS + PATRIMONIO --}}
    <div style="flex:1">
        {{-- Pasivos corrientes --}}
        <div class="section-title section-title-red">📋 &nbsp; PASIVOS CORRIENTES</div>
        {!! tablaBalance($data['pasivos_corrientes'] ?? [], 'SUBTOTAL PASIVOS CORRIENTES', $data['total_pasivos_corrientes'] ?? 0, '#DC2626') !!}

        {{-- Pasivos largo plazo --}}
        @if(count($data['pasivos_largo_plazo'] ?? []) > 0)
        <div class="section-title section-title-red" style="margin-top:10px">📌 &nbsp; PASIVOS LARGO PLAZO</div>
        @php $totalLP = ($data['total_pasivos'] ?? 0) - ($data['total_pasivos_corrientes'] ?? 0); @endphp
        {!! tablaBalance($data['pasivos_largo_plazo'] ?? [], 'SUBTOTAL LARGO PLAZO', $totalLP, '#991B1B') !!}
        @endif

        {{-- Total pasivos --}}
        <table style="margin-top:4px">
            <tr style="background:#7F1D1D;height:28px">
                <td style="color:#fff;font-weight:900;font-size:11px;padding:0 10px;text-align:right" colspan="2">TOTAL PASIVOS</td>
                <td class="right money" style="color:#FCA5A5;font-size:12px;font-weight:900;padding:0 10px">${{ number_format($data['total_pasivos'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        {{-- PATRIMONIO --}}
        <div class="section-title" style="background:#14532D;margin-top:10px">💎 &nbsp; PATRIMONIO</div>
        {!! tablaBalance($data['patrimonio'] ?? [], 'TOTAL PATRIMONIO', $data['total_patrimonio'] ?? 0, '#16A34A') !!}

        {{-- Total pasivos + patrimonio --}}
        @php $totalPP = ($data['total_pasivos'] ?? 0) + ($data['total_patrimonio'] ?? 0); @endphp
        <table style="margin-top:4px">
            <tr style="background:#0F172A;height:32px">
                <td style="color:#fff;font-weight:900;font-size:12px;padding:0 10px;text-align:right" colspan="2">TOTAL PASIVOS + PATRIMONIO</td>
                <td class="right money" style="color:#60A5FA;font-size:14px;font-weight:900;padding:0 10px">${{ number_format($totalPP, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
</div>

{{-- Ecuación contable --}}
<div style="margin-top:14px;padding:12px 16px;background:{{ $cuadra ? '#DCFCE7' : '#FEE2E2' }};border-radius:8px;border-left:4px solid {{ $cuadra ? '#16A34A' : '#DC2626' }};display:flex;justify-content:space-between;align-items:center">
    <div style="font-weight:900;font-size:12px;color:{{ $cuadra ? '#14532D' : '#7F1D1D' }}">
        {{ $cuadra ? '✅' : '❌' }} &nbsp;
        ECUACIÓN CONTABLE: Activos = Pasivos + Patrimonio &nbsp;
        @if(!$cuadra) (Diferencia: ${{ number_format($data['diferencia'] ?? 0, 0, ',', '.') }}) @endif
    </div>
    <div style="font-size:11px;color:{{ $cuadra ? '#16A34A' : '#DC2626' }};font-weight:700">
        {{ $cuadra ? 'CUADRA ✓' : 'REVISAR' }}
    </div>
</div>

<div class="nota" style="margin-top:12px">
    ⚠️ Balance generado automáticamente desde asientos contabilizados. Los saldos acumulados se calculan desde el inicio del sistema. Verifique con el contador antes de presentar a terceros.
</div>
@endsection
