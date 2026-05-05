<?php
/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: RequestDocument.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestDocument extends Model
{
    protected $table = 'request_documents';
    protected $fillable = [
        'request_id','request_third_id','tipo_documento',
        'nombre_original','path','extension','tamanio_bytes',
        'estado_documento','notas','subido_por',
    ];

    public function request(): BelongsTo      { return $this->belongsTo(Request::class); }
    public function requestThird(): BelongsTo { return $this->belongsTo(RequestThird::class, 'request_third_id'); }
    public function subidoPor(): BelongsTo    { return $this->belongsTo(User::class, 'subido_por'); }

    public function getUrlAttribute(): string { return asset('storage/' . $this->path); }
    public function getEsImagenAttribute(): bool
    {
        return in_array(strtolower($this->extension), ['jpg','jpeg','png','webp']);
    }
}
