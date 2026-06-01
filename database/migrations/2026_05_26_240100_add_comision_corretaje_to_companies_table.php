<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('comision_corretaje', 5, 2)->default(3.00)->after('comision_administracion');
            $table->decimal('comision_corretaje_vendedor', 5, 2)->default(3.00)->after('comision_corretaje');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['comision_corretaje','comision_corretaje_vendedor']);
        });
    }
};
