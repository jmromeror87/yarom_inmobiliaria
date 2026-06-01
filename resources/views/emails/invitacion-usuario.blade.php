<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invitación YarOM</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
  <tr>
    <td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        {{-- Header --}}
        <tr>
          <td style="background:#0A192F;padding:32px 40px;">
            <table width="100%"><tr>
              <td>
                <div style="font-size:22px;font-weight:900;color:#ffffff;letter-spacing:-0.03em;text-transform:uppercase;">
                  YAROM <span style="color:#E11D48;">INMO</span>BILIARIA
                </div>
                <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;margin-top:4px;">
                  Serviarrendar S.A.S — Gestión Inmobiliaria
                </div>
              </td>
              <td align="right">
                <div style="background:#E11D48;color:#fff;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.1em;padding:6px 14px;border-radius:8px;">
                  Invitación
                </div>
              </td>
            </tr></table>
          </td>
        </tr>

        {{-- Body --}}
        <tr>
          <td style="padding:40px;">

            <p style="font-size:15px;color:#0f172a;margin:0 0 8px;">Hola, <strong>{{ $usuario->name }}</strong> 👋</p>

            <p style="font-size:14px;color:#475569;line-height:1.7;margin:0 0 24px;">
              Has sido invitado al sistema <strong>YarOM ERP</strong> con el rol de
              <span style="background:#e8edff;color:#0E01A3;padding:2px 10px;border-radius:6px;font-weight:700;font-size:13px;">
                {{ match($rolNombre) {
                    'super_admin'  => '🛡️ Super Administrador',
                    'admin'        => '⚙️ Administrador',
                    'asesor'       => '🏠 Asesor',
                    'contador'     => '📊 Contador',
                    'solo_lectura' => '👁️ Solo lectura',
                    default        => $rolNombre,
                } }}
              </span>.
            </p>

            <p style="font-size:14px;color:#475569;line-height:1.7;margin:0 0 32px;">
              Para activar tu cuenta y crear tu contraseña, haz clic en el botón:
            </p>

            {{-- CTA Button --}}
            <table width="100%"><tr><td align="center" style="padding-bottom:32px;">
              <a href="{{ $urlActivacion }}"
                 style="display:inline-block;background:linear-gradient(135deg,#0E01A3,#2563EB);color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;padding:14px 40px;border-radius:12px;letter-spacing:-0.01em;">
                Activar mi cuenta →
              </a>
            </td></tr></table>

            {{-- Info box --}}
            <table width="100%" style="background:#f8faff;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
              <tr><td style="padding:20px 24px;">
                <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#64748b;">Información de acceso</p>
                <table>
                  <tr>
                    <td style="font-size:13px;color:#64748b;padding:3px 12px 3px 0;width:90px;">Correo:</td>
                    <td style="font-size:13px;color:#0f172a;font-weight:600;">{{ $usuario->email }}</td>
                  </tr>
                  <tr>
                    <td style="font-size:13px;color:#64748b;padding:3px 12px 3px 0;">Sistema:</td>
                    <td style="font-size:13px;color:#0f172a;font-weight:600;">{{ config('app.url') }}/admin</td>
                  </tr>
                </table>
              </td></tr>
            </table>

            {{-- URL fallback --}}
            <p style="font-size:12px;color:#94a3b8;margin:0 0 4px;">Si el botón no funciona, copia este enlace en tu navegador:</p>
            <p style="font-size:11px;color:#2563EB;word-break:break-all;margin:0;">{{ $urlActivacion }}</p>

          </td>
        </tr>

        {{-- Footer --}}
        <tr>
          <td style="background:#f8faff;border-top:1px solid #e2e8f0;padding:20px 40px;">
            <p style="margin:0;font-size:11px;color:#94a3b8;text-align:center;">
              © {{ date('Y') }} <strong>YarOM ERP</strong> — Serviarrendar S.A.S<br>
              Desarrollado por <strong>Ing. Jhoan Romero</strong>
            </p>
          </td>
        </tr>

      </table>
    </td>
  </tr>
</table>

</body>
</html>
