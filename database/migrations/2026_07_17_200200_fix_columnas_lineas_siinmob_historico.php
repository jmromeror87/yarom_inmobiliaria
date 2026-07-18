<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siinmob_historico_lineas', function (Blueprint $table) {
            $table->text('descripcion_linea')->nullable()->change();
            $table->text('cuenta_nombre')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siinmob_historico_lineas', function (Blueprint $table) {
            $table->string('descripcion_linea')->nullable()->change();
            $table->string('cuenta_nombre')->nullable()->change();
        });
    }
};
