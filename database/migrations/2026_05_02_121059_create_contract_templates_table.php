<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->enum('tipo_contrato', ['administracion_arriendo', 'administracion_venta', 'arrendamiento_vivienda', 'arrendamiento_comercial'])->default('administracion_arriendo');
            $table->text('encabezado')->nullable();
            $table->text('pie_pagina')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('contract_templates'); }
};
