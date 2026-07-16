<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('numero_cuenta', 40)->nullable();
            $table->enum('tipo_cuenta', ['ahorros', 'corriente', 'caja', 'digital'])->default('corriente');
            $table->foreignId('accounting_account_id')->nullable()->constrained('accounting_accounts')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Cuentas contables auxiliares para los bancos que aún no existían ──
        $nuevasCuentas = [
            ['codigo' => '11100509', 'nombre' => 'Banco de Bogotá',            'nivel' => 5, 'clase' => '1', 'naturaleza' => 'debito', 'acepta_movimiento' => true],
            ['codigo' => '11100510', 'nombre' => 'Crediservir',                'nivel' => 5, 'clase' => '1', 'naturaleza' => 'debito', 'acepta_movimiento' => true],
        ];

        $parentId = DB::table('accounting_accounts')->where('codigo', '111005')->value('id');

        foreach ($nuevasCuentas as $c) {
            $existe = DB::table('accounting_accounts')->where('codigo', $c['codigo'])->exists();
            if (! $existe) {
                DB::table('accounting_accounts')->insert(array_merge($c, [
                    'parent_id' => $parentId,
                    'requiere_tercero' => false,
                    'requiere_centro_costo' => false,
                    'estado' => 'activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $cajaId        = DB::table('accounting_accounts')->where('codigo', '11050501')->value('id');
        $bancolombiaId = DB::table('accounting_accounts')->where('codigo', '11100502')->value('id');
        $bogotaId      = DB::table('accounting_accounts')->where('codigo', '11100509')->value('id');
        $crediservirId = DB::table('accounting_accounts')->where('codigo', '11100510')->value('id');

        DB::table('banks')->insert([
            ['nombre' => 'Caja general',   'numero_cuenta' => null,          'tipo_cuenta' => 'caja',      'accounting_account_id' => $cajaId,        'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Bancolombia',    'numero_cuenta' => '31807390970', 'tipo_cuenta' => 'corriente', 'accounting_account_id' => $bancolombiaId, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Banco de Bogotá','numero_cuenta' => '446050999',   'tipo_cuenta' => 'corriente', 'accounting_account_id' => $bogotaId,      'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Crediservir',    'numero_cuenta' => '2010069286',  'tipo_cuenta' => 'ahorros',   'accounting_account_id' => $crediservirId, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
