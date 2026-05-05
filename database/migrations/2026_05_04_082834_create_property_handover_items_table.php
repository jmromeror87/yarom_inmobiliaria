<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_handover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_handover_id')->constrained('property_handovers')->cascadeOnDelete();

            // ── Ambiente ─────────────────────────────────────
            $table->enum('ambiente', [
                'sala', 'comedor', 'cocina', 'habitacion_principal',
                'habitacion_2', 'habitacion_3', 'bano_principal',
                'bano_secundario', 'bano_social', 'garaje',
                'deposito', 'patio', 'balcon', 'zona_lavanderia',
                'estudio', 'otro',
            ])->default('sala');

            $table->string('ambiente_detalle', 100)->nullable();

            // ── Elemento del inventario ──────────────────────
            $table->string('elemento', 150);

            // ── Estado ───────────────────────────────────────
            $table->enum('estado', [
                'excelente', 'bueno', 'regular', 'malo', 'no_aplica'
            ])->default('bueno');

            // ── Descripción del estado ───────────────────────
            $table->text('descripcion')->nullable();

            // ── Foto de evidencia ────────────────────────────
            $table->string('foto_path', 300)->nullable();

            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index(['property_handover_id', 'ambiente']);
        });
    }
    public function down(): void { Schema::dropIfExists('property_handover_items'); }
};
