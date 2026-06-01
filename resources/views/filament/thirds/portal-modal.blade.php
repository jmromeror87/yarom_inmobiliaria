@php
    $url = $record->portal_url;
@endphp

<div style="padding: 4px 0;">

    @if($record->portal_activo && $url)
        {{-- Link activo --}}
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 16px; margin-bottom: 16px;">
            <p style="font-size: 12px; color: #16a34a; font-weight: 600; margin-bottom: 8px;">✅ Portal activo</p>
            <div style="display: flex; align-items: center; gap: 8px;">
                <input
                    id="portal-link-{{ $record->id }}"
                    type="text"
                    readonly
                    value="{{ $url }}"
                    style="flex: 1; font-size: 12px; font-family: monospace; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; color: #374151;"
                    onclick="this.select()"
                >
                <button
                    onclick="navigator.clipboard.writeText('{{ $url }}').then(() => { this.textContent = '✓'; setTimeout(() => this.textContent = 'Copiar', 1500); })"
                    style="padding: 8px 14px; background: #6366f1; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; white-space: nowrap;"
                >Copiar</button>
            </div>
            @if($record->celular)
            <a
                href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $record->celular) }}?text={{ urlencode('Hola ' . $record->primer_nombre . ', le compartimos su portal de propietario: ' . $url) }}"
                target="_blank"
                style="display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 12px; font-weight: 600; color: #16a34a;"
            >
                <svg style="width:14px;height:14px;" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Enviar por WhatsApp a {{ $record->celular }}
            </a>
            @endif
        </div>

        <p style="font-size: 11px; color: #9ca3af; margin-bottom: 12px;">
            Generado el {{ $record->portal_token_generado_at?->format('d/m/Y H:i') }}
        </p>

        <div style="display: flex; gap: 8px;">
            <form method="POST" action="{{ route('portal.regenerar', $record->id) }}" style="display:inline;">
                @csrf
                <button type="submit" style="padding: 8px 16px; background: #6366f1; color: #fff; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    🔄 Regenerar link
                </button>
            </form>
            <form method="POST" action="{{ route('portal.revocar', $record->id) }}" style="display:inline;">
                @csrf
                <button type="submit" style="padding: 8px 16px; background: #fee2e2; color: #ef4444; border: 1px solid #fca5a5; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;">
                    🚫 Revocar acceso
                </button>
            </form>
        </div>

    @else
        {{-- Sin link --}}
        <div style="text-align: center; padding: 24px 0;">
            <div style="font-size: 40px; margin-bottom: 12px;">🔒</div>
            <p style="font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 6px;">Sin acceso al portal</p>
            <p style="font-size: 13px; color: #9ca3af; margin-bottom: 20px;">
                {{ $record->nombre_completo }} aún no tiene link de acceso al portal.
            </p>
            <form method="POST" action="{{ route('portal.regenerar', $record->id) }}">
                @csrf
                <button type="submit" style="padding: 10px 24px; background: #6366f1; color: #fff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    🔗 Generar link de acceso
                </button>
            </form>
        </div>
    @endif
</div>
