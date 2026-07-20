<?php
namespace App\Console\Commands;

use App\Jobs\GenerarFacturasMensuales;
use Illuminate\Console\Command;

class GenerarFacturasCommand extends Command
{
    protected $signature   = 'facturas:generar {--mes=} {--anio=} {--origen=} {--sin-whatsapp}';
    protected $description = 'Genera facturas mensuales para todos los contratos activos';

    public function handle(): int
    {
        $this->info('Generando facturas mensuales...');

        $job = new GenerarFacturasMensuales(
            mesParam: $this->option('mes') ? (int) $this->option('mes') : null,
            anioParam: $this->option('anio') ? (int) $this->option('anio') : null,
            businessOriginId: $this->option('origen') ? (int) $this->option('origen') : null,
            enviarWhatsapp: !$this->option('sin-whatsapp'),
        );
        $job->handle();

        $this->info('Proceso completado. Revisa el log para el detalle.');
        return self::SUCCESS;
    }
}
