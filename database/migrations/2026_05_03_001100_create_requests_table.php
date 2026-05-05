<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // SOL-2026-0001

            // ── Relaciones principales ──────────────────────────
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();

            // ── Tipo de solicitud ───────────────────────────────
            $table->enum('tipo', [
                'estudio_propietario',   // ¿Podemos recibir este inmueble?
                'estudio_arrendatario',  // ¿Puede este candidato arrendar?
                'estudio_comprador',     // ¿Puede este candidato comprar?
            ])->default('estudio_arrendatario');

            // ── Pipeline de estados ─────────────────────────────
            $table->enum('estado', [
                'radicada',
                'en_estudio',
                'aprobada',
                'condicional',
                'rechazada',
                'desistida',
            ])->default('radicada');

            // ── Condición económica evaluada ────────────────────
            $table->decimal('canon_evaluar', 12, 2)->nullable();
            $table->decimal('precio_venta_evaluar', 14, 2)->nullable();

            // ── Resultado de la evaluación ──────────────────────
            $table->date('fecha_radicacion')->nullable();
            $table->date('fecha_decision')->nullable();
            $table->string('decidido_por', 150)->nullable();
            $table->text('concepto_evaluacion')->nullable();
            $table->text('condiciones_especiales')->nullable();

            // ── Automatización de estados del inmueble ──────────
            $table->enum('estado_inmueble_anterior', [
                'en_captacion','documentos_pendientes','disponible',
                'arrendado','en_venta','vendido','en_mantenimiento','inactivo',
            ])->nullable();

            $table->enum('estado_inmueble_nuevo', [
                'en_captacion','documentos_pendientes','disponible',
                'arrendado','en_venta','vendido','en_mantenimiento','inactivo',
            ])->nullable();

            $table->boolean('cambio_estado_aplicado')->default(false);

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'estado']);
            $table->index('tipo');
        });
    }
    public function down(): void { Schema::dropIfExists('requests'); }
};
