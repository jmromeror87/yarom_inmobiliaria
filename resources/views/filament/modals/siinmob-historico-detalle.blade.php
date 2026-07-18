<div style="font-family:'Plus Jakarta Sans',sans-serif;">
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:0.8rem;color:#475569;">
        <div><strong>Fecha:</strong> {{ $nota->fecha->format('d/m/Y') }} &nbsp; <strong>Tipo:</strong> {{ $nota->tipo === 'NC' ? 'Manual' : 'Automática' }} &nbsp; <strong>Creada por:</strong> {{ $nota->creada_por }}</div>
        @if($nota->concepto)
        <div style="margin-top:4px;"><strong>Concepto:</strong> {{ $nota->concepto }}</div>
        @endif
    </div>

    <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
        <thead>
            <tr style="background:#f1f5f9;">
                <th style="padding:6px 8px;text-align:left;border:1px solid #e2e8f0;">Cuenta</th>
                <th style="padding:6px 8px;text-align:left;border:1px solid #e2e8f0;">Descripción</th>
                <th style="padding:6px 8px;text-align:right;border:1px solid #e2e8f0;">Débito</th>
                <th style="padding:6px 8px;text-align:right;border:1px solid #e2e8f0;">Crédito</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineas as $l)
            <tr>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;">
                    <strong>{{ $l->cuenta_codigo }}</strong><br>
                    <span style="color:#64748b;">{{ $l->cuenta_nombre }}</span>
                </td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;color:#64748b;">{{ $l->descripcion_linea }}</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">
                    @if($l->debito > 0) ${{ number_format($l->debito, 0, ',', '.') }} @endif
                </td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">
                    @if($l->credito > 0) ${{ number_format($l->credito, 0, ',', '.') }} @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;font-weight:bold;">
                <td colspan="2" style="padding:6px 8px;border:1px solid #e2e8f0;">TOTALES</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">${{ number_format($nota->total_debito, 0, ',', '.') }}</td>
                <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">${{ number_format($nota->total_credito, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
