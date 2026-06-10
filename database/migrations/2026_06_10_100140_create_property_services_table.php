<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->foreignId('third_id')->constrained('thirds')->comment('Proveedor que ejecuta');
            $table->foreignId('rental_contract_id')->nullable()->constrained('rental_contracts')->nullOnDelete();
            $table->foreignId('owner_liquidation_id')->nullable()->constrained('owner_liquidations')->nullOnDelete();
            $table->foreignId('accounting_entry_id')->nullable()->constrained('accounting_entries')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('numero', 20)->unique();
            $table->string('tipo', 30)->default('mantenimiento');
            $table->text('descripcion');
            $table->date('fecha_servicio');
            $table->date('fecha_pago_proveedor')->nullable();

            $table->decimal('valor', 14, 2)->default(0);
            $table->decimal('iva', 14, 2)->default(0);
            $table->decimal('retencion', 14, 2)->default(0);

            $table->string('quien_paga', 30)->default('propietario');
            $table->string('estado', 20)->default('pendiente');
            $table->string('estado_pago_proveedor', 20)->default('pendiente');

            $table->string('cuenta_gasto_puc', 20)->nullable();
            $table->string('cuenta_pagar_puc', 20)->nullable();

            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_services');
    }
};
