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
| Archivo: Third.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
            

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Third extends Model
{
    use SoftDeletes;

    protected $table = 'thirds';

    protected $fillable = [
        'es_propietario','es_arrendatario','es_cliente_compra','es_fiador','es_proveedor',
        'tipo_persona','tipo_documento','numero_documento','digito_verificacion',
        'primer_nombre','segundo_nombre','primer_apellido','segundo_apellido',
        'razon_social','nombre_comercial','nombre_completo',
        'genero','estado_civil','fecha_nacimiento','lugar_nacimiento','nacionalidad',
        'email','email_alt','telefono_fijo','celular','celular_alt','whatsapp',
        'direccion_residencia','barrio_residencia','municipio_id','departamento_id','pais_id','codigo_postal',
        'tipo_empleo','empresa_donde_trabaja','cargo','telefono_empresa','direccion_empresa',
        'meses_empleo_actual','ingresos_mensuales','otros_ingresos','descripcion_otros_ingresos',
        'banco','tipo_cuenta','numero_cuenta','titular_cuenta',
        'estado_crediticio','fecha_evaluacion_crediticia','score_crediticio',
        'reporte_negativo','notas_evaluacion',
        'tipo_garantia','aseguradora','numero_poliza',
        'comision_pactada','referencias_personales','documentos_adjuntos',
        'fuente_captacion','asesor_id','ultimo_contacto','notas_crm',
        'notas','is_active',
    ];

    protected $casts = [
        'es_propietario'             => 'boolean',
        'es_arrendatario'            => 'boolean',
        'es_cliente_compra'          => 'boolean',
        'es_fiador'                  => 'boolean',
        'es_proveedor'               => 'boolean',
        'reporte_negativo'           => 'boolean',
        'is_active'                  => 'boolean',
        'fecha_nacimiento'           => 'date',
        'fecha_evaluacion_crediticia'=> 'date',
        'ultimo_contacto'            => 'datetime',
        'ingresos_mensuales'         => 'decimal:2',
        'otros_ingresos'             => 'decimal:2',
        'comision_pactada'           => 'decimal:2',
        'referencias_personales'     => 'array',
        'documentos_adjuntos'        => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (Third $t) {
            $t->nombre_completo = $t->tipo_persona === 'juridica'
                ? $t->razon_social
                : trim("{$t->primer_nombre} {$t->segundo_nombre} {$t->primer_apellido} {$t->segundo_apellido}");
        });
    }

    public function municipio(): BelongsTo    { return $this->belongsTo(Municipio::class); }
    public function departamento(): BelongsTo { return $this->belongsTo(Departamento::class); }
    public function pais(): BelongsTo         { return $this->belongsTo(Pais::class); }
    public function asesor(): BelongsTo       { return $this->belongsTo(User::class, 'asesor_id'); }

    public function scopePropietarios($q)  { return $q->where('es_propietario', true); }
    public function scopeArrendatarios($q) { return $q->where('es_arrendatario', true); }
    public function scopeClientes($q)      { return $q->where('es_cliente_compra', true); }

    public function getRolesAttribute(): string
    {
        $roles = [];
        if ($this->es_propietario)    $roles[] = 'Propietario';
        if ($this->es_arrendatario)   $roles[] = 'Arrendatario';
        if ($this->es_cliente_compra) $roles[] = 'Cliente compra';
        if ($this->es_fiador)         $roles[] = 'Fiador/Codeudor';
        if ($this->es_proveedor)      $roles[] = 'Proveedor';
        return implode(' · ', $roles) ?: 'Sin rol';
    }

    public function getRatioIngresoCanonAttribute(): float
    {
        return 0;
    }
}
