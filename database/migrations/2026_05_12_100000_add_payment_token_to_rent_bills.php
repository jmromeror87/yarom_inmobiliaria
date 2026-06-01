<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->string('payment_token', 64)->nullable()->unique()->after('notas');
            $table->timestamp('payment_token_expires_at')->nullable()->after('payment_token');
            $table->string('wompi_transaction_id', 100)->nullable()->after('payment_token_expires_at');
            $table->string('wompi_reference', 100)->nullable()->after('wompi_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('rent_bills', function (Blueprint $table) {
            $table->dropColumn(['payment_token', 'payment_token_expires_at', 'wompi_transaction_id', 'wompi_reference']);
        });
    }
};
