<?php
namespace App\Console\Commands;

use App\Jobs\VerificarMoraJob;
use Illuminate\Console\Command;

class VerificarMoraCommand extends Command
{
    protected $signature   = 'mora:verificar {--sin-whatsapp}';
    protected $description = 'Verifica y actualiza mora en facturas vencidas, envía avisos por WhatsApp';

    public function handle(): int
    {
        $this->info('Verificando mora en facturas vencidas...');
        (new VerificarMoraJob(enviarWhatsapp: !$this->option('sin-whatsapp')))->handle();
        $this->info('Proceso completado. Revisa el log para el detalle.');
        return self::SUCCESS;
    }
}
