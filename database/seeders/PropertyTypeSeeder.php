<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Apartamento',       'icono' => 'heroicon-o-building-office'],
            ['nombre' => 'Casa',               'icono' => 'heroicon-o-home'],
            ['nombre' => 'Casa Campestre',     'icono' => 'heroicon-o-home-modern'],
            ['nombre' => 'Local Comercial',    'icono' => 'heroicon-o-shopping-bag'],
            ['nombre' => 'Oficina',            'icono' => 'heroicon-o-briefcase'],
            ['nombre' => 'Bodega',             'icono' => 'heroicon-o-cube'],
            ['nombre' => 'Lote',               'icono' => 'heroicon-o-map'],
            ['nombre' => 'Finca',              'icono' => 'heroicon-o-sun'],
            ['nombre' => 'Consultorio',        'icono' => 'heroicon-o-building-office-2'],
            ['nombre' => 'Parqueadero',        'icono' => 'heroicon-o-truck'],
        ];

        foreach ($tipos as $tipo) {
            DB::table('property_types')->insert([
                'nombre'     => $tipo['nombre'],
                'icono'      => $tipo['icono'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
