<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_contract_thirds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_contract_id')->constrained('rental_contracts')->cascadeOnDelete();
            $table->foreignId('third_id')->constrained('thirds');

            $table->enum('rol', [
                'arrendatario',
                'deudor_solidario',
                'fiador',
                'codeudor',
            ])->default('deudor_solidario');

            $table->string('ciudad_expedicion_doc', 100)->nullable();
            $table->string('direccion_notificacion', 200)->nullable();
            $table->string('email_notificacion', 120)->nullable();
            $table->string('celular_notificacion', 20)->nullable();

            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index(['rental_contract_id', 'rol']);
        });
    }
    public function down(): void { Schema::dropIfExists('rental_contract_thirds'); }
};
