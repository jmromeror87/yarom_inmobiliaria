<x-filament-panels::page>
<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap');
*{box-sizing:border-box;}
.fac{font-family:'Plus Jakarta Sans',sans-serif;max-width:860px;margin:0 auto;background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,0.12);}
.fac-head{background:linear-gradient(135deg,#0A192F,#112240);padding:36px 40px;display:flex;justify-content:space-between;align-items:flex-start;gap:24px;}
.fac-empresa-nombre{font-size:22px;font-weight:900;color:#fff;letter-spacing:-.02em;text-transform:uppercase;}
.fac-empresa-nombre span{color:#E11D48;}
.fac-empresa-sub{font-size:11px;color:rgba(255,255,255,0.45);font-weight:600;letter-spacing:.08em;text-transform:uppercase;margin-top:3px;}
.fac-empresa-datos{font-size:12px;color:rgba(255,255,255,0.55);margin-top:12px;line-height:1.9;}
.fac-empresa-datos strong{color:rgba(255,255,255,0.85);}
.fac-num-box{text-align:right;flex-shrink:0;}
.fac-tipo{font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#E11D48;margin-bottom:6px;}
.fac-numero{font-size:26px;font-weight:900;color:#fff;letter-spacing:-.02em;}
.fac-dian{font-size:10px;color:rgba(255,255,255,0.35);margin-top:4px;}
.badge{display:inline-block;margin-top:10px;padding:5px 14px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;}
.b-pendiente{background:rgba(251,191,36,0.15);color:#FCD34D;border:1px solid rgba(251,191,36,0.3);}
.b-pagada{background:rgba(34,197,94,0.15);color:#4ADE80;border:1px solid rgba(34,197,94,0.3);}
.b-en_mora{background:rgba(225,29,72,0.15);color:#F87171;border:1px solid rgba(225,29,72,0.3);}
.b-anulada{background:rgba(148,163,184,0.15);color:#94A3B8;border:1px solid rgba(148,163,184,0.3);}
.fac-stripe{height:4px;background:linear-gradient(90deg,#E11D48,#2563EB);}
.fac-grid{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid #f1f5f9;}
.fac-bloque{padding:28px 40px;border-right:1px solid #f1f5f9;}
.fac-bloque:last-child{border-right:none;}
.fac-titulo{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#E11D48;margin-bottom:14px;display:flex;align-items:center;gap:7px;}
.fac-titulo::before{content:'';width:3px;height:13px;background:#E11D48;border-radius:2px;display:inline-block;}
.fac-row{display:flex;justify-content:space-between;align-items:baseline;padding:6px 0;border-bottom:1px dashed #f1f5f9;font-size:13px;}
.fac-row:last-child{border-bottom:none;}
.fac-lbl{color:#94a3b8;font-weight:500;}
.fac-val{color:#0f172a;font-weight:700;text-align:right;max-width:58%;}
.fac-tabla-wrap{padding:0 40px 32px;}
.fac-table{width:100%;border-collapse:collapse;font-size:13px;}
.fac-table thead tr{background:#0A192F;}
.fac-table thead th{padding:12px 16px;text-align:left;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#fff;}
.fac-table thead th:last-child{text-align:right;}
.fac-table tbody tr{border-bottom:1px solid #f1f5f9;}
.fac-table tbody tr:hover{background:#fafafa;}
.fac-table tbody td{padding:14px 16px;color:#334155;font-weight:500;}
.fac-table tbody td:last-child{text-align:right;font-weight:700;color:#0f172a;}
.fac-table tbody tr.mora td{color:#E11D48;}
.fac-table tbody tr.desc td{color:#16a34a;}
.fac-totales{margin:0 40px 32px;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;}
.fac-tot-row{display:flex;justify-content:space-between;align-items:center;padding:13px 24px;font-size:13px;border-bottom:1px solid #f1f5f9;}
.fac-tot-row:last-child{border-bottom:none;}
.fac-tot-row.tot-final{background:linear-gradient(135deg,#0A192F,#112240);padding:20px 24px;}
.fac-tot-row.tot-pagado{background:#f0fdf4;}
.fac-tot-row.tot-saldo{background:#fff7ed;}
.tot-lbl{color:#64748b;font-weight:600;}
.tot-val{font-weight:800;color:#0f172a;}
.tot-final .tot-lbl{color:rgba(255,255,255,0.6);font-weight:700;font-size:14px;letter-spacing:.05em;text-transform:uppercase;}
.tot-final .tot-val{color:#fff;font-size:22px;}
.tot-pagado .tot-val{color:#16a34a;}
.tot-saldo .tot-val{color:#ea580c;}
.fac-footer{background:#f8fafc;border-top:1px solid #e2e8f0;padding:22px 40px;display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#94a3b8;}
.fac-footer strong{color:#475569;}
@media print{
  body *{visibility:hidden;}
  .fac,.fac *{visibility:visible;}
  .fac{position:absolute;left:0;top:0;width:100%;box-shadow:none;border-radius:0;}
  .fi-header,.fi-sidebar,.fi-topbar{display:none!important;}
}
</style>

@php
    $bill    = $this->record->load(['rentalContract','property','arrendatario','payments']);
    $company = \App\Models\Company::first();
    $bc      = match($bill->estado){ 'pagada'=>'b-pagada','en_mora'=>'b-en_mora','anulada'=>'b-anulada',default=>'b-pendiente' };
    $bl      = match($bill->estado){ 'pagada'=>'Pagada','en_mora'=>'En Mora','anulada'=>'Anulada',default=>'Pendiente' };
    $fmt     = fn($v)=>'$ '.number_format((float)$v,0,',','.');
    $words   = explode(' ', $company?->razon_social ?? 'YAROM INMOBILIARIA');
    $w1      = $words[0];
    $w2      = implode(' ', array_slice($words,1));
@endphp

<div class="fac">

  {{-- HEADER --}}
  <div class="fac-head">
    <div>
      <div class="fac-empresa-nombre">{{ strtoupper($w1) }} <span>{{ strtoupper($w2) }}</span></div>
      <div class="fac-empresa-sub">{{ $company?->nombre_comercial ?? 'Serviarrendar S.A.S' }}</div>
      <div class="fac-empresa-datos">
        <strong>NIT:</strong> {{ $company?->nit_completo ?? '---' }}<br>
        <strong>Dir:</strong> {{ $company?->direccion ?? '---' }}<br>
        <strong>Tel:</strong> {{ $company?->telefono ?? '---' }} &nbsp;·&nbsp; <strong>Email:</strong> {{ $company?->email ?? '---' }}
      </div>
    </div>
    <div class="fac-num-box">
      <div class="fac-tipo">{{ $bill->tipo_documento ?? 'Documento Equivalente' }}</div>
      <div class="fac-numero">{{ $bill->numero }}</div>
      @if($bill->numero_dian)<div class="fac-dian">DIAN: {{ $bill->numero_dian }}</div>@endif
      <div class="badge {{ $bc }}">{{ $bl }}</div>
    </div>
  </div>

  <div class="fac-stripe"></div>

  {{-- INFO --}}
  <div class="fac-grid">
    <div class="fac-bloque">
      <div class="fac-titulo">Arrendatario</div>
      <div class="fac-row"><span class="fac-lbl">Nombre</span><span class="fac-val">{{ $bill->arrendatario?->nombre_completo ?? $bill->arrendatario?->razon_social ?? '---' }}</span></div>
      <div class="fac-row"><span class="fac-lbl">Documento</span><span class="fac-val">{{ $bill->arrendatario?->numero_documento ?? '---' }}</span></div>
      <div class="fac-row"><span class="fac-lbl">Teléfono</span><span class="fac-val">{{ $bill->arrendatario?->celular ?? $bill->arrendatario?->telefono ?? '---' }}</span></div>
      <div class="fac-row"><span class="fac-lbl">Email</span><span class="fac-val">{{ $bill->arrendatario?->email ?? '---' }}</span></div>
    </div>
    <div class="fac-bloque">
      <div class="fac-titulo">Inmueble y Período</div>
      <div class="fac-row"><span class="fac-lbl">Inmueble</span><span class="fac-val">{{ $bill->property?->nombre ?? $bill->property?->direccion ?? '---' }}</span></div>
      <div class="fac-row"><span class="fac-lbl">Contrato</span><span class="fac-val">{{ $bill->rentalContract?->numero ?? '---' }}</span></div>
      <div class="fac-row"><span class="fac-lbl">Período</span><span class="fac-val">{{ $bill->periodo_inicio?->format('d/m/Y') }} — {{ $bill->periodo_fin?->format('d/m/Y') }}</span></div>
      <div class="fac-row"><span class="fac-lbl">Fecha límite</span><span class="fac-val">{{ $bill->fecha_limite_pago?->format('d/m/Y') ?? '---' }}</span></div>
      @if($bill->dias_mora > 0)
      <div class="fac-row"><span class="fac-lbl" style="color:#E11D48">Días mora</span><span class="fac-val" style="color:#E11D48">{{ $bill->dias_mora }} días</span></div>
      @endif
    </div>
  </div>

  {{-- CONCEPTOS --}}
  <div class="fac-tabla-wrap">
    <div class="fac-titulo" style="padding-top:28px;">Conceptos de Cobro</div>
    <table class="fac-table">
      <thead><tr>
        <th style="width:40%">Concepto</th>
        <th>Descripción</th>
        <th>Valor</th>
      </tr></thead>
      <tbody>
        <tr>
          <td><strong>Canon de Arrendamiento</strong></td>
          <td>{{ $bill->periodo_inicio?->format('d/m/Y') }} al {{ $bill->periodo_fin?->format('d/m/Y') }}</td>
          <td>{{ $fmt($bill->canon_base) }}</td>
        </tr>
        @if($bill->cuota_administracion > 0)
        <tr>
          <td><strong>Cuota Administración</strong></td>
          <td>Período corriente</td>
          <td>{{ $fmt($bill->cuota_administracion) }}</td>
        </tr>
        @endif
        @if($bill->otros_cobros > 0)
        <tr>
          <td><strong>Otros Cobros</strong></td>
          <td>{{ $bill->descripcion_otros_cobros ?? 'Cobros adicionales' }}</td>
          <td>{{ $fmt($bill->otros_cobros) }}</td>
        </tr>
        @endif
        @if($bill->mora_acumulada > 0)
        <tr class="mora">
          <td><strong>⚠ Mora Acumulada</strong></td>
          <td>{{ $bill->dias_mora }} días · {{ $bill->tasa_mora_diaria }}% diario</td>
          <td>{{ $fmt($bill->mora_acumulada) }}</td>
        </tr>
        @endif
        @if($bill->descuentos > 0)
        <tr class="desc">
          <td><strong>✓ Descuento</strong></td>
          <td>Descuento aplicado</td>
          <td>− {{ $fmt($bill->descuentos) }}</td>
        </tr>
        @endif
      </tbody>
    </table>
  </div>

  {{-- TOTALES --}}
  <div class="fac-totales">
    <div class="fac-tot-row tot-final">
      <span class="tot-lbl">Total Factura</span>
      <span class="tot-val">{{ $fmt($bill->total_factura) }}</span>
    </div>
    <div class="fac-tot-row tot-pagado">
      <span class="tot-lbl">Total Pagado</span>
      <span class="tot-val">{{ $fmt($bill->total_pagado) }}</span>
    </div>
    <div class="fac-tot-row tot-saldo">
      <span class="tot-lbl">Saldo Pendiente</span>
      <span class="tot-val">{{ $fmt($bill->saldo_pendiente) }}</span>
    </div>
  </div>

  {{-- PAGOS --}}
  @if($bill->payments && $bill->payments->count() > 0)
  <div class="fac-tabla-wrap">
    <div class="fac-titulo">Pagos Registrados</div>
    <table class="fac-table">
      <thead><tr><th>Fecha</th><th>Referencia</th><th>Método</th><th>Valor</th></tr></thead>
      <tbody>
        @foreach($bill->payments as $p)
        <tr>
          <td>{{ \Carbon\Carbon::parse($p->fecha_pago)->format('d/m/Y') }}</td>
          <td>{{ $p->referencia ?? '---' }}</td>
          <td>{{ $p->metodo_pago ?? '---' }}</td>
          <td>{{ $fmt($p->valor) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- NOTAS --}}
  @if($bill->notas)
  <div style="padding:0 40px 28px;">
    <div class="fac-titulo">Notas</div>
    <div style="background:#f8fafc;border-radius:12px;padding:16px 20px;font-size:13px;color:#475569;line-height:1.7;border-left:3px solid #E11D48;">{{ $bill->notas }}</div>
  </div>
  @endif

  {{-- FOOTER --}}
  <div class="fac-footer">
    <div><strong>{{ $company?->razon_social ?? 'YarOM Inmobiliaria' }}</strong><br>{{ $company?->direccion ?? '' }} · {{ $company?->email ?? '' }}</div>
    <div style="text-align:right;">Generado: <strong>{{ now()->format('d/m/Y H:i') }}</strong>@if($bill->cufe)<br><span style="font-size:9px;">CUFE: {{ substr($bill->cufe,0,40) }}...</span>@endif</div>
  </div>

</div>
</x-filament-panels::page>
