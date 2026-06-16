<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->string('ubicacion_archivo', 100)->nullable()->after('estado_expediente');
        });
    }

    public function down(): void
    {
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropColumn('ubicacion_archivo');
        });
    }
};
