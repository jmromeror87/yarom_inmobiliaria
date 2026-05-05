<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_payments', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // PAG-2026-0001

            $table->foreignId('rent_bill_id')->constrained('rent_bills')->cascadeOnDelete();
            $table->foreignId('rental_contract_id')->constrained('rental_contracts');
            $table->foreignId('arrendatario_id')->constrained('thirds');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();

            // ── Valores ──────────────────────────────────────
            $table->decimal('valor_canon', 12, 2)->default(0);
            $table->decimal('valor_mora', 12, 2)->default(0);
            $table->decimal('valor_administracion', 12, 2)->default(0);
            $table->decimal('otros_valores', 12, 2)->default(0);
            $table->decimal('total_pagado', 12, 2);

            // ── Forma de pago ─────────────────────────────────
            $table->enum('forma_pago', [
                'efectivo',
                'transferencia',
                'consignacion',
                'nequi',
                'daviplata',
                'pse',
                'cheque',
                'otro',
            ])->default('transferencia');

            $table->date('fecha_pago');
            $table->string('referencia_pago', 100)->nullable();
            $table->string('banco_origen', 100)->nullable();
            $table->string('comprobante_path', 300)->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();

            $table->index(['rent_bill_id', 'fecha_pago']);
        });
    }
    public function down(): void { Schema::dropIfExists('rent_payments'); }
};
