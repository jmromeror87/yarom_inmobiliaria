{{-- Partial genérico para informes sin vista específica --}}
@php $fmt = fn($v) => '$'.number_format((float)$v,0,',','.'); @endphp

@foreach(['activos_corrientes','pasivos_corrientes','patrimonio','practicadas','a_favor','cuentas','registros','por_rango','por_cuenta','por_mes','ingresos','gastos'] as $key)
@if(!empty($data[$key]))
<div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#0F172A;margin-bottom:8px;padding-top:12px;border-top:1px solid #E2E8F0">{{ str_replace('_',' ',$key) }}</div>
<table style="width:100%;border-collapse:collapse;margin-bottom:12px">
    @php $primera = (array)($data[$key][0] ?? []); @endphp
    <thead><tr>@foreach(array_keys($primera) as $col)<th style="text-align:left;padding:6px 8px;font-size:9px;color:#64748B;font-weight:700;background:#F8FAFC">{{ strtoupper(str_replace('_',' ',$col)) }}</th>@endforeach</tr></thead>
    <tbody>
        @foreach($data[$key] as $row)
        <tr style="border-bottom:1px solid #F1F5F9">
            @foreach((array)$row as $val)
            <td style="padding:5px 8px;font-size:11px">
                @if(is_numeric($val) && $val > 100){{ $fmt($val) }}@else{{ is_array($val) ? count($val).' items' : $val }}@endif
            </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endforeach
