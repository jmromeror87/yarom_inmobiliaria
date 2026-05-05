<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_bills', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // FAC-2026-0001

            // ── Relaciones ───────────────────────────────────
            $table->foreignId('rental_contract_id')->constrained('rental_contracts');
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('arrendatario_id')->constrained('thirds');

            // ── Periodo ──────────────────────────────────────
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->integer('mes');
            $table->integer('anio');

            // ── Valores ──────────────────────────────────────
            $table->decimal('canon_base', 12, 2);
            $table->decimal('cuota_administracion', 12, 2)->default(0);
            $table->decimal('descuentos', 12, 2)->default(0);
            $table->decimal('otros_cobros', 12, 2)->default(0);
            $table->text('descripcion_otros_cobros')->nullable();
            $table->decimal('total_factura', 12, 2);

            // ── Mora ─────────────────────────────────────────
            $table->date('fecha_limite_pago');
            $table->integer('dias_gracia')->default(5);
            $table->decimal('tasa_mora_diaria', 8, 6)->default(0);
            $table->decimal('mora_acumulada', 12, 2)->default(0);
            $table->date('fecha_inicio_mora')->nullable();
            $table->integer('dias_mora')->default(0);

            // ── Estado ───────────────────────────────────────
            $table->enum('estado', [
                'pendiente',
                'parcial',
                'pagada',
                'vencida',
                'en_mora',
                'anulada',
            ])->default('pendiente');

            // ── Pago ─────────────────────────────────────────
            $table->decimal('total_pagado', 12, 2)->default(0);
            $table->decimal('saldo_pendiente', 12, 2)->default(0);
            $table->date('fecha_pago')->nullable();

            // ── Facturación ───────────────────────────────────
            $table->enum('tipo_documento', [
                'documento_equivalente',
                'factura_electronica',
            ])->default('documento_equivalente');
            $table->string('cufe', 200)->nullable();
            $table->string('numero_dian', 50)->nullable();

            // ── Notificaciones ────────────────────────────────
            $table->boolean('wap_enviado')->default(false);
            $table->timestamp('wap_enviado_at')->nullable();
            $table->boolean('wap_mora_enviado')->default(false);
            $table->timestamp('wap_mora_enviado_at')->nullable();

            // ── Liquidación ────────────────────────────────────
            $table->unsignedBigInteger('owner_liquidation_id')->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['rental_contract_id', 'mes', 'anio']);
            $table->index(['estado', 'fecha_limite_pago']);
        });
    }
    public function down(): void { Schema::dropIfExists('rent_bills'); }
};
