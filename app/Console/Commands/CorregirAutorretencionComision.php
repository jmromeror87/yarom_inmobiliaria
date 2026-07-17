<?php

namespace App\Console\Commands;

use App\Models\AccountingAccount;
use App\Models\AccountingEntryLine;
use Illuminate\Console\Command;

/**
 * Corrige un error contable: la línea "Autorretención renta — ajuste ingreso"
 * se estaba acreditando por segunda vez a la cuenta de ingresos (41551001
 * Comisiones) en vez de a la cuenta de pasivo 236525 "Autorretención a
 * título de renta" (lo que realmente se le debe a la DIAN). El total del
 * comprobante no cambia (sigue cuadrado), solo se corrige a qué cuenta
 * pertenece ese crédito.
 *
 * Uso: php artisan contabilidad:corregir-autorretencion [--dry-run]
 */
class CorregirAutorretencionComision extends Command
{
    protected $signature = 'contabilidad:corregir-autorretencion {--dry-run : Solo mostrar los cambios sin aplicarlos}';

    protected $description = 'Reasigna las líneas de autorretención mal imputadas a Comisiones (41551001) hacia el pasivo 236525';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $cuentaComision = AccountingAccount::where('codigo', '41551001')->value('id');
        $cuentaPasivo    = AccountingAccount::where('codigo', '236525')->value('id');

        if (!$cuentaComision || !$cuentaPasivo) {
            $this->error('No se encontraron las cuentas 41551001 y/o 236525.');
            return self::FAILURE;
        }

        $lineas = AccountingEntryLine::where('descripcion', 'Autorretención renta — ajuste ingreso')
            ->where('account_id', $cuentaComision)
            ->get();

        $this->info('Líneas a corregir: ' . $lineas->count() . ' (total $' . number_format($lineas->sum('credito'), 2, ',', '.') . ')');

        if ($lineas->isEmpty()) {
            return self::SUCCESS;
        }

        if ($dryRun) {
            foreach ($lineas as $l) {
                $this->line("  [DRY-RUN] línea {$l->id} (asiento {$l->entry_id}): 41551001 -> 236525, crédito \${$l->credito}");
            }
            return self::SUCCESS;
        }

        $entriesAfectados = $lineas->pluck('entry_id')->unique();

        foreach ($lineas as $l) {
            $l->update([
                'account_id'  => $cuentaPasivo,
                'descripcion' => 'Autorretención a título de renta por pagar',
            ]);
        }

        foreach ($entriesAfectados as $entryId) {
            \App\Models\AccountingEntry::find($entryId)?->recalcularTotales();
        }

        $this->info('Corregidas: ' . $lineas->count() . ' líneas en ' . $entriesAfectados->count() . ' asientos.');

        return self::SUCCESS;
    }
}
