<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_handover_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_handover_id')->constrained('property_handovers')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estado_anterior', ['borrador','en_proceso','firmada','cerrada'])->nullable();
            $table->enum('estado_nuevo', ['borrador','en_proceso','firmada','cerrada']);
            $table->string('canal', 30)->nullable();
            $table->text('razon_cambio')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('cambiado_en');
            $table->timestamps();
            $table->index('property_handover_id');
        });
    }
    public function down(): void { Schema::dropIfExists('property_handover_history'); }
};
