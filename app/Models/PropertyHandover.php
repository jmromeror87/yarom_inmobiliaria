<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class PropertyHandover extends Model
{
    use SoftDeletes;

    protected $table = 'property_handovers';

    protected $fillable = [
        'numero','rental_contract_id','property_id','arrendatario_id','asesor_id',
        'tipo','fecha_acta','hora_acta','lugar_acta',
        'lectura_agua','lectura_energia','lectura_gas',
        'llaves_entregadas','llaves_control_acceso','llaves_parqueadero','llaves_deposito','notas_llaves',
        'estado_general','observaciones_generales',
        'firmado_arrendatario','firmado_asesor','fecha_firma',
        'path_acta_firmada','estado','firma_digital_arrendatario','firma_digital_asesor','whatsapp_enviado','fecha_whatsapp_enviado',
    ];

    protected $casts = [
        'fecha_acta'  => 'date',
        'fecha_firma' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function ($h) {
            if (empty($h->numero)) {
                $year  = now()->year;
                $tipo  = $h->tipo === 'devolucion' ? 'DEV' : 'ACT';
                $ultimo = static::whereYear('created_at', $year)->max('numero');
                $count  = $ultimo ? ((int)substr($ultimo, -4)) + 1 : 1;
                $h->numero = $tipo . '-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::created(function ($h) {
            PropertyHandoverHistory::create([
                'property_handover_id' => $h->id,
                'changed_by'   => \Illuminate\Support\Facades\Auth::id(),
                'estado_anterior' => null,
                'estado_nuevo'    => $h->estado,
                'canal'           => 'sistema',
                'razon_cambio'    => 'Acta creada',
                'ip_address'      => request()?->ip(),
                'cambiado_en'     => now(),
            ]);
        });

        static::updating(function ($h) {
            if ($h->isDirty('estado')) {
                PropertyHandoverHistory::create([
                    'property_handover_id' => $h->id,
                    'changed_by'      => \Illuminate\Support\Facades\Auth::id(),
                    'estado_anterior' => $h->getOriginal('estado'),
                    'estado_nuevo'    => $h->estado,
                    'canal'           => 'sistema',
                    'ip_address'      => request()?->ip(),
                    'cambiado_en'     => now(),
                ]);
            }
            if ($h->isDirty('estado') && $h->estado === 'cerrada' && $h->tipo === 'entrega') {
                RentalContract::find($h->rental_contract_id)?->update(['estado' => 'activo']);
            }
        });
    }

    public function history(): HasMany { return $this->hasMany(PropertyHandoverHistory::class)->orderByDesc('cambiado_en'); }

    public function rentalContract(): BelongsTo { return $this->belongsTo(RentalContract::class); }
    public function property(): BelongsTo       { return $this->belongsTo(Property::class); }
    public function arrendatario(): BelongsTo   { return $this->belongsTo(Third::class, 'arrendatario_id'); }
    public function asesor(): BelongsTo         { return $this->belongsTo(User::class, 'asesor_id'); }
    public function items(): HasMany            { return $this->hasMany(PropertyHandoverItem::class)->orderBy('orden'); }

    public function getAmbientesAttribute()
    {
        return $this->items->groupBy('ambiente');
    }
}
