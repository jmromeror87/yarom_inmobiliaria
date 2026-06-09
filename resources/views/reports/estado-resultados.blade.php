@extends('reports.layout')

@section('content')
@php
    $claseNombres = ['4130'=>'Comisiones de administración','4135'=>'Ingresos gestión inmobiliaria','4210'=>'Ingresos financieros'];
@endphp

{{-- INGRESOS OPERACIONALES --}}
<div class="section-title section-title-green">📈 &nbsp; INGRESOS OPERACIONALES</div>
<table>
    <thead>
        <tr>
            <th style="width:12%">Código</th>
            <th style="width:46%">Cuenta</th>
            <th class="right" style="width:14%">Débitos</th>
            <th class="right" style="width:14%">Créditos</th>
            <th class="right" style="width:14%">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data['ingresos'] ?? [] as $r)
        <tr>
            <td style="font-family:monospace;font-size:9px;color:#64748B">{{ $r['codigo'] }}</td>
            <td>{{ $r['nombre'] }}</td>
            <td class="right money">{{ number_format($r['debito'],0,',','.') }}</td>
            <td class="right money">{{ number_format($r['credito'],0,',','.') }}</td>
            <td class="right money" style="font-weight:700;color:#16A34A">{{ number_format($r['saldo'],0,',','.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="tr-total">
            <td colspan="4" style="text-align:right">TOTAL INGRESOS OPERACIONALES</td>
            <td class="right money" style="color:#14532D">${{ number_format($data['total_ingresos'],0,',','.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- COSTOS (si existen) --}}
@if(count($data['costos'] ?? []) > 0)
<div class="section-title" style="background:#78350F">📦 &nbsp; COSTOS</div>
<table>
    <thead><tr><th style="width:12%">Código</th><th style="width:46%">Cuenta</th><th class="right" style="width:14%">Débitos</th><th class="right" style="width:14%">Créditos</th><th class="right" style="width:14%">Saldo</th></tr></thead>
    <tbody>
        @foreach($data['costos'] as $r)
        <tr><td style="font-family:monospace;font-size:9px;color:#64748B">{{ $r['codigo'] }}</td><td>{{ $r['nombre'] }}</td><td class="right money">{{ number_format($r['debito'],0,',','.') }}</td><td class="right money">{{ number_format($r['credito'],0,',','.') }}</td><td class="right money" style="font-weight:700;color:#B45309">{{ number_format($r['saldo'],0,',','.') }}</td></tr>
        @endforeach
    </tbody>
    <tfoot><tr class="tr-total"><td colspan="4" class="right">TOTAL COSTOS</td><td class="right money">${{ number_format($data['total_costos'],0,',','.') }}</td></tr></tfoot>
</table>
@endif

{{-- UTILIDAD BRUTA --}}
@php $uBruta = $data['utilidad_bruta'] ?? 0; @endphp
<table style="margin-top:4px">
    <tr class="{{ $uBruta >= 0 ? 'tr-resultado-positive' : 'tr-resultado-negative' }}">
        <td colspan="4" style="text-align:right;font-size:11px">UTILIDAD BRUTA</td>
        <td class="right money" style="font-size:13px">${{ number_format($uBruta,0,',','.') }}</td>
    </tr>
</table>

{{-- GASTOS OPERACIONALES --}}
<div class="section-title section-title-red" style="margin-top:16px">📉 &nbsp; GASTOS OPERACIONALES</div>
<table>
    <thead><tr><th style="width:12%">Código</th><th style="width:46%">Cuenta</th><th class="right" style="width:14%">Débitos</th><th class="right" style="width:14%">Créditos</th><th class="right" style="width:14%">Saldo</th></tr></thead>
    <tbody>
        @forelse($data['gastos'] ?? [] as $r)
        <tr>
            <td style="font-family:monospace;font-size:9px;color:#64748B">{{ $r['codigo'] }}</td>
            <td>{{ $r['nombre'] }}</td>
            <td class="right money">{{ number_format($r['debito'],0,',','.') }}</td>
            <td class="right money">{{ number_format($r['credito'],0,',','.') }}</td>
            <td class="right money" style="font-weight:700;color:#DC2626">{{ number_format($r['saldo'],0,',','.') }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center" style="color:#94A3B8;padding:14px">Sin gastos registrados en el período</td></tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="tr-total">
            <td colspan="4" style="text-align:right">TOTAL GASTOS OPERACIONALES</td>
            <td class="right money" style="color:#7F1D1D">${{ number_format($data['total_gastos'],0,',','.') }}</td>
        </tr>
    </tfoot>
</table>

{{-- RESULTADO FINAL --}}
@php $uOp = $data['utilidad_operacional'] ?? 0; @endphp
<table style="margin-top:6px">
    <tr class="{{ $uOp >= 0 ? 'tr-resultado-positive' : 'tr-resultado-negative' }}" style="height:36px">
        <td style="font-size:13px;font-weight:900;padding-left:12px">
            {{ $uOp >= 0 ? '✅' : '❌' }} &nbsp; UTILIDAD OPERACIONAL DEL PERÍODO
        </td>
        <td class="right money" style="font-size:16px;font-weight:900;padding-right:12px">
            ${{ number_format($uOp,0,',','.') }}
        </td>
    </tr>
</table>

{{-- Márgenes --}}
<div style="display:flex;gap:12px;margin-top:14px">
    <div style="flex:1;background:#F0F9FF;border:1px solid #BAE6FD;border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:9px;color:#0C4A6E;font-weight:700;text-transform:uppercase">Margen Bruto</div>
        <div style="font-size:20px;font-weight:900;color:#0284C7">{{ $data['margen_bruto'] ?? 0 }}%</div>
    </div>
    <div style="flex:1;background:{{ ($data['margen_operacional'] ?? 0) >= 0 ? '#F0FDF4' : '#FFF1F2' }};border:1px solid {{ ($data['margen_operacional'] ?? 0) >= 0 ? '#BBF7D0' : '#FECDD3' }};border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:9px;color:{{ ($data['margen_operacional'] ?? 0) >= 0 ? '#14532D' : '#9F1239' }};font-weight:700;text-transform:uppercase">Margen Operacional</div>
        <div style="font-size:20px;font-weight:900;color:{{ ($data['margen_operacional'] ?? 0) >= 0 ? '#16A34A' : '#DC2626' }}">{{ $data['margen_operacional'] ?? 0 }}%</div>
    </div>
    <div style="flex:1;background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;padding:10px;text-align:center">
        <div style="font-size:9px;color:#4C1D95;font-weight:700;text-transform:uppercase">Total ingresos</div>
        <div style="font-size:14px;font-weight:900;color:#6D28D9">${{ number_format($data['total_ingresos'],0,',','.') }}</div>
    </div>
</div>

<div class="nota" style="margin-top:16px">
    ⚠️ Este informe es generado automáticamente desde los asientos contabilizados en el sistema YarOM ERP. Para efectos legales o tributarios, verifique con el contador de la empresa.
</div>
@endsection
