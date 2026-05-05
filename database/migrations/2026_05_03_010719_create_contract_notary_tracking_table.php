<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_notary_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administration_contract_id')->constrained('administration_contracts')->cascadeOnDelete();
            $table->foreignId('gestionado_por')->nullable()->constrained('users')->nullOnDelete();

            // ── Envío a notaría ───────────────────────────────
            $table->string('notaria_nombre', 150)->nullable();
            $table->string('notaria_ciudad', 100)->nullable()->default('Ocaña');
            $table->string('notaria_direccion', 200)->nullable();
            $table->string('notaria_telefono', 20)->nullable();
            $table->date('fecha_envio_notaria')->nullable();
            $table->string('enviado_por_nombre', 150)->nullable();
            $table->string('numero_radicado_notaria', 50)->nullable();

            // ── Autenticación ─────────────────────────────────
            $table->date('fecha_autenticacion')->nullable();
            $table->string('numero_escritura', 50)->nullable();
            $table->decimal('valor_autenticacion', 10, 2)->nullable();

            // ── Regreso firmado ───────────────────────────────
            $table->date('fecha_regreso')->nullable();
            $table->string('recibido_por', 150)->nullable();
            $table->string('path_contrato_firmado', 300)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->index('administration_contract_id');
        });
    }
    public function down(): void { Schema::dropIfExists('contract_notary_tracking'); }
};
