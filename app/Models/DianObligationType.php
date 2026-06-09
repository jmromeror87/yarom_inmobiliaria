<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DianObligationType extends Model
{
    protected $table = 'dian_obligation_types';

    protected $fillable = [
        'codigo','nombre','formulario','periodicidad','descripcion','activa','orden',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'orden'  => 'integer',
    ];

    public function declarations(): HasMany
    {
        return $this->hasMany(DianDeclaration::class, 'obligation_type_id');
    }

    public function getPeriodicidadLabelAttribute(): string
    {
        return match($this->periodicidad) {
            'mensual'        => 'Mensual',
            'bimestral'      => 'Bimestral',
            'cuatrimestral'  => 'Cuatrimestral',
            'anual'          => 'Anual',
            default          => $this->periodicidad,
        };
    }
}
