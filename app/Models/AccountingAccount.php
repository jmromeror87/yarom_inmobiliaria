<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingAccount extends Model
{
    protected $table = 'accounting_accounts';

    protected $fillable = [
        'codigo','nombre','nivel','parent_id','clase',
        'naturaleza','acepta_movimiento','requiere_tercero',
        'requiere_centro_costo','estado',
    ];

    protected $casts = [
        'acepta_movimiento'    => 'boolean',
        'requiere_tercero'     => 'boolean',
        'requiere_centro_costo'=> 'boolean',
    ];

    public function parent(): BelongsTo  { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany  { return $this->hasMany(self::class, 'parent_id'); }
    public function lines(): HasMany     { return $this->hasMany(AccountingEntryLine::class, 'account_id'); }

    public function getClaseLabelAttribute(): string
    {
        return match($this->clase) {
            '1' => 'Activo',
            '2' => 'Pasivo',
            '3' => 'Patrimonio',
            '4' => 'Ingreso',
            '5' => 'Gasto',
            '6' => 'Costo de producción',
            '7' => 'Costo de ventas',
            '8' => 'Cuentas de orden deudoras',
            '9' => 'Cuentas de orden acreedoras',
            default => $this->clase,
        };
    }

    public function getNivelLabelAttribute(): string
    {
        return match($this->nivel) {
            1 => 'Clase',
            2 => 'Grupo',
            3 => 'Cuenta',
            4 => 'Subcuenta',
            default => "Nivel {$this->nivel}",
        };
    }

    // Saldo de la cuenta en un período dado
    public function saldo(?int $periodoId = null): float
    {
        $query = $this->lines();
        if ($periodoId) {
            $query->whereHas('entry', fn($q) => $q->where('period_id', $periodoId)->where('estado', 'contabilizado'));
        } else {
            $query->whereHas('entry', fn($q) => $q->where('estado', 'contabilizado'));
        }
        $debitos  = (float) $query->sum('debito');
        $creditos = (float) $query->sum('credito');
        return $this->naturaleza === 'debito' ? ($debitos - $creditos) : ($creditos - $debitos);
    }
}
