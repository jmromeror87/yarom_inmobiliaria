<?php

namespace App\Http\Controllers;

use App\Models\PropertyHandover;
use App\Models\PropertyHandoverItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActaPublicaController extends Controller
{
    // ── Mostrar acta pública ──────────────────────────────
    public function show(string $token)
    {
        $acta = PropertyHandover::where('acta_token', $token)
            ->with(['property.tipo', 'arrendatario', 'asesor', 'items', 'rentalContract'])
            ->first();

        // Token inválido o acta ya cerrada (token fue anulado al cerrar)
        if (!$acta) {
            return view('acta-publica.cerrada');
        }

        if ($acta->estado === 'cerrada') {
            return view('acta-publica.cerrada', compact('acta'));
        }

        $itemsJson = $acta->items->map(fn($i) => [
            'id'          => $i->id,
            'ambiente'    => $i->ambiente,
            'elemento'    => $i->elemento,
            'estado'      => $i->estado ?? 'bueno',
            'descripcion' => $i->descripcion ?? '',
            'foto_path'   => $i->foto_path ? Storage::url($i->foto_path) : null,
            'subiendo'    => false,
        ])->values()->toArray();

        return view('acta-publica.index', compact('acta', 'token', 'itemsJson'));
    }

    // ── Guardar datos del acta desde el móvil ────────────
    public function guardar(Request $request, string $token)
    {
        $acta = PropertyHandover::where('acta_token', $token)->firstOrFail();

        if ($acta->estado === 'cerrada') {
            return response()->json(['ok' => false, 'error' => 'Acta ya cerrada'], 403);
        }

        $data = $request->validate([
            'lectura_agua'               => 'nullable|string|max:50',
            'lectura_energia'            => 'nullable|string|max:50',
            'lectura_gas'                => 'nullable|string|max:50',
            'llaves_entregadas'          => 'nullable|integer|min:0',
            'llaves_control_acceso'      => 'nullable|integer|min:0',
            'llaves_parqueadero'         => 'nullable|integer|min:0',
            'llaves_deposito'            => 'nullable|integer|min:0',
            'notas_llaves'               => 'nullable|string|max:500',
            'estado_general'             => 'nullable|in:excelente,bueno,regular,malo',
            'observaciones_generales'    => 'nullable|string|max:2000',
            'firma_digital_asesor'       => 'nullable|string',
            'firma_digital_arrendatario' => 'nullable|string',
            'items'                      => 'nullable|array',
            'items.*.id'                 => 'nullable|integer',
            'items.*.estado'             => 'nullable|in:excelente,bueno,regular,malo,no_aplica',
            'items.*.descripcion'        => 'nullable|string|max:500',
        ]);

        $acta->update([
            'lectura_agua'               => $data['lectura_agua'] ?? $acta->lectura_agua,
            'lectura_energia'            => $data['lectura_energia'] ?? $acta->lectura_energia,
            'lectura_gas'                => $data['lectura_gas'] ?? $acta->lectura_gas,
            'llaves_entregadas'          => $data['llaves_entregadas'] ?? $acta->llaves_entregadas,
            'llaves_control_acceso'      => $data['llaves_control_acceso'] ?? $acta->llaves_control_acceso,
            'llaves_parqueadero'         => $data['llaves_parqueadero'] ?? $acta->llaves_parqueadero,
            'llaves_deposito'            => $data['llaves_deposito'] ?? $acta->llaves_deposito,
            'notas_llaves'               => $data['notas_llaves'] ?? $acta->notas_llaves,
            'estado_general'             => $data['estado_general'] ?? $acta->estado_general,
            'observaciones_generales'    => $data['observaciones_generales'] ?? $acta->observaciones_generales,
            'firma_digital_asesor'       => $data['firma_digital_asesor'] ?? $acta->firma_digital_asesor,
            'firma_digital_arrendatario' => $data['firma_digital_arrendatario'] ?? $acta->firma_digital_arrendatario,
            'estado'                     => 'en_proceso',
        ]);

        // Actualizar ítems de inventario
        foreach (($data['items'] ?? []) as $item) {
            if (!empty($item['id'])) {
                PropertyHandoverItem::where('id', $item['id'])
                    ->where('property_handover_id', $acta->id)
                    ->update([
                        'estado'      => $item['estado'] ?? 'bueno',
                        'descripcion' => $item['descripcion'] ?? null,
                    ]);
            }
        }

        // Si ambas firmas → estado firmada
        $fresh = $acta->fresh();
        if ($fresh->firma_digital_asesor && $fresh->firma_digital_arrendatario) {
            $acta->update([
                'estado'                       => 'firmada',
                'fecha_firma'                  => now()->toDateString(),
                'acta_completada_asesor_at'    => $acta->acta_completada_asesor_at ?? now(),
                'acta_completada_inquilino_at' => now(),
            ]);
        }

        return response()->json(['ok' => true, 'estado' => $acta->fresh()->estado]);
    }

    // ── Guardar firma individual ──────────────────────────
    public function guardarFirma(Request $request, string $token)
    {
        $acta = PropertyHandover::where('acta_token', $token)->firstOrFail();

        $data = $request->validate([
            'rol'   => 'required|in:asesor,arrendatario',
            'firma' => 'required|string',
        ]);

        $campo = $data['rol'] === 'asesor' ? 'firma_digital_asesor' : 'firma_digital_arrendatario';
        $acta->update([
            $campo => $data['firma'],
            'acta_completada_' . $data['rol'] . '_at' => now(),
        ]);

        // Si ambas firmas → firmada
        $fresh = $acta->fresh();
        if ($fresh->firma_digital_asesor && $fresh->firma_digital_arrendatario) {
            $acta->update(['estado' => 'firmada', 'fecha_firma' => now()->toDateString()]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Subir foto de evidencia desde móvil ──────────────
    public function subirFoto(Request $request, string $token)
    {
        $acta = PropertyHandover::where('acta_token', $token)->firstOrFail();

        if ($acta->estado === 'cerrada') {
            return response()->json(['ok' => false, 'error' => 'Acta cerrada'], 403);
        }

        $request->validate([
            'foto'    => 'required|image|max:10240',
            'item_id' => 'required|integer',
        ]);

        $path = $request->file('foto')->store('actas/fotos', 'public');

        PropertyHandoverItem::where('id', $request->item_id)
            ->where('property_handover_id', $acta->id)
            ->update(['foto_path' => $path]);

        return response()->json([
            'ok'   => true,
            'path' => Storage::url($path),
        ]);
    }
}
