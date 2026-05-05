<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_thirds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('third_id')->constrained('thirds');

            // ── Rol en esta solicitud ───────────────────────────
            $table->enum('rol', [
                'titular',       // Principal arrendatario o propietario
                'codeudor',      // Codeudor solidario
                'fiador',        // Fiador personal
                'propietario',   // Propietario del inmueble
                'representante', // Representante legal (para empresas)
            ])->default('titular');

            // ── Evaluación individual ───────────────────────────
            $table->decimal('ingresos_declarados', 12, 2)->nullable();
            $table->decimal('ingresos_verificados', 12, 2)->nullable();
            $table->integer('score_datacredito')->nullable();
            $table->boolean('reporte_negativo')->default(false);
            $table->enum('resultado_individual', [
                'pendiente', 'aprobado', 'rechazado', 'condicional'
            ])->default('pendiente');
            $table->text('notas_evaluacion')->nullable();

            // ── Relación canon ──────────────────────────────────
            $table->decimal('relacion_ingreso_canon', 5, 2)->nullable();

            $table->timestamps();
            $table->index(['request_id', 'rol']);
        });
    }
    public function down(): void { Schema::dropIfExists('request_thirds'); }
};
