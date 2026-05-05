<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('numero_contrato', 30)->unique();

            // ── Relaciones ───────────────────────────────────────
            $table->foreignId('property_id')->constrained('properties');
            $table->foreignId('administration_contract_id')->nullable()->constrained('administration_contracts')->nullOnDelete();
            $table->foreignId('request_id')->nullable()->constrained('requests')->nullOnDelete();
            $table->foreignId('contract_template_id')->nullable()->constrained('contract_templates')->nullOnDelete();
            $table->foreignId('asesor_id')->nullable()->constrained('users')->nullOnDelete();

            // ── Tipo ─────────────────────────────────────────────
            $table->enum('tipo', [
                'vivienda_urbana',
                'comercial',
            ])->default('vivienda_urbana');

            // ── Datos encabezado ─────────────────────────────────
            $table->string('lugar_contrato', 100)->default('Ocaña');
            $table->date('fecha_contrato')->nullable();
            $table->string('destinacion', 200)->nullable();
            $table->string('actividad_comercial', 200)->nullable(); // solo comercial
            $table->string('folio_inmobiliario', 80)->nullable();

            // ── Partes ───────────────────────────────────────────
            $table->foreignId('arrendatario_id')->constrained('thirds');

            // ── Condiciones económicas ───────────────────────────
            $table->decimal('canon_mensual', 12, 2);
            $table->decimal('deposito', 12, 2)->default(0);
            $table->decimal('cuota_administracion', 12, 2)->default(0);

            // ── Vigencia ─────────────────────────────────────────
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('duracion_meses')->default(6);

            // ── Incremento ───────────────────────────────────────
            $table->enum('tipo_incremento', ['ipc_vivienda', 'porcentaje_fijo'])->default('ipc_vivienda');
            $table->decimal('porcentaje_incremento', 5, 2)->nullable();

            // ── Preaviso (Ley 820) ───────────────────────────────
            $table->integer('meses_preaviso')->default(3);

            // ── Servicios ────────────────────────────────────────
            $table->text('servicios_cargo_arrendatario')->nullable();

            // ── Garantía ─────────────────────────────────────────
            $table->enum('tipo_garantia', ['codeudor', 'garantia_bancaria', 'seguro_arrendamiento', 'ninguna'])->default('codeudor');

            // ── Estado del contrato ──────────────────────────────
            $table->enum('estado', [
                'borrador',
                'enviado_arrendatario',
                'aprobado',
                'firmado',
                'activo',
                'terminado',
                'cancelado',
            ])->default('borrador');

            // ── Firma ────────────────────────────────────────────
            $table->date('fecha_firma')->nullable();
            $table->string('firmado_por', 200)->nullable();
            $table->string('path_contrato_firmado', 300)->nullable();

            // ── Terminación ──────────────────────────────────────
            $table->date('fecha_terminacion')->nullable();
            $table->enum('causal_terminacion', [
                'vencimiento',
                'mutuo_acuerdo',
                'incumplimiento_arrendatario',
                'incumplimiento_arrendador',
                'desahucio',
                'necesidad_propietario',
                'otra',
            ])->nullable();

            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_id', 'estado']);
            $table->index('arrendatario_id');
        });
    }
    public function down(): void { Schema::dropIfExists('rental_contracts'); }
};
