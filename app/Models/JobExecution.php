<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobExecution extends Model
{
    protected $fillable = [
        'job_name',
        'job_class',
        'disparado_por',
        'estado',
        'started_at',
        'finished_at',
        'registros_procesados',
        'detalles',
        'errores',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'detalles'    => 'array',
    ];

    public function duracionSegundos(): ?int
    {
        if (!$this->finished_at) return null;
        return (int) $this->started_at->diffInSeconds($this->finished_at);
    }

    public function duracionLabel(): string
    {
        $seg = $this->duracionSegundos();
        if ($seg === null) return '—';
        if ($seg < 60) return "{$seg}s";
        return round($seg / 60, 1) . 'min';
    }

    public static function ultimoPorJob(string $jobClass): ?self
    {
        return static::where('job_class', $jobClass)
            ->latest('started_at')
            ->first();
    }
}
