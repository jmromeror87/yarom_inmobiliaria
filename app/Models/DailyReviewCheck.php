<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReviewCheck extends Model
{
    protected $fillable = [
        'fecha', 'tipo', 'third_id', 'entry_id', 'datos', 'revisado', 'revisado_por_id', 'revisado_en',
    ];

    protected $casts = [
        'fecha'       => 'date',
        'datos'       => 'array',
        'revisado'    => 'boolean',
        'revisado_en' => 'datetime',
    ];

    public function third(): BelongsTo         { return $this->belongsTo(Third::class); }
    public function entry(): BelongsTo         { return $this->belongsTo(AccountingEntry::class, 'entry_id'); }
    public function revisadoPor(): BelongsTo   { return $this->belongsTo(User::class, 'revisado_por_id'); }

    public static function marcar(string $tipo, int $thirdId, int $usuarioId): self
    {
        return static::updateOrCreate(
            ['fecha' => now()->toDateString(), 'tipo' => $tipo, 'third_id' => $thirdId],
            ['revisado' => true, 'revisado_por_id' => $usuarioId, 'revisado_en' => now()]
        );
    }

    public static function desmarcar(string $tipo, int $thirdId): void
    {
        static::where('fecha', now()->toDateString())
            ->where('tipo', $tipo)
            ->where('third_id', $thirdId)
            ->delete();
    }
}
