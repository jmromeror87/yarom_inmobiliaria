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
| Archivo: PropertyDocument.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDocument extends Model
{
    protected $table = 'property_documents';
    protected $fillable = [
        'property_id','tipo','nombre_original',
        'path','extension','tamanio_bytes','notas','subido_por','fecha_vencimiento',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
    ];

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }
    public function subidoPor(): BelongsTo { return $this->belongsTo(User::class, 'subido_por'); }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'escritura'              => 'Escritura pública',
            'certificado_libertad'   => 'Certificado de libertad',
            'predial'                => 'Predial',
            'paz_salvo_admin'        => 'Paz y salvo administración',
            'documento_propietario'  => 'Documento propietario',
            'recibo_servicios'       => 'Recibo servicios',
            default                  => 'Otro',
        };
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getEsImagenAttribute(): bool
    {
        return in_array(strtolower($this->extension), ['jpg','jpeg','png','webp']);
    }

    public function getEsPdfAttribute(): bool
    {
        return strtolower($this->extension) === 'pdf';
    }
}
