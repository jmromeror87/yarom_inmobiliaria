<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingPeriod extends Model
{
    protected $table = 'accounting_periods';

    protected $fillable = [
        'anio','mes','estado','cerrado_por','cerrado_en','notas',
    ];

    protected $casts = [
        'anio'       => 'integer',
        'mes'        => 'integer',
        'cerrado_en' => 'datetime',
    ];

    public function cerradoPor(): BelongsTo { return $this->belongsTo(User::class, 'cerrado_por'); }
    public function entries(): HasMany      { return $this->hasMany(AccountingEntry::class, 'period_id'); }

    private const MESES = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    public function getNombreAttribute(): string
    {
        return self::MESES[$this->mes] . ' ' . $this->anio;
    }

    public function getMesNombreAttribute(): string
    {
        return self::MESES[$this->mes] ?? (string)$this->mes;
    }

    public function getEstaAbiertAttribute(): bool { return $this->estado === 'abierto'; }

    public static function actual(): ?self
    {
        return static::where('anio', now()->year)
            ->where('mes', now()->month)
            ->where('estado', 'abierto')
            ->first();
    }

    public static function abrirSiNoExiste(int $anio, int $mes): self
    {
        return static::firstOrCreate(
            ['anio' => $anio, 'mes' => $mes],
            ['estado' => 'abierto']
        );
    }
}
