<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siinmob_historico_notas', function (Blueprint $table) {
            $table->text('detalle')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siinmob_historico_notas', function (Blueprint $table) {
            $table->string('detalle')->nullable()->change();
        });
    }
};
