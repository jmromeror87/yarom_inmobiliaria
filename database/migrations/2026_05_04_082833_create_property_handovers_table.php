<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // ACT-2026-0001

            // ── Relaciones ───────────────────────────────────
            $table->foreignId('rental_contract_id')->constrained('rental_contracts')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('arrendatario_id')->constrained('thirds');
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();

            // ── Tipo de acta ─────────────────────────────────
            $table->enum('tipo', ['entrega', 'devolucion'])->default('entrega');

            // ── Fecha y lugar ────────────────────────────────
            $table->date('fecha_acta');
            $table->time('hora_acta')->nullable();
            $table->string('lugar_acta', 200)->nullable();

            // ── Lecturas de medidores ────────────────────────
            $table->string('lectura_agua', 50)->nullable();
            $table->string('lectura_energia', 50)->nullable();
            $table->string('lectura_gas', 50)->nullable();

            // ── Llaves ───────────────────────────────────────
            $table->integer('llaves_entregadas')->default(0);
            $table->integer('llaves_control_acceso')->default(0);
            $table->integer('llaves_parqueadero')->default(0);
            $table->integer('llaves_deposito')->default(0);
            $table->text('notas_llaves')->nullable();

            // ── Estado general ───────────────────────────────
            $table->enum('estado_general', [
                'excelente', 'bueno', 'regular', 'malo'
            ])->default('bueno');

            // ── Observaciones generales ──────────────────────
            $table->text('observaciones_generales')->nullable();

            // ── Firmas ───────────────────────────────────────
            $table->string('firmado_arrendatario', 200)->nullable();
            $table->string('firmado_asesor', 200)->nullable();
            $table->date('fecha_firma')->nullable();

            // ── Documentos ───────────────────────────────────
            $table->string('path_acta_firmada', 300)->nullable();

            // ── Estado del acta ──────────────────────────────
            $table->enum('estado', [
                'borrador', 'en_proceso', 'firmada', 'cerrada'
            ])->default('borrador');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'tipo']);
            $table->index('rental_contract_id');
        });
    }
    public function down(): void { Schema::dropIfExists('property_handovers'); }
};
