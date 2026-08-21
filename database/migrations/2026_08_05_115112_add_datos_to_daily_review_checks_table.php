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
        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->json('datos')->nullable()->after('third_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_review_checks', function (Blueprint $table) {
            $table->dropColumn('datos');
        });
    }
};
