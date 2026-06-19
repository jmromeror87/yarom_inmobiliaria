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
| Archivo: Company.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
            

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'tipo_persona', 'razon_social', 'nombre_comercial',
        'nit', 'digito_verificacion', 'nit_completo',
        'matricula_mercantil', 'fecha_matricula', 'fecha_renovacion', 'camara_comercio',
        'codigo_ciiu', 'descripcion_ciiu',
        'tipo_contribuyente', 'regimen_fiscal',
        'responsable_iva', 'tarifa_iva',
        'gran_contribuyente', 'autorretenedor',
        'agente_retencion_fuente', 'agente_reteica', 'agente_reteiva',
        'tarifa_retefuente_servicios', 'tarifa_retefuente_honorarios',
        'tarifa_retefuente_arrendamiento', 'tarifa_reteica',
        'resolucion_facturacion', 'fecha_resolucion', 'fecha_vencimiento_resolucion',
        'prefijo_factura', 'consecutivo_desde', 'consecutivo_hasta',
        'consecutivo_actual', 'factura_electronica_activa',
        'fe_operador', 'fe_ambiente', 'fe_nota_pie',
        'rep_legal_nombre', 'rep_legal_tipo_doc',
        'rep_legal_documento', 'rep_legal_email', 'rep_legal_telefono',
        'direccion', 'barrio', 'municipio_id', 'departamento_id', 'pais_id',
        'codigo_postal', 'telefono', 'telefono_alt', 'celular',
        'email', 'email_notificaciones', 'sitio_web',
        'logo_path', 'color_primario', 'color_secundario',
        'comision_administracion', 'comision_corretaje', 'comision_corretaje_vendedor',
        'dia_corte_mensual',
        'dias_gracia_mora', 'tasa_mora_mensual',
        'sura_tarifa_estudio', 'inmobiliaria_tarifa_estudio',
        'tarifa_estudio_directo', 'nota_estudio_sura', 'tarifa_seguro_sura',
        'banco', 'tipo_cuenta', 'numero_cuenta',
        'notas', 'is_active',
    ];

    protected $casts = [
        'fecha_matricula'            => 'date',
        'fecha_renovacion'           => 'date',
        'fecha_resolucion'           => 'date',
        'responsable_iva'            => 'boolean',
        'gran_contribuyente'         => 'boolean',
        'autorretenedor'             => 'boolean',
        'agente_retencion_fuente'    => 'boolean',
        'agente_reteica'             => 'boolean',
        'agente_reteiva'             => 'boolean',
        'factura_electronica_activa' => 'boolean',
        'is_active'                  => 'boolean',
    ];

    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class);
    }

    public function pais(): BelongsTo
    {
        return $this->belongsTo(Pais::class);
    }

    public static function calcularDigitoNit(string $nit): int
    {
        $nit    = preg_replace('/[^0-9]/', '', $nit);
        // Pesos DIAN: se aplican de derecha a izquierda sobre el NIT
        $pesos  = [71, 67, 59, 53, 47, 43, 41, 37, 29, 23, 19, 17, 13, 7, 3];
        $nit    = str_pad($nit, 15, '0', STR_PAD_LEFT);
        $suma   = 0;
        for ($i = 0; $i < 15; $i++) {
            $suma += (int)$nit[$i] * $pesos[$i];
        }
        $residuo = $suma % 11;
        return $residuo > 1 ? 11 - $residuo : $residuo;
    }

    protected static function booted(): void
    {
        static::saving(function (Company $company) {
            if ($company->nit) {
                $dv = self::calcularDigitoNit($company->nit);
                $company->digito_verificacion = $dv;
                $company->nit_completo = $company->nit . '-' . $dv;
            }
        });
    }
}
