<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Solicitud de Servicio</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
  <tr><td align="center">
    <table width="580" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

      {{-- Header --}}
      <tr>
        <td style="background:linear-gradient(135deg,#0F172A 0%,#0369a1 100%);padding:32px 40px;">
          <table width="100%"><tr>
            <td>
              <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;">
                YarOM Inmobiliaria — Serviarrendar S.A.S
              </div>
              <div style="font-size:24px;font-weight:900;color:#ffffff;letter-spacing:-.02em;">
                Solicitud de Servicio
              </div>
              <div style="margin-top:10px;">
                <span style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);color:#fff;
                             border-radius:20px;padding:4px 14px;font-size:12px;font-weight:700;font-family:monospace;">
                  {{ $servicio->numero }}
                </span>
              </div>
            </td>
            <td align="right">
              <div style="width:52px;height:52px;background:rgba(255,255,255,.1);border-radius:14px;
                          display:flex;align-items:center;justify-content:center;font-size:28px;">
                🔧
              </div>
            </td>
          </tr></table>
        </td>
      </tr>

      {{-- Saludo --}}
      <tr>
        <td style="padding:32px 40px 0;">
          <p style="font-size:16px;color:#1e293b;margin:0 0 6px;font-weight:600;">
            Estimado/a <strong>{{ $servicio->proveedor->nombre_completo }}</strong>,
          </p>
          <p style="font-size:14px;color:#64748b;margin:0;line-height:1.6;">
            Le informamos que ha sido asignado para realizar el siguiente servicio en nuestro inmueble administrado. Por favor revise los detalles y confírmenos su disponibilidad.
          </p>
        </td>
      </tr>

      {{-- Detalle del servicio --}}
      <tr>
        <td style="padding:24px 40px 0;">
          <table width="100%" cellpadding="0" cellspacing="0"
                 style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">
            <tr>
              <td style="background:#1e3a8a;padding:12px 20px;">
                <span style="font-size:11px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.08em;">
                  Detalles del servicio
                </span>
              </td>
            </tr>
            <tr><td style="padding:20px;">
              <table width="100%" cellpadding="6" cellspacing="0">
                <tr>
                  <td style="font-size:12px;color:#64748b;font-weight:600;width:40%;">Número:</td>
                  <td style="font-size:13px;color:#0f172a;font-weight:800;font-family:monospace;">{{ $servicio->numero }}</td>
                </tr>
                <tr style="background:#f1f5f9;">
                  <td style="font-size:12px;color:#64748b;font-weight:600;padding:8px 6px;">Tipo:</td>
                  <td style="font-size:13px;color:#0f172a;font-weight:600;padding:8px 6px;">{{ $servicio->tipo_label }}</td>
                </tr>
                <tr>
                  <td style="font-size:12px;color:#64748b;font-weight:600;">Inmueble:</td>
                  <td style="font-size:13px;color:#0f172a;font-weight:600;">
                    {{ $servicio->property->direccion ?? '—' }}
                    {{ $servicio->property->apto_casa_oficina ? '— ' . $servicio->property->apto_casa_oficina : '' }}
                  </td>
                </tr>
                <tr style="background:#f1f5f9;">
                  <td style="font-size:12px;color:#64748b;font-weight:600;padding:8px 6px;">Fecha del servicio:</td>
                  <td style="font-size:13px;color:#0f172a;font-weight:700;padding:8px 6px;">
                    {{ $servicio->fecha_servicio->format('d/m/Y') }}
                  </td>
                </tr>
                <tr>
                  <td style="font-size:12px;color:#64748b;font-weight:600;">Valor:</td>
                  <td style="font-size:15px;color:#16a34a;font-weight:900;">
                    $ {{ number_format($servicio->valor, 0, ',', '.') }} COP
                  </td>
                </tr>
              </table>
            </td></tr>
          </table>
        </td>
      </tr>

      {{-- Descripción --}}
      <tr>
        <td style="padding:20px 40px 0;">
          <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:16px 18px;">
            <div style="font-size:11px;font-weight:700;color:#d97706;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px;">
              Descripción del trabajo
            </div>
            <p style="font-size:14px;color:#1e293b;margin:0;line-height:1.6;">{{ $servicio->descripcion }}</p>
          </div>
        </td>
      </tr>

      @if($servicio->notas)
      <tr>
        <td style="padding:14px 40px 0;">
          <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:10px;padding:14px 18px;">
            <div style="font-size:11px;font-weight:700;color:#0284c7;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;">
              Notas adicionales
            </div>
            <p style="font-size:13px;color:#1e293b;margin:0;line-height:1.5;">{{ $servicio->notas }}</p>
          </div>
        </td>
      </tr>
      @endif

      {{-- Contacto --}}
      <tr>
        <td style="padding:24px 40px;">
          <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px 18px;">
            <p style="font-size:12px;color:#64748b;margin:0 0 4px;font-weight:600;">¿Preguntas? Contáctenos:</p>
            <p style="font-size:13px;color:#1e293b;margin:0;font-weight:700;">Serviarrendar S.A.S</p>
            <p style="font-size:12px;color:#0284c7;margin:4px 0 0;">
              administracion@serviarrendar.com.co
            </p>
          </div>
        </td>
      </tr>

      {{-- Footer --}}
      <tr>
        <td style="background:#0F172A;padding:18px 40px;text-align:center;">
          <p style="font-size:11px;color:rgba(255,255,255,.4);margin:0;">
            © {{ date('Y') }} YarOM ERP — Serviarrendar S.A.S — Todos los derechos reservados
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>

</body>
</html>
