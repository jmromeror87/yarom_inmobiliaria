<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('administration_contract_id')->constrained('administration_contracts')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('estado_anterior', [
                'borrador','enviado_propietario','en_revision',
                'aprobado_gerencia','enviado_notaria','autenticado_notaria',
                'firmado','activo','terminado','cancelado',
            ])->nullable();

            $table->enum('estado_nuevo', [
                'borrador','enviado_propietario','en_revision',
                'aprobado_gerencia','enviado_notaria','autenticado_notaria',
                'firmado','activo','terminado','cancelado',
            ]);

            $table->string('canal', 30)->nullable(); // whatsapp, email, presencial, sistema
            $table->text('razon_cambio')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('cambiado_en');
            $table->timestamps();

            $table->index('administration_contract_id');
        });
    }
    public function down(): void { Schema::dropIfExists('contract_status_history'); }
};
