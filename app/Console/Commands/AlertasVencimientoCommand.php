<?php

namespace App\Console\Commands;

use App\Jobs\AlertasVencimientoContratosJob;
use Illuminate\Console\Command;

class AlertasVencimientoCommand extends Command
{
    protected $signature   = 'contratos:alertas-vencimiento';
    protected $description = 'Envía WhatsApp de alertas a contratos que vencen en 60/30/15/5 días';

    public function handle(): void
    {
        $this->info('Enviando alertas de vencimiento...');
        AlertasVencimientoContratosJob::dispatchSync();
        $this->info('✅ Listo.');
    }
}
