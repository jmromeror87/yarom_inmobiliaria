<?php

namespace App\Jobs\Concerns;

use App\Models\JobExecution;

trait LogsExecution
{
    private ?JobExecution $__ejecucion = null;

    protected function iniciarLog(string $nombre, string $disparadoPor = 'scheduler'): void
    {
        $this->__ejecucion = JobExecution::create([
            'job_name'     => $nombre,
            'job_class'    => static::class,
            'disparado_por' => $disparadoPor,
            'estado'       => 'ejecutando',
            'started_at'   => now(),
        ]);
    }

    protected function finalizarLog(int $registros = 0, array $detalles = []): void
    {
        $this->__ejecucion?->update([
            'estado'               => 'completado',
            'finished_at'          => now(),
            'registros_procesados' => $registros,
            'detalles'             => $detalles,
        ]);
    }

    protected function falloLog(string $error): void
    {
        $this->__ejecucion?->update([
            'estado'      => 'fallido',
            'finished_at' => now(),
            'errores'     => $error,
        ]);
    }

    // Laravel llama este método automáticamente cuando el job lanza una excepción no capturada
    public function failed(\Throwable $e): void
    {
        $this->__ejecucion?->update([
            'estado'      => 'fallido',
            'finished_at' => now(),
            'errores'     => get_class($e) . ': ' . $e->getMessage(),
        ]);
    }
}
