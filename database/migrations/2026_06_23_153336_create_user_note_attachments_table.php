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
        Schema::create('user_note_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_note_id')->constrained('user_notes')->onDelete('cascade');
            $table->string('path');
            $table->string('nombre');
            $table->string('mime');
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        // Eliminar columnas de adjunto único que ya no se usan
        Schema::table('user_notes', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_mime']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_note_attachments');
    }
};
