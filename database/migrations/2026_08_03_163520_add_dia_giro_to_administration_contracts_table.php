<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->unsignedTinyInteger('dia_giro')->nullable()->after('comision_porcentaje')
                ->comment('Día del mes en que se gira al propietario, independiente de si el inquilino ya pagó');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('administration_contracts', function (Blueprint $table) {
            $table->dropColumn('dia_giro');
        });
    }
};
