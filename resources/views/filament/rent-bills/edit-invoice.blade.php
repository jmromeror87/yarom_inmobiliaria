<x-filament-panels::page>
<style>
  .fac{background:var(--color-background-primary);border:0.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-lg);overflow:hidden;font-size:13px;max-width:860px;margin:0 auto;}
  .fac-head{background:#0A192F;padding:18px 24px;display:flex;justify-content:space-between;align-items:flex-start;}
  .fac-logo{font-size:16px;font-weight:500;color:#fff;}
  .fac-logo span{color:#E24B4A;}
  .fac-logo-sub{font-size:10px;color:#94a3b8;margin-top:2px;}
  .fac-num-label{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;text-align:right;}
  .fac-num-val{font-size:18px;font-weight:500;color:#fff;text-align:right;margin-top:2px;}
  .fac-num-tipo{font-size:10px;color:#94a3b8;text-align:right;margin-top:2px;}
  .fac-dian{background:#0d2340;padding:7px 24px;display:flex;justify-content:space-between;align-items:center;border-bottom:0.5px solid #1e3a5f;}
  .fac-dian-txt{font-size:10px;color:#64748b;}
  .fac-dian-res{font-size:10px;color:#378ADD;font-weight:500;}
  .fac-body{padding:18px 24px;}
  .fac-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;}
  .fac-block{border:0.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);padding:12px;}
  .fac-block-title{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:8px;padding-bottom:6px;border-bottom:0.5px solid var(--color-border-tertiary);}
  .fac-row{display:flex;justify-content:space-between;padding:3px 0;font-size:12px;}
  .fac-row label{color:var(--color-text-secondary);}
  .fac-row span{font-weight:500;color:var(--color-text-primary);text-align:right;max-width:60%;}
  .fac-table{width:100%;border-collapse:collapse;margin-bottom:16px;table-layout:fixed;}
  .fac-table th{background:var(--color-background-secondary);padding:8px 10px;text-align:left;font-size:11px;font-weight:500;color:var(--color-text-secondary);text-transform:uppercase;letter-spacing:0.06em;border-bottom:0.5px solid var(--color-border-tertiary);}
  .fac-table th:last-child,.fac-table td:last-child{text-align:right;}
  .fac-table td{padding:10px;font-size:12px;color:var(--color-text-primary);border-bottom:0.5px solid var(--color-border-tertiary);vertical-align:top;}
  .fac-table tr:last-child td{border-bottom:none;}
  .fac-totales{border:0.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);overflow:hidden;margin-bottom:16px;}
  .fac-tot-row{display:flex;justify-content:space-between;padding:8px 14px;font-size:12px;border-bottom:0.5px solid var(--color-border-tertiary);}
  .fac-tot-row:last-child{border-bottom:none;}
  .fac-tot-row label{color:var(--color-text-secondary);}
  .fac-tot-row span{font-weight:500;color:var(--color-text-primary);}
  .fac-tot-final{background:#0A192F;display:flex;justify-content:space-between;padding:13px 14px;}
  .fac-tot-final label{font-size:13px;font-weight:500;color:#94a3b8;}
  .fac-tot-final span{font-size:17px;font-weight:500;color:#fff;}
  .fac-mora{background:var(--color-background-danger);border:0.5px solid var(--color-border-danger);border-radius:var(--border-radius-md);padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;}
  .fac-cufe{background:var(--color-background-secondary);border-radius:var(--border-radius-md);padding:10px 14px;margin-bottom:16px;}
  .fac-cufe-label{font-size:10px;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:4px;}
  .fac-cufe-val{font-size:10px;color:var(--color-text-secondary);font-family:monospace;word-break:break-all;}
  .fac-pagos{border:0.5px solid var(--color-border-tertiary);border-radius:var(--border-radius-md);overflow:hidden;margin-bottom:16px;}
  .fac-pago-row{display:flex;justify-content:space-between;align-items:center;padding:12px 14px;border-bottom:0.5px solid var(--color-border-tertiary);font-size:12px;}
  .fac-pago-row:last-child{border-bottom:none;}
  .fac-pago-badge{display:inline-block;padding:2px 8px;border-radius:99px;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;background:var(--color-background-success);color:var(--color-text-success);margin-left:6px;}
  .fac-pago-links{display:flex;gap:10px;align-items:center;justify-content:flex-end;margin-top:4px;}
  .fac-pago-link{font-size:11px;color:var(--color-text-info);text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:3px;}
  .fac-pago-link:hover{text-decoration:underline;}
  .fac-pago-link-wap{color:#16a34a;background:none;border:none;cursor:pointer;padding:0;font:inherit;}
  .fac-pago-link-wap:hover{text-decoration:underline;}
  .fac-pago-link-wap:disabled{color:var(--color-text-tertiary);cursor:not-allowed;}
  .fac-acciones{display:flex;gap:8px;align-items:center;padding:14px 24px;border-top:0.5px solid var(--color-border-tertiary);background:var(--color-background-secondary);}
  .fac-footer{background:var(--color-background-secondary);padding:10px 24px;border-top:0.5px solid var(--color-border-tertiary);display:flex;justify-content:space-between;align-items:center;}
  .fac-footer-txt{font-size:10px;color:var(--color-text-tertiary);max-width:420px;line-height:1.5;}
  .fac-footer-plat{font-size:10px;color:var(--color-text-tertiary);text-align:right;}
  .badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:500;}
  .badge-pendiente{background:var(--color-background-warning);color:var(--color-text-warning);}
  .badge-pagada{background:var(--color-background-success);color:var(--color-text-success);}
  .badge-en_mora,.badge-vencida{background:var(--color-background-danger);color:var(--color-text-danger);}
  .badge-parcial{background:var(--color-background-info);color:var(--color-text-info);}
  .badge-anulada{background:var(--color-background-secondary);color:var(--color-text-secondary);}
  .unspsc{font-size:10px;color:var(--color-text-tertiary);}
  .metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:16px;}
  .metric{background:var(--color-background-secondary);border-radius:var(--border-radius-md);padding:12px 14px;}
  .metric .num{font-size:18px;font-weight:500;color:var(--color-text-primary);}
  .metric .lbl{font-size:11px;color:var(--color-text-secondary);margin-top:3px;}
</style>

@php
  $r        = $this->record->load(['rentalContract','arrendatario','property.tipo','property.municipio','payments.registradoPor']);
  $company  = \App\Models\Company::with(['municipio'])->first();
  $mesAnio  = \Carbon\Carbon::create($r->anio, $r->mes, 1)->translatedFormat('F Y');
  // La retefuente solo la practica un arrendatario persona jurídica — igual que
  // ContabilidadService::generarParaFactura y la plantilla del PDF.
  $aplicaRete = $r->arrendatario?->tipo_persona === 'juridica';
  $rtefonte = $aplicaRete ? round($r->canon_base * 0.035, 2) : 0;
  $netoPagar = $r->total_factura + $r->mora_acumulada - $rtefonte;
  $logoBase64 = null;
  if ($company?->logo_path) {
      $path = storage_path('app/public/' . $company->logo_path);
      if (file_exists($path)) {
          $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
      }
  }
@endphp

<div class="fac">

  {{-- Encabezado oscuro --}}
  <div class="fac-head">
    <div>
      @if($logoBase64)
      <img src="{{ $logoBase64 }}" style="max-height:36px;max-width:140px;display:block;margin-bottom:8px;">
      @else
      <div class="fac-logo">YAROM<span>INMOBILIARIA</span></div>
      @endif
      <div class="fac-logo-sub">{{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}</div>
      <div class="fac-logo-sub">NIT {{ $company?->nit_completo ?? '807.005.762-8' }} · {{ $company?->direccion ?? 'Carrera 13 # 11-15 Of. 103' }}, {{ $company?->municipio?->nombre ?? 'Ocaña' }} N/S</div>
      <div class="fac-logo-sub">{{ $company?->email ?? 'serviarrendarltda@gmail.com' }} · {{ $company?->celular ?? '318 693 4710' }}</div>
    </div>
    <div>
      <div class="fac-num-label">{{ $r->tipo_documento === 'factura_electronica' ? 'Factura de venta electrónica' : 'Documento equivalente' }}</div>
      <div class="fac-num-val">{{ $r->numero_dian ?? $r->numero }}</div>
      <div class="fac-num-tipo">Arrendamiento de inmueble · {{ ucfirst($mesAnio) }}</div>
      <div style="text-align:right;margin-top:8px;">
        <span class="badge badge-{{ $r->estado }}">
          {{ match($r->estado) { 'pendiente'=>'Pendiente','pagada'=>'Pagada','en_mora'=>'En mora','parcial'=>'Parcial','vencida'=>'Vencida','anulada'=>'Anulada',default=>$r->estado } }}
        </span>
      </div>
    </div>
  </div>

  {{-- Barra DIAN --}}
  <div class="fac-dian">
    <div class="fac-dian-txt">Autorización DIAN:</div>
    <div class="fac-dian-res">
      Res. {{ $company?->resolucion_facturacion ?? '18760000001' }} ·
      Rango {{ $company?->prefijo_factura ?? 'FEFE' }}{{ str_pad($company?->consecutivo_desde ?? 1001, 4, '0', STR_PAD_LEFT) }}–{{ $company?->prefijo_factura ?? 'FEFE' }}{{ str_pad($company?->consecutivo_hasta ?? 2000, 4, '0', STR_PAD_LEFT) }}
    </div>
    <div class="fac-dian-txt">Emisión: {{ $r->created_at?->format('d/m/Y H:i') }}</div>
  </div>

  <div class="fac-body">

    {{-- Métricas --}}
    <div class="metrics">
      <div class="metric">
        <div class="num">${{ number_format($r->total_factura, 0, ',', '.') }}</div>
        <div class="lbl">Total factura</div>
      </div>
      <div class="metric">
        <div class="num" style="color:{{ $r->mora_acumulada > 0 ? 'var(--color-text-danger)' : 'var(--color-text-primary)' }};">${{ number_format($r->mora_acumulada, 0, ',', '.') }}</div>
        <div class="lbl">Mora acumulada</div>
      </div>
      <div class="metric">
        <div class="num" style="color:var(--color-text-success);">${{ number_format($r->total_pagado, 0, ',', '.') }}</div>
        <div class="lbl">Pagado</div>
      </div>
      <div class="metric">
        <div class="num" style="color:{{ $r->saldo_pendiente > 0 ? 'var(--color-text-warning)' : 'var(--color-text-success)' }};">${{ number_format($r->saldo_pendiente, 0, ',', '.') }}</div>
        <div class="lbl">Saldo pendiente</div>
      </div>
    </div>

    {{-- Alerta mora --}}
    @if($r->estaEnMora() && $r->estado !== 'pagada')
    <div class="fac-mora">
      <div>
        <div style="font-size:12px;font-weight:500;color:var(--color-text-danger);">Factura en mora — {{ $r->dias_mora }} días</div>
        <div style="font-size:11px;color:var(--color-text-danger);margin-top:2px;">Tasa diaria: {{ $r->tasa_mora_diaria }}% · Mora: ${{ number_format($r->mora_acumulada, 0, ',', '.') }}</div>
      </div>
      <div style="font-size:14px;font-weight:500;color:var(--color-text-danger);">${{ number_format($r->mora_acumulada, 0, ',', '.') }}</div>
    </div>
    @endif

    {{-- Grilla cliente / condiciones --}}
    <div class="fac-grid">
      <div class="fac-block">
        <div class="fac-block-title">Adquirente / Arrendatario</div>
        <div class="fac-row"><label>Nombre</label><span>{{ mb_strtoupper($r->arrendatario?->nombre_completo ?? '', 'UTF-8') }}</span></div>
        <div class="fac-row"><label>{{ $r->arrendatario?->tipo_documento ?? 'CC' }}</label><span>{{ number_format((float)($r->arrendatario?->numero_documento ?? 0), 0, ',', '.') }}</span></div>
        @if($r->arrendatario?->celular)<div class="fac-row"><label>Celular</label><span>{{ $r->arrendatario->celular }}</span></div>@endif
        @if($r->arrendatario?->email)<div class="fac-row"><label>Correo</label><span>{{ $r->arrendatario->email }}</span></div>@endif
      </div>
      <div class="fac-block">
        <div class="fac-block-title">Condiciones de pago</div>
        <div class="fac-row"><label>Contrato</label><span>{{ $r->rentalContract?->numero_contrato }}</span></div>
        <div class="fac-row"><label>Período</label><span>{{ ucfirst($mesAnio) }}</span></div>
        <div class="fac-row"><label>Fecha límite</label><span style="color:{{ $r->estaEnMora() ? 'var(--color-text-danger)' : 'var(--color-text-primary)' }};">{{ $r->fecha_limite_pago?->format('d/m/Y') }}</span></div>
        <div class="fac-row"><label>Días de gracia</label><span>{{ $r->dias_gracia }} días</span></div>
        <div class="fac-row"><label>Forma de pago</label><span>Transferencia / Consignación</span></div>
      </div>
    </div>

    {{-- Tabla de ítems --}}
    <table class="fac-table">
      <thead>
        <tr>
          <th style="width:10%;">Cód.</th>
          <th style="width:52%;">Descripción del servicio</th>
          <th style="width:8%;">Cant.</th>
          <th style="width:15%;">Vr. unitario</th>
          <th style="width:15%;">Total</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><div style="font-weight:500;">001</div><div class="unspsc">UNSPSC: 70330000-3</div></td>
          <td>
            <div style="font-weight:500;">Canon de arrendamiento — {{ ucfirst($mesAnio) }}</div>
            <div style="color:var(--color-text-secondary);font-size:11px;margin-top:2px;">
              {{ $r->property?->tipo?->nombre }} · {{ $r->property?->codigo }} — {{ $r->property?->direccion }}
            </div>
            <div style="color:var(--color-text-secondary);font-size:11px;">Contrato: {{ $r->rentalContract?->numero_contrato }}</div>
          </td>
          <td>1</td>
          <td>${{ number_format($r->canon_base, 0, ',', '.') }}</td>
          <td>${{ number_format($r->canon_base, 0, ',', '.') }}</td>
        </tr>
        @if($r->cuota_administracion > 0)
        <tr>
          <td><div style="font-weight:500;">002</div><div class="unspsc">UNSPSC: 72100000</div></td>
          <td>
            <div style="font-weight:500;">Cuota de administración — {{ ucfirst($mesAnio) }}</div>
            <div style="color:var(--color-text-secondary);font-size:11px;">{{ $r->property?->conjunto_edificio ?? 'Conjunto residencial' }}</div>
          </td>
          <td>1</td>
          <td>${{ number_format($r->cuota_administracion, 0, ',', '.') }}</td>
          <td>${{ number_format($r->cuota_administracion, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($r->otros_cobros > 0)
        <tr>
          <td><div style="font-weight:500;">003</div></td>
          <td><div style="font-weight:500;">{{ $r->descripcion_otros_cobros ?? 'Otros cobros' }}</div></td>
          <td>1</td>
          <td>${{ number_format($r->otros_cobros, 0, ',', '.') }}</td>
          <td>${{ number_format($r->otros_cobros, 0, ',', '.') }}</td>
        </tr>
        @endif
      </tbody>
    </table>

    {{-- Totales --}}
    <div class="fac-totales">
      <div class="fac-tot-row"><label>Subtotal</label><span>${{ number_format($r->total_factura, 0, ',', '.') }}</span></div>
      <div class="fac-tot-row"><label>IVA (0% — Arrendamiento vivienda)</label><span>$0</span></div>
      @if($aplicaRete)
      <div class="fac-tot-row" style="color:var(--color-text-warning);">
        <label style="color:var(--color-text-warning);">ReteFuente arrendamiento 3.5% (Cód. 06)</label>
        <span style="color:var(--color-text-warning);">-${{ number_format($rtefonte, 0, ',', '.') }}</span>
      </div>
      @endif
      @if($r->mora_acumulada > 0)
      <div class="fac-tot-row" style="color:var(--color-text-danger);">
        <label style="color:var(--color-text-danger);">Intereses de mora ({{ $r->dias_mora }} días · {{ $r->tasa_mora_diaria }}% diario)</label>
        <span style="color:var(--color-text-danger);">+${{ number_format($r->mora_acumulada, 0, ',', '.') }}</span>
      </div>
      @endif
      @if($r->descuentos > 0)
      <div class="fac-tot-row" style="color:var(--color-text-success);">
        <label style="color:var(--color-text-success);">Descuentos</label>
        <span style="color:var(--color-text-success);">-${{ number_format($r->descuentos, 0, ',', '.') }}</span>
      </div>
      @endif
      <div class="fac-tot-final">
        <label>Neto a pagar</label>
        <span>${{ number_format($netoPagar, 0, ',', '.') }} COP</span>
      </div>
    </div>

    {{-- Datos para pago --}}
    @if($r->estado !== 'pagada' && $r->estado !== 'anulada')
    <div class="fac-block" style="margin-bottom:16px;">
      <div class="fac-block-title">Datos para consignación o transferencia</div>
      <div class="fac-row"><label>Banco</label><span>{{ $company?->banco ?? 'Bancolombia' }}</span></div>
      <div class="fac-row"><label>Cuenta ahorros</label><span>{{ $company?->numero_cuenta ?? 'N/A' }}</span></div>
      <div class="fac-row"><label>Titular</label><span>{{ $company?->razon_social ?? 'Inmobiliaria Serviarrendar S.A.S' }}</span></div>
      <div class="fac-row"><label>Referencia</label><span>{{ $r->numero }} — {{ mb_strtoupper($r->arrendatario?->nombre_completo ?? '', 'UTF-8') }}</span></div>
    </div>
    @endif

    {{-- Historial de pagos --}}
    @if($r->payments->isNotEmpty())
    <div style="font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.08em;color:var(--color-text-tertiary);margin-bottom:8px;">Pagos registrados</div>
    <div class="fac-pagos">
      @foreach($r->payments as $p)
      <div class="fac-pago-row">
        <div>
          <div style="font-weight:500;font-size:13px;color:var(--color-text-primary);">
            {{ $p->numero }}<span class="fac-pago-badge">{{ ucfirst(str_replace('_',' ',$p->forma_pago)) }}</span>
          </div>
          <div style="font-size:11px;color:var(--color-text-secondary);margin-top:2px;">
            📆 {{ $p->fecha_pago?->format('d/m/Y') }}
            @if($p->bank) · 🏦 {{ $p->bank->nombre }}@elseif($p->banco_origen) · {{ $p->banco_origen }}@endif
            @if($p->referencia_pago) · Ref: {{ $p->referencia_pago }}@endif
          </div>
          <div style="font-size:11px;color:var(--color-text-tertiary);margin-top:1px;">Registrado por {{ $p->registradoPor?->name ?? 'Sistema' }}</div>
        </div>
        <div style="text-align:right;">
          <div style="font-size:16px;font-weight:600;color:var(--color-text-success);">${{ number_format($p->total_pagado, 0, ',', '.') }}</div>
          @if($p->valor_mora > 0)<div style="font-size:11px;color:var(--color-text-danger);">Mora: ${{ number_format($p->valor_mora, 0, ',', '.') }}</div>@endif
          <div class="fac-pago-links">
            <a href="{{ route('pago.pdf', $p) }}" target="_blank" class="fac-pago-link">🧾 Recibo</a>
            @if($p->comprobante_path)
            <a href="{{ asset('storage/'.$p->comprobante_path) }}" target="_blank" class="fac-pago-link">📎 Comprobante</a>
            @endif
            @if($p->arrendatario?->celular)
            <button
              type="button"
              wire:click="enviarReciboWhatsapp({{ $p->id }})"
              wire:loading.attr="disabled"
              wire:target="enviarReciboWhatsapp({{ $p->id }})"
              class="fac-pago-link-wap"
            >
              <span wire:loading.remove wire:target="enviarReciboWhatsapp({{ $p->id }})">📲 Enviar por WhatsApp</span>
              <span wire:loading wire:target="enviarReciboWhatsapp({{ $p->id }})">Enviando…</span>
            </button>
            @else
            <span class="fac-pago-link-wap" style="color:var(--color-text-tertiary);cursor:default;" title="El arrendatario no tiene celular registrado">
              📲 Sin celular
            </span>
            @endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    {{-- CUFE --}}
    @if($r->cufe)
    <div class="fac-cufe">
      <div class="fac-cufe-label">CUFE — Código único de factura electrónica</div>
      <div class="fac-cufe-val">{{ $r->cufe }}</div>
    </div>
    @endif

  </div>

  {{-- Pie de página --}}
  <div class="fac-footer">
    <div class="fac-footer-txt">
      Factura electrónica generada automáticamente de acuerdo al art. 774 del C.C.
      Una vez aceptada, el adquirente declara haber recibido el servicio a satisfacción.
      Representación gráfica de la Factura de Venta Electrónica.
    </div>
    <div class="fac-footer-plat">
      NIT {{ $company?->nit_completo ?? '807.005.762-8' }}<br>
      Software: Yarom Inmobiliaria
    </div>
  </div>

</div>
</x-filament-panels::page>
