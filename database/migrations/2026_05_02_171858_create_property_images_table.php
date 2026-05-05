<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('path', 300);
            $table->string('titulo', 150)->nullable();
            $table->enum('categoria', [
                'fachada','sala','cocina','habitacion','bano',
                'zona_comun','vista','plano','otro'
            ])->default('otro');
            $table->boolean('es_portada')->default(false);
            $table->smallInteger('orden')->default(0);
            $table->timestamps();

            $table->index(['property_id', 'orden']);
        });
    }
    public function down(): void { Schema::dropIfExists('property_images'); }
};
