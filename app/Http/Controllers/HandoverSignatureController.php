<?php
namespace App\Http\Controllers;

use App\Models\PropertyHandover;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HandoverSignatureController extends Controller
{
    public function store(Request $request, PropertyHandover $handover)
    {
        $request->validate([
            'firmante' => 'required|in:arrendatario,asesor',
            'firma'    => 'required|string',
        ]);

        $firmante = $request->firmante;
        $campo    = $firmante === 'asesor' ? 'firma_digital_asesor' : 'firma_digital_arrendatario';

        // Guardar imagen
        $filename  = 'actas/firmas/' . $handover->numero . '-' . $firmante . '.png';
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->firma));
        Storage::disk('public')->put($filename, $imageData);

        $handover->update([$campo => $request->firma]);
        $handover->refresh();

        // Estado según firmas
        if ($handover->firma_digital_arrendatario && $handover->firma_digital_asesor) {
            $handover->update(['estado' => 'firmada']);
            $mensaje = 'ambas';
        } else {
            $handover->update(['estado' => 'en_proceso']);
            $mensaje = $firmante;
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => $mensaje,
            'estado'  => $handover->estado,
        ]);
    }
}
