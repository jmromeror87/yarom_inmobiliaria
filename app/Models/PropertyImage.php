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
| Archivo: PropertyImage.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImage extends Model
{
    protected $table = 'property_images';
    protected $fillable = ['property_id','path','titulo','categoria','es_portada','orden'];
    protected $casts = ['es_portada' => 'boolean'];
    protected $attributes = ['orden' => 0];

    protected static function booted(): void
    {
        static::creating(function (PropertyImage $image) {
            if (is_null($image->orden)) {
                $image->orden = static::where('property_id', $image->property_id)->count();
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getCategoriaLabelAttribute(): string
    {
        $labels = [
            'fachada'    => '🏠 Fachada',
            'sala'       => '🛋️ Sala',
            'cocina'     => '🍳 Cocina',
            'habitacion' => '🛏️ Habitación',
            'bano'       => '🚿 Baño',
            'zona_comun' => '🏊 Zona común',
            'vista'      => '🌅 Vista',
            'plano'      => '📐 Plano',
            'otro'       => '📷 Otro',
        ];
        return $labels[$this->categoria] ?? '📷 Otro';
    }
}