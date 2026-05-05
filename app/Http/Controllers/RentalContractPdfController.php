<?php
namespace App\Http\Controllers;

use App\Models\RentalContract;
use App\Models\Company;
use Barryvdh\DomPDF\Facade\Pdf;

class RentalContractPdfController extends Controller
{
    public function download(RentalContract $contract)
    {
        $contract->load([
            'property.tipo','property.municipio','property.departamento',
            'arrendatario','thirds.third','clauses','asesor',
        ]);

        $company = Company::with(['municipio'])->first();

        $logoBase64 = null;
        if ($company?->logo_path) {
            $path = storage_path('app/public/' . $company->logo_path);
            if (file_exists($path)) {
                $logoBase64 = 'data:' . mime_content_type($path) . ';base64,' . base64_encode(file_get_contents($path));
            }
        }

        // ── Reemplazar variables dinámicas en las cláusulas ──
        $arr  = $contract->arrendatario;
        $arrNombre = mb_strtoupper($arr?->nombre_completo ?? '', 'UTF-8');
        $arrDoc    = number_format((float)($arr?->numero_documento ?? 0), 0, ',', '.');
        $arrLugar  = $arr?->lugar_nacimiento ?? '';

        // Construir texto de deudores solidarios
        $deudoresTexto = $contract->thirds->map(function ($t) {
            $nombre = mb_strtoupper($t->third?->nombre_completo ?? '', 'UTF-8');
            $doc    = number_format((float)($t->third?->numero_documento ?? 0), 0, ',', '.');
            $ciudad = $t->ciudad_expedicion_doc ?? ($t->third?->lugar_nacimiento ?? '');
            return "Yo, {$nombre} identificad" . ($t->third?->genero === 'femenino' ? 'a' : 'o') . " con cédula de ciudadanía {$doc}" . ($ciudad ? " de {$ciudad}" : '');
        })->implode(' y ');

        // Dirección arrendatario para notificación
        $arrDireccion = $arr?->direccion_residencia ?? $contract->property?->direccion ?? '';
        $arrEmail     = $arr?->email ?? '';
        $arrCelular   = $arr?->celular ?? '';
        $arrContacto  = $arrDireccion;
        if ($arrEmail)   $arrContacto .= ' o al correo electrónico ' . $arrEmail;
        if ($arrCelular) $arrContacto .= ' o al número de celular ' . $arrCelular;

        // Dirección deudores para notificación
        $deudoresDirecciones = $contract->thirds->map(function ($t) {
            $nombre  = mb_strtoupper($t->third?->nombre_completo ?? '', 'UTF-8');
            $dir     = $t->direccion_notificacion ?? $t->third?->direccion_residencia ?? '';
            $email   = $t->email_notificacion ?? $t->third?->email ?? '';
            $celular = $t->celular_notificacion ?? $t->third?->celular ?? '';
            $texto   = $nombre . ($dir ? ' a la dirección ' . $dir : '');
            if ($email)   $texto .= ' o al correo electrónico ' . $email;
            if ($celular) $texto .= ' o al número de celular ' . $celular;
            return $texto;
        })->implode('. ');

        // Servicios
        $servicios = mb_strtoupper($contract->servicios_cargo_arrendatario ?? '', 'UTF-8');

        // Empresa datos de contacto
        $empresaDireccion = ($company?->direccion ?? 'carrera 13 # 11-15 ofc 103 centro') . '- ' . ($company?->municipio?->nombre ?? 'Ocaña');
        $empresaEmail     = $company?->email ?? 'serviarrendarltda@gmail.com';
        $empresaCelular   = $company?->celular ?? '3186934710';
        $repLegal         = mb_strtoupper($company?->rep_legal_nombre ?? 'YANETH DEL CARMEN PÉREZ ARÉVALO', 'UTF-8');

        $reemplazos = [
            '{SERVICIOS}'              => $servicios,
            '{ARRENDATARIO}'           => $arrNombre . ' identificad' . ($arr?->genero === 'femenino' ? 'a' : 'o') . ' con cédula de ciudadanía ' . $arrDoc . ($arrLugar ? ' de ' . $arrLugar : ''),
            '{DEUDOR_SOLIDARIO}'       => $deudoresTexto ?: 'EL DEUDOR SOLIDARIO',
            '{DEUDORES_SOLIDARIOS}'    => $deudoresTexto ?: 'LOS DEUDORES SOLIDARIOS',
            '{DIRECCION_ARRENDATARIO}' => $arrContacto,
            '{DIRECCION_DEUDORES}'     => $deudoresDirecciones,
            '{EMPRESA_DIRECCION}'      => $empresaDireccion,
            '{EMPRESA_EMAIL}'          => $empresaEmail,
            '{EMPRESA_CELULAR}'        => $empresaCelular,
            '{REP_LEGAL}'              => $repLegal,
        ];

        // Aplicar reemplazos a todas las cláusulas
        foreach ($contract->clauses as $clausula) {
            $clausula->contenido_actual = str_replace(
                array_keys($reemplazos),
                array_values($reemplazos),
                $clausula->contenido_actual
            );
        }

        $pdf = Pdf::loadView('pdf.contrato-arriendo', compact('contract', 'company', 'logoBase64'))
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled'         => false,
                'dpi'                  => 150,
            ]);

        return $pdf->download('Contrato-' . $contract->numero_contrato . '.pdf');
    }
}
