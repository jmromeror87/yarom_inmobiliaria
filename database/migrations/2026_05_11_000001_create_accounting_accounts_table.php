<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('nombre', 200);
            $table->tinyInteger('nivel');        // 1=Clase 2=Grupo 3=Cuenta 4=Subcuenta
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->enum('clase', ['1','2','3','4','5','6','7','8','9']);
            $table->enum('naturaleza', ['debito', 'credito']);
            $table->boolean('acepta_movimiento')->default(false); // Solo cuentas auxiliares
            $table->boolean('requiere_tercero')->default(false);
            $table->boolean('requiere_centro_costo')->default(false);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('accounting_accounts')->nullOnDelete();
            $table->index(['codigo', 'nivel', 'clase', 'estado']);
        });
    }

    public function down(): void { Schema::dropIfExists('accounting_accounts'); }
};
