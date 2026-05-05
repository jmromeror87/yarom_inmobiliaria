<?php
/*
|----------------               ----------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: RequestSuraStudy.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestSuraStudy extends Model
{
    protected $table = 'request_sura_studies';
    protected $fillable = [
        'request_id','canal_envio','fecha_envio','enviado_por','mensaje_enviado',
        'contacto_sura','telefono_sura','email_sura',
        'numero_solicitud_sura','fecha_respuesta','resultado_sura',
        'analista_sura','observaciones_sura','path_respuesta','notas',
    ];
    protected $casts = [
        'fecha_envio'     => 'datetime',
        'fecha_respuesta' => 'datetime',
    ];

    public function request(): BelongsTo    { return $this->belongsTo(Request::class); }
    public function enviadoPor(): BelongsTo { return $this->belongsTo(User::class, 'enviado_por'); }
}
