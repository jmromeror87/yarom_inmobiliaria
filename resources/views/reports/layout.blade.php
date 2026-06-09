<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #1E293B; background:#fff; }

/* ── ENCABEZADO ─────────────────────────────────────────── */
.header { background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); color: #fff; padding: 18px 24px 14px; margin-bottom: 0; }
.header-empresa { font-size: 16px; font-weight: 900; letter-spacing: -.02em; text-transform: uppercase; }
.header-nit { font-size: 9px; color: rgba(255,255,255,.55); margin-top: 2px; }
.header-titulo { font-size: 20px; font-weight: 900; letter-spacing: -.03em; margin-top: 10px; }
.header-periodo { font-size: 10px; color: rgba(255,255,255,.7); margin-top: 4px; }
.header-top { display: flex; justify-content: space-between; align-items: flex-start; }
.header-logo { font-size: 24px; }
.header-fecha { font-size: 9px; color: rgba(255,255,255,.5); text-align: right; margin-top: 4px; }

/* ── KPIs ──────────────────────────────────────────────── */
.kpis { display: flex; gap: 0; margin-bottom: 16px; }
.kpi { flex: 1; padding: 12px 10px; text-align: center; border-right: 1px solid rgba(255,255,255,.2); }
.kpi:last-child { border-right: none; }
.kpi-icon { font-size: 18px; }
.kpi-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-top: 4px; opacity: .8; }
.kpi-valor { font-size: 14px; font-weight: 900; margin-top: 2px; }
.kpi-green  { background: #DCFCE7; color: #14532D; }
.kpi-red    { background: #FEE2E2; color: #7F1D1D; }
.kpi-blue   { background: #DBEAFE; color: #1E3A8A; }
.kpi-orange { background: #FED7AA; color: #7C2D12; }
.kpi-gray   { background: #F1F5F9; color: #0F172A; }
.kpi-purple { background: #EDE9FE; color: #4C1D95; }
.kpi-emerald{ background: #D1FAE5; color: #064E3B; }
.kpi-indigo { background: #E0E7FF; color: #312E81; }

/* ── SECCIONES ─────────────────────────────────────────── */
.section-title { background: #0F172A; color: #fff; padding: 7px 12px; font-size: 11px; font-weight: 900; margin-bottom: 0; margin-top: 14px; border-radius: 6px 6px 0 0; }
.section-title-green  { background: #14532D; }
.section-title-red    { background: #7F1D1D; }
.section-title-blue   { background: #1E3A8A; }

/* ── TABLAS ─────────────────────────────────────────────── */
table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
th { background: #1E3A8A; color: #fff; padding: 6px 8px; font-size: 9px; font-weight: 700; text-align: left; text-transform: uppercase; letter-spacing: .04em; }
th.right, td.right { text-align: right; }
th.center, td.center { text-align: center; }
td { padding: 5px 8px; font-size: 9.5px; border-bottom: 1px solid #E2E8F0; }
tr:nth-child(even) td { background: #F8FAFC; }
tr:hover td { background: #EFF6FF; }
.tr-total td { background: #E2E8F0 !important; font-weight: 900; font-size: 10px; border-top: 2px solid #1E3A8A; }
.tr-resultado td { background: #DBEAFE !important; font-weight: 900; font-size: 12px; color: #1E3A8A; border-top: 3px solid #1E3A8A; padding: 10px 8px; }
.tr-resultado-positive td { background: #DCFCE7 !important; color: #14532D; border-top-color: #16A34A; }
.tr-resultado-negative td { background: #FEE2E2 !important; color: #7F1D1D; border-top-color: #DC2626; }

/* ── PIE DE PÁGINA ─────────────────────────────────────── */
.footer { position: fixed; bottom: 0; left: 0; right: 0; background: #F8FAFC; border-top: 1px solid #E2E8F0; padding: 5px 16px; display: flex; justify-content: space-between; font-size: 8px; color: #94A3B8; }
.page-number:before { content: "Página " counter(page) " de " counter(pages); }

/* ── MISC ───────────────────────────────────────────────── */
.text-right { text-align: right; }
.text-center { text-align: center; }
.money { font-family: monospace; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 8px; font-weight: 700; }
.badge-green  { background: #DCFCE7; color: #14532D; }
.badge-red    { background: #FEE2E2; color: #7F1D1D; }
.badge-blue   { background: #DBEAFE; color: #1E3A8A; }
.nota { background: #FEFCE8; border-left: 3px solid #FDE68A; padding: 8px 12px; margin: 10px 0; font-size: 9px; color: #78350F; border-radius: 0 6px 6px 0; }
.content { padding: 0 16px 48px; }
</style>
</head>
<body>

{{-- ENCABEZADO --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="header-empresa">{{ $company?->razon_social ?? 'Serviarrendar S.A.S' }}</div>
            <div class="header-nit">NIT {{ $company?->nit_completo ?? '' }} &nbsp;|&nbsp; {{ $company?->email ?? '' }} &nbsp;|&nbsp; {{ $company?->telefono ?? '' }}</div>
            <div class="header-titulo">{{ $data['titulo'] ?? 'Informe Contable' }}</div>
            <div class="header-periodo">{{ $data['periodo_label'] ?? $data['hasta_label'] ?? '' }}</div>
        </div>
        <div style="text-align:right;">
            <div class="header-logo">🏢</div>
            <div class="header-fecha">Generado: {{ now()->locale('es')->isoFormat('D MMM YYYY, H:mm') }}</div>
        </div>
    </div>
</div>

{{-- KPIs --}}
@if(!empty($data['kpis']))
<div class="kpis">
    @foreach($data['kpis'] as $kpi)
    <div class="kpi kpi-{{ $kpi['color'] ?? 'gray' }}">
        <div class="kpi-icon">{{ $kpi['icon'] ?? '📊' }}</div>
        <div class="kpi-label">{{ $kpi['label'] }}</div>
        <div class="kpi-valor">
            @if($kpi['es_pct'] ?? false)
                {{ $kpi['valor'] }}
            @else
                @if(is_numeric($kpi['valor']))
                    ${{ number_format((float)$kpi['valor'], 0, ',', '.') }}
                @else
                    {{ $kpi['valor'] }}
                @endif
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- CONTENIDO PRINCIPAL --}}
<div class="content">
    @yield('content')
</div>

{{-- PIE --}}
<div class="footer">
    <span>YarOM ERP — Serviarrendar S.A.S &nbsp;|&nbsp; Sistema de Gestión Inmobiliaria</span>
    <span class="page-number"></span>
</div>
</body>
</html>
