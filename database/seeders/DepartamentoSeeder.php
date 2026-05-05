<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartamentoSeeder extends Seeder
{
    public function run(): void
    {
        $departamentos = [
            ['05','Antioquia'],['08','Atlántico'],['11','Bogotá D.C.'],
            ['13','Bolívar'],['15','Boyacá'],['17','Caldas'],
            ['18','Caquetá'],['19','Cauca'],['20','Cesar'],
            ['23','Córdoba'],['25','Cundinamarca'],['27','Chocó'],
            ['41','Huila'],['44','La Guajira'],['47','Magdalena'],
            ['50','Meta'],['52','Nariño'],['54','Norte de Santander'],
            ['63','Quindío'],['66','Risaralda'],['68','Santander'],
            ['70','Sucre'],['73','Tolima'],['76','Valle del Cauca'],
            ['81','Arauca'],['85','Casanare'],['86','Putumayo'],
            ['88','San Andrés y Providencia'],['91','Amazonas'],
            ['94','Guainía'],['95','Guaviare'],['97','Vaupés'],['99','Vichada'],
        ];

        foreach ($departamentos as [$codigo, $nombre]) {
            DB::table('departamentos')->insert([
                'pais_id' => 1, 'codigo_dane' => $codigo,
                'nombre' => $nombre, 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }
}
