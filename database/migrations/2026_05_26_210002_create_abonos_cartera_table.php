<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abonos_cartera', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->cascadeOnDelete();
            $table->decimal('valor', 12, 2);
            $table->date('fecha_abono');
            $table->string('forma_pago')->default('transferencia'); // efectivo | transferencia | cheque
            $table->string('referencia')->nullable(); // número de transacción/recibo
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonos_cartera');
    }
};
