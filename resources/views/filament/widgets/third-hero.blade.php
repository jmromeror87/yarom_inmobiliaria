@php
    use Illuminate\Support\Str;
    if (!$record) return;

    $initials = collect([
        $record->primer_nombre ?? $record->razon_social,
        $record->primer_apellido,
    ])->filter()->map(fn($w) => Str::upper(Str::substr($w, 0, 1)))->join('');

    $roles = [];
    if ($record->es_propietario)    $roles[] = ['label' => 'Propietario',  'bg' => '#eff6ff', 'color' => '#1d4ed8', 'emoji' => '🏠'];
    if ($record->es_arrendatario)   $roles[] = ['label' => 'Arrendatario', 'bg' => '#fef2f2', 'color' => '#991b1b', 'emoji' => '🔑'];
    if ($record->es_cliente_compra) $roles[] = ['label' => 'Comprador',    'bg' => '#f0fdf4', 'color' => '#166534', 'emoji' => '🛒'];
    if ($record->es_fiador)         $roles[] = ['label' => 'Fiador',       'bg' => '#fdf4ff', 'color' => '#7e22ce', 'emoji' => '🤝'];
    if ($record->es_proveedor)      $roles[] = ['label' => 'Proveedor',    'bg' => '#fffbeb', 'color' => '#92400e', 'emoji' => '🔧'];

    $creditColor = match($record->estado_crediticio) {
        'aprobado'    => ['bg' => '#f0fdf4', 'color' => '#166534', 'label' => '✓ Aprobado'],
        'rechazado'   => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => '✕ Rechazado'],
        'condicional' => ['bg' => '#fffbeb', 'color' => '#92400e', 'label' => '⚠ Condicional'],
        'en_proceso'  => ['bg' => '#eff6ff', 'color' => '#1d4ed8', 'label' => '🔍 En estudio'],
        default       => ['bg' => '#f8fafc', 'color' => '#64748b', 'label' => '⏳ Sin evaluar'],
    };

    $expColor = match($record->estado_expediente) {
        'completo'  => ['bg' => '#f0fdf4', 'color' => '#166534', 'label' => '✓ Completo'],
        'bloqueado' => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => '🚫 Bloqueado'],
        default     => ['bg' => '#fffbeb', 'color' => '#92400e', 'label' => '⏳ Incompleto'],
    };
@endphp

<div style="background:linear-gradient(135deg,#0F172A 0%,#1e2d45 55%,#1a1f3a 100%);border-radius:1.25rem;padding:28px 32px;margin-bottom:16px;display:flex;align-items:center;gap:28px;position:relative;overflow:hidden;">

    {{-- Decoración fondo --}}
    <div style="position:absolute;right:-40px;top:-40px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(225,29,72,.18),transparent 70%);pointer-events:none;"></div>
    <div style="position:absolute;right:100px;bottom:-30px;width:140px;height:140px;border-radius:50%;background:radial-gradient(circle,rgba(37,99,235,.12),transparent 70%);pointer-events:none;"></div>

    {{-- Avatar --}}
    <div style="width:80px;height:80px;border-radius:20px;background:linear-gradient(135deg,#1e3a8a,#E11D48);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 8px 24px rgba(225,29,72,.3);">
        <span style="font-size:28px;font-weight:900;color:#fff;letter-spacing:-.02em;">{{ $initials ?: '?' }}</span>
    </div>

    {{-- Info principal --}}
    <div style="flex:1;min-width:0;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <h2 style="font-size:20px;font-weight:900;color:#fff;margin:0;letter-spacing:-.02em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ Str::upper($record->nombre_completo) }}
            </h2>
            @if($record->is_active)
                <span style="font-size:9.5px;font-weight:800;background:#16a34a;color:#fff;border-radius:99px;padding:2px 10px;letter-spacing:.06em;text-transform:uppercase;">● Activo</span>
            @else
                <span style="font-size:9.5px;font-weight:800;background:#64748b;color:#fff;border-radius:99px;padding:2px 10px;letter-spacing:.06em;text-transform:uppercase;">● Inactivo</span>
            @endif
        </div>

        <div style="font-size:12.5px;color:rgba(255,255,255,.55);margin-top:4px;font-weight:500;">
            {{ $record->tipo_documento ?? 'CC' }} {{ $record->numero_documento }}
            @if($record->municipio) · {{ $record->municipio->nombre }}, {{ $record->departamento?->nombre }} @endif
            @if($record->celular) · {{ $record->celular }} @endif
        </div>

        {{-- Roles --}}
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">
            @foreach($roles as $role)
                <span style="font-size:10.5px;font-weight:700;background:{{ $role['bg'] }};color:{{ $role['color'] }};border-radius:99px;padding:3px 10px;">
                    {{ $role['emoji'] }} {{ $role['label'] }}
                </span>
            @endforeach
            @if(empty($roles))
                <span style="font-size:10.5px;color:rgba(255,255,255,.4);">Sin rol asignado</span>
            @endif
        </div>
    </div>

    {{-- Stats rápidos --}}
    <div style="display:flex;gap:12px;flex-shrink:0;">
        {{-- Crédito --}}
        <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:14px 18px;text-align:center;min-width:110px;">
            <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">Crédito</div>
            <span style="font-size:10.5px;font-weight:700;background:{{ $creditColor['bg'] }};color:{{ $creditColor['color'] }};border-radius:99px;padding:3px 10px;">
                {{ $creditColor['label'] }}
            </span>
        </div>

        {{-- Expediente --}}
        <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:14px 18px;text-align:center;min-width:110px;">
            <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">Expediente</div>
            <span style="font-size:10.5px;font-weight:700;background:{{ $expColor['bg'] }};color:{{ $expColor['color'] }};border-radius:99px;padding:3px 10px;">
                {{ $expColor['label'] }}
            </span>
        </div>

        {{-- Habeas Data --}}
        <div style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:14px;padding:14px 18px;text-align:center;min-width:110px;">
            <div style="font-size:9.5px;font-weight:800;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">Habeas Data</div>
            @if($record->habeas_data_aceptado)
                <span style="font-size:10.5px;font-weight:700;background:#f0fdf4;color:#166534;border-radius:99px;padding:3px 10px;">✓ Firmado</span>
            @else
                <span style="font-size:10.5px;font-weight:700;background:#fef2f2;color:#991b1b;border-radius:99px;padding:3px 10px;">✕ Pendiente</span>
            @endif
        </div>
    </div>

</div>
