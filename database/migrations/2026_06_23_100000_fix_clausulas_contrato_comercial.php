<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private int $templateId = 3; // Contrato Arrendamiento Comercial

    public function up(): void
    {
        // 1. Corregir DÉCIMA (id=61): redacción correcta según contrato físico
        DB::table('contract_clauses')->where('id', 61)->update([
            'contenido' => 'Las adecuaciones serán asumidas al 100% por el arrendatario. Así mismo las mejoras quedarán en el inmueble y el arrendatario no podrá solicitar ningún derecho de reembolso por estas, las mejoras deberán quedar a satisfacción del propietario y subsanar los daños que por estos sufra el inmueble.',
        ]);

        // 2. Eliminar VIGÉSIMA PRIMERA (id=72) "Solidaridad" — no está en contrato físico
        DB::table('contract_clauses')->where('id', 72)->delete();

        // 3. Mover "Gastos" (id=77) a VIGÉSIMA PRIMERA (orden 21, después de cláusula penal)
        DB::table('contract_clauses')->where('id', 77)->update([
            'numero' => 'VIGÉSIMA PRIMERA',
            'orden'  => 21,
        ]);

        // 4. Renumerar las cláusulas que siguen (Requerimientos → VIGÉSIMA SEGUNDA, etc.)
        $renombrar = [
            73 => ['numero' => 'VIGÉSIMA SEGUNDA',  'titulo' => 'Requerimientos',         'orden' => 22],
            74 => ['numero' => 'VIGÉSIMA TERCERA',   'titulo' => 'Autorización',           'orden' => 23],
            75 => ['numero' => 'VIGÉSIMA CUARTA',    'titulo' => 'No existe Good Will',    'orden' => 24],
            76 => ['numero' => 'VIGÉSIMA QUINTA',    'titulo' => 'Copia del Contrato',     'orden' => 25],
            78 => ['numero' => 'VIGÉSIMA SEXTA',     'titulo' => 'Autorización de datos',  'orden' => 26],
            79 => ['numero' => 'VIGÉSIMA SÉPTIMA',   'titulo' => 'Datos de Contacto',      'orden' => 27],
        ];

        foreach ($renombrar as $id => $data) {
            DB::table('contract_clauses')->where('id', $id)->update($data);
        }
    }

    public function down(): void
    {
        // Restaurar DÉCIMA original
        DB::table('contract_clauses')->where('id', 61)->update([
            'contenido' => 'EL ARRENDADOR autoriza de forma expresa al ARRENDATARIO para realizar las mejoras correspondientes, estas adecuaciones serán asumidas al 100% por el arrendatario. Así mismo las mejoras quedarán en el inmueble y el arrendatario no podrá solicitar ningún derecho de reembolso por estas.',
        ]);

        // Restaurar Gastos a VIGÉSIMA SEXTA
        DB::table('contract_clauses')->where('id', 77)->update([
            'numero' => 'VIGÉSIMA SEXTA',
            'orden'  => 26,
        ]);

        // Restaurar numeración
        $restaurar = [
            73 => ['numero' => 'VIGÉSIMA SEGUNDA',  'orden' => 22],
            74 => ['numero' => 'VIGÉSIMA TERCERA',  'orden' => 23],
            75 => ['numero' => 'VIGÉSIMA CUARTA',   'orden' => 24],
            76 => ['numero' => 'VIGÉSIMA QUINTA',   'orden' => 25],
            78 => ['numero' => 'VIGÉSIMA SÉPTIMA',  'orden' => 27],
            79 => ['numero' => 'VIGÉSIMA OCTAVA',   'orden' => 28],
        ];

        foreach ($restaurar as $id => $data) {
            DB::table('contract_clauses')->where('id', $id)->update($data);
        }

        // Re-insertar VIGÉSIMA PRIMERA Solidaridad
        DB::table('contract_clauses')->insert([
            'id'                  => 72,
            'contract_template_id'=> 3,
            'numero'              => 'VIGÉSIMA PRIMERA',
            'titulo'              => 'Solidaridad',
            'tipo'                => 'clausula',
            'contenido'           => 'Los derechos y las obligaciones derivadas del presente contrato son solidarias, tanto entre ARRENDADORES como entre ARRENDATARIOS.',
            'es_editable'         => 1,
            'es_obligatoria'      => 1,
            'orden'               => 21,
            'is_active'           => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }
};
