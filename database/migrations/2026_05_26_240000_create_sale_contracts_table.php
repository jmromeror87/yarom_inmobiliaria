<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrato')->unique();

            // Partes
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('vendedor_id')->nullable()->constrained('thirds')->nullOnDelete();
            $table->foreignId('comprador_id')->nullable()->constrained('thirds')->nullOnDelete();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();

            // Datos de la negociación
            $table->decimal('precio_venta', 14, 2);
            $table->decimal('precio_avaluo', 14, 2)->nullable();
            $table->string('forma_pago')->default('contado'); // contado | credito_hipotecario | leasing | mixto
            $table->string('entidad_financiera')->nullable();
            $table->decimal('valor_credito', 14, 2)->nullable();
            $table->decimal('valor_cuota_inicial', 14, 2)->nullable();

            // Comisión de corretaje
            $table->string('quien_paga_comision')->default('comprador'); // vendedor | comprador | ambos | ninguno
            $table->decimal('porcentaje_comision', 5, 2)->default(3.00);
            $table->decimal('valor_comision', 14, 2)->nullable();
            $table->decimal('comision_pagada', 14, 2)->default(0);
            $table->string('estado_comision')->default('pendiente'); // pendiente | parcial | pagada

            // Estado del contrato
            $table->string('estado')->default('promesa'); // promesa | escrituracion | registrado | entregado | cancelado
            $table->date('fecha_promesa')->nullable();
            $table->date('fecha_escritura')->nullable();
            $table->date('fecha_registro')->nullable();
            $table->date('fecha_entrega')->nullable();

            // Notaría
            $table->string('notaria')->nullable();
            $table->string('notaria_ciudad')->nullable();
            $table->string('numero_escritura')->nullable();
            $table->date('fecha_escrituracion')->nullable();

            // Documentos
            $table->string('path_promesa')->nullable();
            $table->string('path_escritura')->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_contracts');
    }
};
