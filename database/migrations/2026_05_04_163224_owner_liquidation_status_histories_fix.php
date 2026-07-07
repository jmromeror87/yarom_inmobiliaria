<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_liquidation_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_liquidation_id')
                  ->constrained('owner_liquidations')->cascadeOnDelete();
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->text('notas')->nullable();
            $table->timestamp('cambiado_en')->useCurrent();
            $table->timestamps();
            $table->index(['owner_liquidation_id', 'cambiado_en'], 'olsh_liquidation_cambiado_idx');
        });

        Schema::table('owner_liquidations', function (Blueprint $table) {
            if (!Schema::hasColumn('owner_liquidations', 'aplica_retefuente')) {
                $table->boolean('aplica_retefuente')->default(false)->after('iva_comision');
            }
            if (!Schema::hasColumn('owner_liquidations', 'retefuente_valor')) {
                $table->decimal('retefuente_valor', 12, 2)->default(0)->after('aplica_retefuente');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_liquidation_status_histories');
        Schema::table('owner_liquidations', function (Blueprint $table) {
            $table->dropColumnIfExists(['aplica_retefuente', 'retefuente_valor']);
        });
    }
};
