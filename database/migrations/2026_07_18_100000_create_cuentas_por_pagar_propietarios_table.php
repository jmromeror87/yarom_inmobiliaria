<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas_por_pagar_propietarios', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->string('tipo')->default('otro');
            $table->string('concepto');
            $table->foreignId('third_id')->constrained('thirds'); // propietario
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->decimal('valor_original', 15, 2);
            $table->decimal('valor_pagado', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->enum('estado', ['pendiente', 'parcial', 'pagado'])->default('pendiente');
            $table->date('fecha_origen');
            $table->date('fecha_pago_total')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_pagar_propietarios');
    }
};
