<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contract_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_contract_id')->constrained('rental_contracts')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado_anterior', [
                'borrador','enviado_arrendatario','aprobado','firmado','activo','terminado','cancelado'
            ])->nullable();
            $table->enum('estado_nuevo', [
                'borrador','enviado_arrendatario','aprobado','firmado','activo','terminado','cancelado'
            ]);
            $table->string('canal', 30)->nullable();
            $table->text('razon_cambio')->nullable();
            $table->timestamp('cambiado_en');
            $table->timestamps();
            $table->index('rental_contract_id');
        });
    }
    public function down(): void { Schema::dropIfExists('rental_contract_status_history'); }
};
