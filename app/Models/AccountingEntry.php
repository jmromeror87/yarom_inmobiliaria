<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class AccountingEntry extends Model
{
    protected $table = 'accounting_entries';

    protected $fillable = [
        'tipo','numero','fecha','descripcion','period_id',
        'third_id','cost_center_id','referencia','referencia_tipo','referencia_id',
        'total_debitos','total_creditos','estado',
        'creado_por','contabilizado_por','contabilizado_en',
        'anulado_por','anulado_en','razon_anulacion',
    ];

    protected $casts = [
        'fecha'            => 'date',
        'contabilizado_en' => 'datetime',
        'anulado_en'       => 'datetime',
        'total_debitos'    => 'decimal:2',
        'total_creditos'   => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $e) {
            if (empty($e->numero)) {
                $e->numero = self::generarNumero($e->tipo);
            }
            if (empty($e->creado_por)) {
                $e->creado_por = Auth::id();
            }
        });

        static::saving(function (self $e) {
            $e->total_debitos  = $e->lines()->sum('debito');
            $e->total_creditos = $e->lines()->sum('credito');
        });
    }

    public static function generarNumero(string $tipo): string
    {
        $anio = now()->year;
        $ultimo = static::where('tipo', $tipo)
            ->whereYear('created_at', $anio)
            ->count() + 1;
        return $tipo . '-' . $anio . '-' . str_pad($ultimo, 4, '0', STR_PAD_LEFT);
    }

    public function period(): BelongsTo     { return $this->belongsTo(AccountingPeriod::class, 'period_id'); }
    public function third(): BelongsTo      { return $this->belongsTo(Third::class); }
    public function costCenter(): BelongsTo { return $this->belongsTo(AccountingCostCenter::class, 'cost_center_id'); }
    public function creadoPor(): BelongsTo  { return $this->belongsTo(User::class, 'creado_por'); }
    public function contabilizadoPor(): BelongsTo { return $this->belongsTo(User::class, 'contabilizado_por'); }
    public function anuladoPor(): BelongsTo { return $this->belongsTo(User::class, 'anulado_por'); }
    public function lines(): HasMany        { return $this->hasMany(AccountingEntryLine::class, 'entry_id')->orderBy('orden'); }

    public function getCuadradoAttribute(): bool
    {
        return abs($this->total_debitos - $this->total_creditos) < 0.01;
    }

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'CC' => 'Comp. Contabilidad',
            'CI' => 'Comp. Ingreso',
            'CE' => 'Comp. Egreso',
            'ND' => 'Nota Débito',
            'NC' => 'Nota Crédito',
            'CA' => 'Comp. Ajuste',
            default => $this->tipo,
        };
    }

    public function contabilizar(): void
    {
        if (!$this->cuadrado) {
            throw new \RuntimeException('El comprobante no está cuadrado (débitos ≠ créditos).');
        }
        $this->update([
            'estado'            => 'contabilizado',
            'contabilizado_por' => Auth::id(),
            'contabilizado_en'  => now(),
        ]);
    }

    public function anular(string $razon): void
    {
        $this->update([
            'estado'          => 'anulado',
            'anulado_por'     => Auth::id(),
            'anulado_en'      => now(),
            'razon_anulacion' => $razon,
        ]);
    }
}
