<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_sura_studies', function (Blueprint $table) {
            $table->string('estudio_token', 64)->nullable()->unique()->after('notas');
            $table->timestamp('estudio_token_used_at')->nullable()->after('estudio_token');
        });
    }

    public function down(): void
    {
        Schema::table('request_sura_studies', function (Blueprint $table) {
            $table->dropColumn(['estudio_token', 'estudio_token_used_at']);
        });
    }
};
