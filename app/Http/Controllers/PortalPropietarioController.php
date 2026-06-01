<?php

namespace App\Http\Controllers;

use App\Models\Third;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class PortalPropietarioController extends Controller
{
    public function show(string $token)
    {
        $propietario = Third::where('portal_token', $token)
            ->where('portal_activo', true)
            ->where('es_propietario', true)
            ->firstOrFail();

        $company = Company::first();

        // Inmuebles del propietario con contratos activos
        $propiedades = $propietario->properties()
            ->with([
                'rentalContracts' => fn ($q) => $q->where('estado', 'activo')->with('arrendatario'),
                'administrationContracts' => fn ($q) => $q->where('estado', 'activo'),
            ])
            ->where('is_active', true)
            ->get();

        // Últimas liquidaciones (máx 12)
        $liquidaciones = $propietario->ownerLiquidations()
            ->with('property')
            ->orderByDesc('periodo_inicio')
            ->limit(12)
            ->get();

        // Resumen financiero
        $totalGirado   = $liquidaciones->where('estado', 'girada')->sum('total_giro');
        $pendientePago = $liquidaciones->whereIn('estado', ['aprobada', 'pendiente'])->sum('total_giro');

        return view('portal.propietario', compact(
            'propietario',
            'company',
            'propiedades',
            'liquidaciones',
            'totalGirado',
            'pendientePago',
        ));
    }

    public function regenerar(Third $third): RedirectResponse
    {
        abort_unless($third->es_propietario, 403);
        $third->generarPortalToken();
        return back()->with('success', 'Link del portal generado correctamente.');
    }

    public function revocar(Third $third): RedirectResponse
    {
        abort_unless($third->es_propietario, 403);
        $third->revocarPortalToken();
        return back()->with('success', 'Acceso al portal revocado.');
    }
}
