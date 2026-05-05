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
| Archivo: ContractTemplate.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    protected $table = 'contract_templates';
    protected $fillable = ['nombre','tipo_contrato','encabezado','pie_pagina','is_active','is_default'];
    protected $casts = ['is_active' => 'boolean', 'is_default' => 'boolean'];

    public function clauses() { return $this->hasMany(ContractClause::class); }
    public function clausesOrdenadas() { return $this->hasMany(ContractClause::class)->orderBy('orden'); }
}
