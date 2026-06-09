<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BumpVersion extends Command
{
    protected $signature   = 'app:version {version? : Nueva versión (ej: 1.2.0). Sin argumento muestra la actual.}';
    protected $description = 'Muestra o actualiza la versión del sistema en el archivo VERSION';

    public function handle(): int
    {
        $path    = base_path('VERSION');
        $current = file_exists($path) ? trim(file_get_contents($path)) : '0.0.0';
        $new     = $this->argument('version');

        if (! $new) {
            $this->line("Versión actual: <info>{$current}</info>");
            return self::SUCCESS;
        }

        if (! preg_match('/^\d+\.\d+\.\d+$/', $new)) {
            $this->error("Formato inválido. Usa semver: MAYOR.MENOR.PARCHE (ej: 1.2.0)");
            return self::FAILURE;
        }

        file_put_contents($path, $new . PHP_EOL);
        $this->line("Versión actualizada: <comment>{$current}</comment> → <info>{$new}</info>");

        return self::SUCCESS;
    }
}
