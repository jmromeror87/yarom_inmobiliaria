<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_liquidations', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 30)->unique(); // LIQ-2026-0001

            $table->foreignId('rental_contract_id')->constrained('rental_contracts');
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('propietario_id')->constrained('thirds');

            // ── Periodo ───────────────────────────────────────
            $table->integer('mes');
            $table->integer('anio');
            $table->date('periodo_inicio');
            $table->date('periodo_fin');

            // ── Cálculo ───────────────────────────────────────
            $table->decimal('canon_cobrado', 12, 2);
            $table->decimal('comision_porcentaje', 5, 2);
            $table->decimal('comision_valor', 12, 2);
            $table->decimal('iva_comision', 12, 2);
            $table->decimal('otros_descuentos', 12, 2)->default(0);
            $table->text('descripcion_descuentos')->nullable();
            $table->decimal('total_giro', 12, 2);

            // ── Estado ────────────────────────────────────────
            $table->enum('estado', [
                'pendiente',
                'aprobada',
                'pagada',
                'anulada',
            ])->default('pendiente');

            // ── Pago al propietario ───────────────────────────
            $table->date('fecha_giro')->nullable();
            $table->enum('forma_giro', [
                'transferencia', 'consignacion', 'cheque', 'efectivo'
            ])->nullable();
            $table->string('referencia_giro', 100)->nullable();
            $table->string('comprobante_giro_path', 300)->nullable();

            // ── Notificación ──────────────────────────────────
            $table->boolean('wap_enviado')->default(false);
            $table->timestamp('wap_enviado_at')->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['propietario_id', 'mes', 'anio']);
            $table->index('estado');
        });
    }
    public function down(): void { Schema::dropIfExists('owner_liquidations'); }
};
