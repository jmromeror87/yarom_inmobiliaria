<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('paises')->insert([
            ['codigo_dian' => '170', 'codigo_iso' => 'CO', 'nombre' => 'Colombia',       'indicativo' => '+57',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo_dian' => '840', 'codigo_iso' => 'US', 'nombre' => 'Estados Unidos', 'indicativo' => '+1',   'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo_dian' => '724', 'codigo_iso' => 'ES', 'nombre' => 'España',          'indicativo' => '+34',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo_dian' => '862', 'codigo_iso' => 'VE', 'nombre' => 'Venezuela',       'indicativo' => '+58',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo_dian' => '604', 'codigo_iso' => 'PE', 'nombre' => 'Perú',            'indicativo' => '+51',  'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['codigo_dian' => '218', 'codigo_iso' => 'EC', 'nombre' => 'Ecuador',         'indicativo' => '+593', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
