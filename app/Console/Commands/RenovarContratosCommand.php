<?php

namespace App\Console\Commands;

use App\Jobs\RenovarContratosJob;
use Illuminate\Console\Command;

class RenovarContratosCommand extends Command
{
    protected $signature   = 'contratos:renovar';
    protected $description = 'Renueva contratos vencidos con incremento IPC o porcentaje fijo';

    public function handle(): void
    {
        $this->info('Procesando renovaciones automáticas...');
        RenovarContratosJob::dispatchSync();
        $this->info('✅ Listo.');
    }
}
