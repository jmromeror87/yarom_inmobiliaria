<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_origins', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('color', 20)->default('#64748B'); // hex para el badge
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('business_origins')->insert([
            ['nombre' => 'Serviarrendar', 'color' => '#2563EB', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Victoria', 'color' => '#E11D48', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_origins');
    }
};
