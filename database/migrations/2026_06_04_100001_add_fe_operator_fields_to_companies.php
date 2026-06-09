<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->enum('fe_operador', ['factus', 'dataico', 'facturatech'])
                  ->default('factus')->after('factura_electronica_activa');
            $table->enum('fe_ambiente', ['habilitacion', 'produccion'])
                  ->default('habilitacion')->after('fe_operador');
            $table->date('fecha_vencimiento_resolucion')->nullable()->after('fecha_resolucion');
            $table->string('fe_nota_pie', 300)->nullable()->after('fe_ambiente')
                  ->comment('Texto que aparece al pie de la factura electrónica');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['fe_operador', 'fe_ambiente', 'fecha_vencimiento_resolucion', 'fe_nota_pie']);
        });
    }
};
