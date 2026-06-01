<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            // Origen
            $table->string('tipo'); // 'deposito_arriendo' | 'mora' | 'dano' | 'otro'
            $table->string('concepto');
            // Relaciones
            $table->foreignId('rental_contract_id')->nullable()->constrained('rental_contracts')->nullOnDelete();
            $table->foreignId('third_id')->nullable()->constrained('thirds')->nullOnDelete(); // deudor
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            // Montos
            $table->decimal('valor_original', 12, 2);
            $table->decimal('valor_pagado',   12, 2)->default(0);
            $table->decimal('saldo',          12, 2);
            // Estado: 'pendiente' | 'parcial' | 'pagado' | 'castigada'
            $table->string('estado')->default('pendiente');
            $table->date('fecha_origen');
            $table->date('fecha_vencimiento')->nullable();
            $table->date('fecha_pago_total')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};
