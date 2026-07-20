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
        // El saving hook fue eliminado: calculaba sum() sobre líneas aún no guardadas,
        // dejando total_debitos/total_creditos = 0 en todos los comprobantes.
        // Los totales se pasan explícitamente en crearComprobante() o se recalculan
        // con recalcularTotales() después de guardar las líneas (form Filament).
    }

    public function recalcularTotales(): void
    {
        $this->updateQuietly([
            'total_debitos'  => $this->lines()->sum('debito'),
            'total_creditos' => $this->lines()->sum('credito'),
        ]);
    }

    public static function generarNumero(string $tipo): string
    {
        $anio = now()->year;
        // La migración del histórico Siinmob insertó miles de comprobantes con
        // created_at de HOY (no la fecha original de la nota), lo que infla el
        // conteo por año y produce números que ya existen entre esos registros
        // heredados. Se excluyen del conteo y, por seguridad adicional, se
        // verifica que el número candidato no exista ya (de ningún origen)
        // antes de asignarlo — lockForUpdate evita carreras entre inserciones
        // simultáneas del mismo tipo.
        $ultimo = static::where('tipo', $tipo)
            ->whereYear('created_at', $anio)
            ->where('referencia_tipo', '!=', 'historico_siinmob')
            ->lockForUpdate()
            ->count();

        do {
            $ultimo++;
            $candidato = $tipo . '-' . $anio . '-' . str_pad($ultimo, 4, '0', STR_PAD_LEFT);
        } while (static::where('numero', $candidato)->exists());

        return $candidato;
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
            'CR' => 'Comp. Recaudo',
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
