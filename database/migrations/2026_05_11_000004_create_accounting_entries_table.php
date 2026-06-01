<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->enum('tipo', ['CC','CI','CE','ND','NC','CA'])->comment('CC=Contabilidad,CI=Ingreso,CE=Egreso,ND=Nota Débito,NC=Nota Crédito,CA=Ajuste');
            $table->string('numero', 30)->unique();   // Ej: CC-2026-0001
            $table->date('fecha');
            $table->string('descripcion', 300);

            // Período contable
            $table->foreignId('period_id')->constrained('accounting_periods');

            // Referencias opcionales
            $table->foreignId('third_id')->nullable()->constrained('thirds')->nullOnDelete();
            $table->foreignId('cost_center_id')->nullable()->constrained('accounting_cost_centers')->nullOnDelete();
            $table->string('referencia', 100)->nullable();     // Número de factura, liquidación, etc.
            $table->string('referencia_tipo', 50)->nullable(); // rent_bill, owner_liquidation, etc.
            $table->unsignedBigInteger('referencia_id')->nullable();

            // Totales (calculados)
            $table->decimal('total_debitos', 15, 2)->default(0);
            $table->decimal('total_creditos', 15, 2)->default(0);

            // Estado y flujo
            $table->enum('estado', ['borrador','contabilizado','anulado'])->default('borrador');
            $table->foreignId('creado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('contabilizado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('contabilizado_en')->nullable();
            $table->foreignId('anulado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('anulado_en')->nullable();
            $table->string('razon_anulacion', 300)->nullable();

            $table->timestamps();

            $table->index(['fecha', 'tipo', 'estado', 'period_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('accounting_entries'); }
};
