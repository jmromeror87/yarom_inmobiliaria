<?php

namespace App\Console\Commands;

use App\Models\PropertyHandover;
use App\Models\RentalContract;
use Illuminate\Console\Command;

class BackfillFechaEntregaEfectiva extends Command
{
    protected $signature = 'contratos:backfill-fecha-entrega';

    protected $description = 'Rellena fecha_entrega_efectiva en contratos ya activos, usando el acta de entrega si existe o la fecha de inicio del contrato';

    public function handle(): int
    {
        $contratos = RentalContract::where('estado', 'activo')
            ->whereNull('fecha_entrega_efectiva')
            ->get();

        $porActa = 0;
        $porInicio = 0;

        foreach ($contratos as $contrato) {
            $handover = PropertyHandover::where('rental_contract_id', $contrato->id)
                ->where('tipo', 'entrega')
                ->where('estado', 'cerrada')
                ->orderByDesc('fecha_firma')
                ->first();

            if ($handover) {
                $fecha = $handover->fecha_firma ?? $handover->fecha_acta;
                $porActa++;
            } else {
                $fecha = $contrato->fecha_inicio ?? $contrato->fecha_contrato ?? $contrato->created_at;
                $porInicio++;
            }

            $contrato->fecha_entrega_efectiva = $fecha;
            $contrato->saveQuietly();
        }

        $this->info("Actualizados: {$contratos->count()} (por acta: {$porActa}, por fecha_inicio: {$porInicio})");

        return self::SUCCESS;
    }
}
