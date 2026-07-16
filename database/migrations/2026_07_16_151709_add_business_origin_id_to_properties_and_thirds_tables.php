<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultId = DB::table('business_origins')->where('nombre', 'Serviarrendar')->value('id');

        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('business_origin_id')->nullable()->after('propietario_id')->constrained('business_origins')->nullOnDelete();
        });

        Schema::table('thirds', function (Blueprint $table) {
            $table->foreignId('business_origin_id')->nullable()->after('numero_documento')->constrained('business_origins')->nullOnDelete();
        });

        if ($defaultId) {
            DB::table('properties')->update(['business_origin_id' => $defaultId]);
            DB::table('thirds')->update(['business_origin_id' => $defaultId]);
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_origin_id');
        });
        Schema::table('thirds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('business_origin_id');
        });
    }
};
