<?php

namespace App\Console\Commands;

use App\Models\RentalContract;
use Illuminate\Console\Command;

class CorregirVigenciaContratosVencidos extends Command
{
    protected $signature = 'contratos:corregir-vigencia-vencida {--dry-run}';

    protected $description = 'Extiende la fecha_fin de contratos marcados como activos cuya vigencia ya pasó, sin tocar el canon (que ya está vigente según la facturación actual) — corrige el bug de RenovarContratosJob que nunca los renovó por un valor de tipo_incremento mal comparado';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $contratos = RentalContract::where('estado', 'activo')
            ->where('fecha_fin', '<', now())
            ->with('property.businessOrigin')
            ->get();

        $this->info("Contratos con vigencia vencida: {$contratos->count()}");

        foreach ($contratos as $c) {
            $duracion = (int) ($c->duracion_meses ?? 12);
            $nuevaInicio = now()->startOfDay();
            $nuevaFin = $nuevaInicio->copy()->addMonths($duracion)->subDay();

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "{$c->numero_contrato} ({$c->property?->businessOrigin?->nombre}) — fecha_fin {$c->fecha_fin->format('Y-m-d')} -> {$nuevaFin->format('Y-m-d')} (canon sin cambios: \${$c->canon_mensual})");

            if (!$dryRun) {
                $c->update(['fecha_fin' => $nuevaFin->toDateString()]);
            }
        }

        return self::SUCCESS;
    }
}
