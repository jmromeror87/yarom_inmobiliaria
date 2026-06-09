<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_declarations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('obligation_type_id')->constrained('dian_obligation_types');

            // Período
            $table->unsignedSmallInteger('anio');
            $table->unsignedTinyInteger('periodo');      // mes, bimestre, cuatrimestre o 0 para anual
            $table->string('periodo_label', 50);         // "Enero 2026", "Bimestre 1 2026", etc.
            $table->date('fecha_inicio_periodo');
            $table->date('fecha_fin_periodo');
            $table->date('fecha_vencimiento');           // Calculada según NIT + calendario DIAN

            // Estado
            $table->enum('estado', [
                'pendiente',
                'en_proceso',
                'presentada',
                'pagada',
                'no_aplica',
            ])->default('pendiente');

            // Valores calculados automáticamente
            $table->json('calculo')->nullable();         // Desglose detallado del cálculo
            $table->decimal('valor_a_pagar', 15, 2)->default(0);
            $table->decimal('sanciones', 15, 2)->default(0);
            $table->decimal('intereses', 15, 2)->default(0);
            $table->decimal('total_declarado', 15, 2)->default(0);

            // Presentación
            $table->string('numero_formulario', 30)->nullable();
            $table->date('fecha_presentacion')->nullable();
            $table->decimal('valor_pagado', 15, 2)->default(0);
            $table->date('fecha_pago')->nullable();
            $table->string('banco_pago', 100)->nullable();
            $table->string('referencia_pago', 100)->nullable();

            // Notas y adjunto
            $table->text('notas')->nullable();
            $table->string('adjunto_path', 500)->nullable();  // PDF firmado guardado

            // Trazabilidad
            $table->foreignId('calculado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculado_en')->nullable();
            $table->foreignId('presentado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pagado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['obligation_type_id', 'anio', 'periodo']);
            $table->index(['anio', 'estado']);
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dian_declarations');
    }
};
