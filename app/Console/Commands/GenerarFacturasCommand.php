<?php
namespace App\Console\Commands;

use App\Jobs\GenerarFacturasMensuales;
use Illuminate\Console\Command;

class GenerarFacturasCommand extends Command
{
    protected $signature   = 'facturas:generar';
    protected $description = 'Genera facturas mensuales para todos los contratos activos';

    public function handle(): int
    {
        $this->info('Generando facturas mensuales...');
        GenerarFacturasMensuales::dispatchSync();
        $this->info('Proceso completado. Revisa el log para el detalle.');
        return self::SUCCESS;
    }
}
