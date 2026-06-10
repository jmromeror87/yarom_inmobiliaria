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
| Archivo: Property.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
    
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Property extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['codigo', 'direccion', 'estado', 'canon_arriendo', 'propietario_id', 'is_active'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $e) => match($e) {
                'created' => 'Inmueble creado',
                'updated' => 'Inmueble actualizado',
                'deleted' => 'Inmueble eliminado',
                default   => $e,
            });
    }
    protected $table = 'properties';
    protected $fillable = [
        'codigo','property_type_id','destinacion','propietario_id',
        'direccion','barrio','conjunto_edificio','apto_casa_oficina',
        'municipio_id','departamento_id','latitud','longitud',
        'estrato','area_construida_m2','area_privada_m2','area_total_m2',
        'habitaciones','banos','garajes','depositos','piso','total_pisos','anio_construccion',
        'tiene_ascensor','tiene_piscina','tiene_gym','tiene_salon_comunal',
        'tiene_vigilancia','permite_mascotas','amoblado',
        'canon_arriendo','cuota_administracion','precio_venta',
        'avaluo_catastral','avaluo_comercial','anio_avaluo',
        'disponible_arriendo','disponible_venta','estado',
        'doc_escritura','doc_certificado_libertad','doc_certificado_libertad_fecha',
        'ctl_tiene_limitacion','ctl_tipo_limitacion','ctl_observacion_limitacion',
        'doc_predial','doc_paz_salvo_admin','doc_documento_propietario',
        'doc_recibo_servicios','doc_recibo_tipo','doc_recibo_periodo',
        'doc_escritura_path','doc_certificado_libertad_path','doc_predial_path',
        'doc_paz_salvo_admin_path','doc_propietario_path','doc_recibo_servicios_path',
        'fecha_captacion','fecha_disponible','descripcion_publica','notas_internas',
        'coeficiente_copropiedad','escritura_ph_numero','porcentaje_propiedad',
        'servicios_publicos','asesor_id','is_active',
    ];

    protected $casts = [
        'tiene_ascensor'       => 'boolean',
        'tiene_piscina'        => 'boolean',
        'tiene_gym'            => 'boolean',
        'tiene_salon_comunal'  => 'boolean',
        'tiene_vigilancia'     => 'boolean',
        'permite_mascotas'     => 'boolean',
        'amoblado'             => 'boolean',
        'disponible_arriendo'  => 'boolean',
        'disponible_venta'     => 'boolean',
        'doc_escritura'        => 'boolean',
        'doc_certificado_libertad'  => 'boolean',
        'ctl_tiene_limitacion'      => 'boolean',
        'doc_predial'          => 'boolean',
        'doc_paz_salvo_admin'  => 'boolean',
        'doc_documento_propietario' => 'boolean',
        'doc_recibo_servicios' => 'boolean',
        'is_active'            => 'boolean',
        'fecha_captacion'      => 'date',
        'fecha_disponible'     => 'date',
        'doc_certificado_libertad_fecha' => 'date',
        'canon_arriendo'        => 'decimal:2',
        'cuota_administracion'  => 'decimal:2',
        'precio_venta'          => 'decimal:2',
        'avaluo_catastral'      => 'decimal:2',
        'avaluo_comercial'      => 'decimal:2',
        'area_construida_m2'    => 'decimal:2',
        'area_privada_m2'       => 'decimal:2',
        'area_total_m2'         => 'decimal:2',
        'coeficiente_copropiedad' => 'decimal:4',
        'porcentaje_propiedad'  => 'decimal:4',
    ];

    protected static function booted(): void
    {
        static::creating(function (Property $p) {
            if (empty($p->codigo)) {
                // Uso de lockForUpdate para evitar race condition en creación simultánea
                $year  = now()->year;
                $count = \DB::table('properties')
                    ->whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->count() + 1;
                $p->codigo = 'INM-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function (Property $p) {
            // Solo aplicar auto-estado si el estado está cambiando desde el formulario
            // No interferir cuando el cambio viene desde contratos (administración o arriendo)
            if (!$p->isDirty('estado')) return;

            // CTL bloqueado — siempre aplica
            if ($p->ctl_tiene_limitacion && !in_array($p->estado, ['arrendado', 'vendido'])) {
                $p->estado = 'documentos_pendientes';
                return;
            }

            // Si viene de un contrato de administración activado → respetar 'disponible'
            // El contrato ya validó documentos en su propio flujo
            if ($p->estado === 'disponible' && $p->getOriginal('estado') !== 'disponible') {
                return; // Respetar el cambio externo
            }
        });
    }

    public function tipo(): BelongsTo       { return $this->belongsTo(PropertyType::class, 'property_type_id'); }
    public function propietario(): BelongsTo { return $this->belongsTo(Third::class, 'propietario_id'); }
    public function municipio(): BelongsTo  { return $this->belongsTo(Municipio::class); }
    public function departamento(): BelongsTo { return $this->belongsTo(Departamento::class); }
    public function images() { return $this->hasMany(PropertyImage::class)->orderBy('orden'); }
    public function administrationContracts() { return $this->hasMany(\App\Models\AdministrationContract::class); }
    public function rentalContracts() { return $this->hasMany(\App\Models\RentalContract::class); }
    public function portada() { return $this->hasOne(PropertyImage::class)->where('es_portada', true); }
    public function documents() { return $this->hasMany(PropertyDocument::class); }
    public function asesor(): BelongsTo { return $this->belongsTo(User::class, 'asesor_id'); }

    // Documentos obligatorios para avanzar a contrato
    private function docsObligatorios(): array
    {
        return [
            'CTL'           => $this->doc_certificado_libertad,
            'Cédula prop.'  => $this->doc_documento_propietario,
            'Recibo serv.'  => $this->doc_recibo_servicios,
        ];
    }

    // Documentos deseables (no bloquean pero suman al porcentaje)
    private function docsDeseables(): array
    {
        return [
            'Escritura'     => $this->doc_escritura,
            'Predial'       => $this->doc_predial,
            'Paz salvo adm.'=> $this->doc_paz_salvo_admin,
        ];
    }

    public function getDocumentosCompletosAttribute(): bool
    {
        return !in_array(false, $this->docsObligatorios(), true)
            && !$this->ctl_tiene_limitacion;
    }

    public function getPorcentajeDocumentosAttribute(): int
    {
        $todos = array_merge($this->docsObligatorios(), $this->docsDeseables());
        return (int) round((array_sum($todos) / count($todos)) * 100);
    }

    public function getDocumentosFaltantesAttribute(): array
    {
        return array_keys(array_filter($this->docsObligatorios(), fn ($v) => !$v));
    }

    public function scopeDisponibles($q)  { return $q->where('estado', 'disponible'); }
    public function scopeArrendados($q)   { return $q->where('estado', 'arrendado'); }
    public function scopeEnCaptacion($q)  { return $q->where('estado', 'en_captacion'); }

    public function services()
    {
        return $this->hasMany(\App\Models\PropertyService::class)->orderByDesc('fecha_servicio');
    }
}
