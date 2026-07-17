<?php

namespace App\Console\Commands;

use App\Models\RentalContract;
use App\Models\RentBill;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Corrige el día de pago real de cada contrato de arriendo de Victoria
 * (tomado de "LISTADO PRO- ARRE (VICTORIA).xlsx", suministrado por el
 * cliente) y recalcula fecha_limite_pago en las facturas del mes actual
 * ya generadas con el día global incorrecto.
 *
 * Uso: php artisan victoria:importar-dia-pago [--dry-run]
 */
class ImportarDiaPagoVictoria extends Command
{
    protected $signature = 'victoria:importar-dia-pago {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    protected $description = 'Aplica el día de pago real por contrato para los arrendatarios de Victoria';

    private function normalizar(string $s): string
    {
        $s = mb_strtoupper(trim($s), 'UTF-8');
        $s = str_replace(['Á','É','Í','Ó','Ú','Ñ'], ['A','E','I','O','U','N'], $s);
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $ruta   = database_path('data/victoria_dia_pago.json');

        if (! file_exists($ruta)) {
            $this->error("No se encontró {$ruta}");
            return self::FAILURE;
        }

        $filas = json_decode(file_get_contents($ruta), true);
        $this->info('Registros a procesar: ' . count($filas));

        $contratosActivos = RentalContract::whereHas('property.businessOrigin', fn ($q) => $q->where('nombre', 'Victoria'))
            ->where('estado', 'activo')
            ->with('arrendatario')
            ->get();

        $actualizados = 0;
        $noEncontrados = [];

        foreach ($filas as $fila) {
            $clave = $this->normalizar($fila['arrendatario']);

            // Coincidencia exacta primero; si no, por prefijo (el Excel a veces
            // trae el nombre recortado, sin segundo apellido, respecto al tercero).
            $contrato = $contratosActivos->first(fn ($c) => $this->normalizar($c->arrendatario?->nombre_completo ?? '') === $clave)
                ?? $contratosActivos->first(function ($c) use ($clave) {
                    $nombreDb = $this->normalizar($c->arrendatario?->nombre_completo ?? '');
                    return $nombreDb !== '' && (Str::startsWith($nombreDb, $clave) || Str::startsWith($clave, $nombreDb));
                });

            if (! $contrato) {
                $noEncontrados[] = $fila['arrendatario'];
                continue;
            }

            $this->line(($dryRun ? '[DRY-RUN] ' : '') . "  {$contrato->numero_contrato} ({$fila['arrendatario']}): dia_pago {$contrato->dia_pago} -> {$fila['dia_pago']}");

            if (! $dryRun) {
                $contrato->update(['dia_pago' => $fila['dia_pago']]);

                // Recalcular fecha_limite_pago en facturas del período actual que
                // aún no estén pagadas (no tiene sentido mover la fecha de una
                // factura ya saldada).
                $bills = RentBill::where('rental_contract_id', $contrato->id)
                    ->where('estado', '!=', 'pagada')
                    ->get();

                foreach ($bills as $bill) {
                    $periodoBase = \Carbon\Carbon::create($bill->anio, $bill->mes, 1);
                    $dia = min($fila['dia_pago'], $periodoBase->copy()->endOfMonth()->day);
                    $nuevaFecha = $periodoBase->copy()->startOfMonth()->addDays($dia - 1)->toDateString();

                    if ($bill->fecha_limite_pago?->toDateString() !== $nuevaFecha) {
                        $bill->update(['fecha_limite_pago' => $nuevaFecha]);
                        $this->line("      -> {$bill->numero}: fecha_limite_pago = {$nuevaFecha}");
                    }
                }
            }

            $actualizados++;
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY-RUN] ' : '') . "Contratos actualizados: {$actualizados}");
        if ($noEncontrados) {
            $this->warn('No se encontró contrato activo para: ' . implode(', ', $noEncontrados));
        }

        return self::SUCCESS;
    }
}
