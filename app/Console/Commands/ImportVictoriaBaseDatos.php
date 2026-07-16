<?php

namespace App\Console\Commands;

use App\Filament\Resources\AdministrationContracts\Schemas\AdministrationContractForm;
use App\Filament\Resources\RentalContracts\Schemas\RentalContractForm;
use App\Models\AdministrationContract;
use App\Models\BusinessOrigin;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\RentalContract;
use App\Models\RentalContractThird;
use App\Models\Third;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Carga la "Base de datos de Victoria" (inmuebles que Victoria entrega a
 * Serviarrendar) — un Excel compacto de 1 fila por contrato con: arrendatario,
 * inmueble, propietario, canon, administración, comisión y duración.
 *
 * Crea: Third (arrendatario), Third (propietario), Property,
 * AdministrationContract y RentalContract, todos etiquetados con el origen
 * de negocio "Victoria".
 *
 * Uso:
 *   php artisan victoria:import {archivo} --dry-run   (solo valida)
 *   php artisan victoria:import {archivo}              (crea los registros)
 */
class ImportVictoriaBaseDatos extends Command
{
    protected $signature = 'victoria:import {archivo} {--dry-run}';

    protected $description = 'Importa la base de datos de Victoria (inmuebles, propietarios, arrendatarios y contratos)';

    private const TIPO_MAP = [
        'local' => 'Local Comercial',
        'casa'  => 'Casa',
        'apto'  => 'Apartamento',
    ];

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! file_exists($ruta)) {
            $this->error("No se encontró el archivo: {$ruta}");
            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $origenVictoria = BusinessOrigin::where('nombre', 'Victoria')->first();
        if (! $origenVictoria) {
            $this->error('No existe el origen de negocio "Victoria". Corre primero la migración/seeder correspondiente.');
            return self::FAILURE;
        }

        $spreadsheet = IOFactory::load($ruta);
        $sheet       = $spreadsheet->getSheet(0);
        $highestRow  = $sheet->getHighestDataRow();

        $filas = [];

        for ($row = 3; $row <= $highestRow; $row++) {
            $get = fn (string $col) => trim((string) $sheet->getCell("{$col}{$row}")->getFormattedValue());

            $indicador = $get('B');
            if ($indicador === '') continue; // filas vacías finales

            $filas[] = [
                'fila'                 => $row,
                'arrendatario_doc'     => $get('C'),
                'arrendatario_nombre'  => $get('D'),
                'direccion'            => $get('E'),
                'tipo'                 => $get('F'),
                'canon'                => $this->parseMoney($get('G')),
                'admon'                => $this->parseMoney($get('H')),
                'comision'             => $this->parseMoney($get('I')),
                'fecha_inicio'         => $get('J'),
                'propietario_doc'      => $get('K'),
                'propietario_nombre'   => $get('L'),
                'duracion_texto'       => $get('M'),
                'activo'               => $get('N'),
            ];
        }

        $this->info('Total de filas encontradas: ' . count($filas));

        $resultados = [];
        foreach ($filas as $fila) {
            $resultados[] = $this->procesarFila($fila, $origenVictoria->id, $dryRun);
        }

        $this->table(
            ['Fila', 'Arrendatario', 'Inmueble', 'Estado', 'Motivo'],
            array_map(fn ($r) => [$r['fila'], $r['arrendatario_nombre'], $r['direccion'], $r['estado'], $r['motivo']], $resultados)
        );

        $validos = count(array_filter($resultados, fn ($r) => in_array($r['estado'], ['valido', 'creado'])));
        $errores = count(array_filter($resultados, fn ($r) => $r['estado'] === 'error'));

        $this->info(($dryRun ? 'Válidos' : 'Creados') . ": {$validos} · Errores: {$errores}");

        return self::SUCCESS;
    }

    private function procesarFila(array $f, int $origenId, bool $dryRun): array
    {
        $base = ['fila' => $f['fila'], 'arrendatario_nombre' => $f['arrendatario_nombre'], 'direccion' => $f['direccion']];

        if ($f['arrendatario_doc'] === '' || $f['direccion'] === '' || $f['tipo'] === '' || $f['propietario_doc'] === '') {
            return [...$base, 'estado' => 'error', 'motivo' => 'Faltan datos obligatorios (documento arrendatario, dirección, tipo o documento propietario).'];
        }

        $tipoNombre = self::TIPO_MAP[mb_strtolower($f['tipo'])] ?? null;
        $tipo = $tipoNombre ? PropertyType::where('nombre', $tipoNombre)->first() : null;
        if (! $tipo) {
            return [...$base, 'estado' => 'error', 'motivo' => "Tipo de inmueble \"{$f['tipo']}\" no reconocido."];
        }

        $fechaInicio = $this->parseFecha($f['fecha_inicio']);
        if (! $fechaInicio) {
            return [...$base, 'estado' => 'error', 'motivo' => "Fecha de inicio inválida: \"{$f['fecha_inicio']}\"."];
        }

        $meses = $this->parseMeses($f['duracion_texto']);
        if (! $meses) {
            return [...$base, 'estado' => 'error', 'motivo' => "Duración de contrato inválida: \"{$f['duracion_texto']}\"."];
        }

        if ($f['canon'] <= 0) {
            return [...$base, 'estado' => 'error', 'motivo' => 'Canon inválido o vacío.'];
        }

        if ($dryRun) {
            return [...$base, 'estado' => 'valido', 'motivo' => 'Listo para importar.'];
        }

        try {
            DB::transaction(function () use ($f, $origenId, $tipo, $fechaInicio, $meses) {
                $arrendatario = Third::firstOrCreate(
                    ['numero_documento' => $f['arrendatario_doc']],
                    $this->datosPersonaDesdeNombre($f['arrendatario_nombre'], $origenId, esArrendatario: true)
                );
                if (! $arrendatario->es_arrendatario) {
                    $arrendatario->update(['es_arrendatario' => true]);
                }

                $propietario = Third::firstOrCreate(
                    ['numero_documento' => $f['propietario_doc']],
                    $this->datosPersonaDesdeNombre($f['propietario_nombre'], $origenId, esArrendatario: false)
                );
                if (! $propietario->es_propietario) {
                    $propietario->update(['es_propietario' => true]);
                }

                $property = Property::create([
                    'property_type_id'   => $tipo->id,
                    'propietario_id'     => $propietario->id,
                    'business_origin_id' => $origenId,
                    'destinacion'        => $tipo->nombre === 'Local Comercial' ? 'comercial' : 'vivienda_familiar',
                    'direccion'          => $f['direccion'],
                    'estrato'            => 1,
                    'canon_arriendo'     => $f['canon'],
                    'cuota_administracion' => $f['admon'] ?: 0,
                    'disponible_arriendo'=> true,
                    'estado'             => 'arrendado',
                    'notas_internas'     => 'Migrado desde base de datos de Victoria.',
                ]);

                $canon = (float) $f['canon'];
                $comisionPorcentaje = $canon > 0 && $f['comision'] > 0
                    ? round(($f['comision'] / $canon) * 100, 2)
                    : 10;

                $adminContract = AdministrationContract::create([
                    'contract_template_id' => 1,
                    'property_id'          => $property->id,
                    'propietario_id'       => $propietario->id,
                    'tipo_contrato'        => 'administracion_arriendo',
                    'fecha_inicio'         => $fechaInicio,
                    'fecha_fin'            => $fechaInicio->copy()->addYear(),
                    'renovacion'           => 'automatica',
                    'canon_pactado'        => $canon,
                    'comision_porcentaje'  => $comisionPorcentaje,
                    'estado'               => 'activo',
                    'notas'                => 'Migrado desde base de datos de Victoria.',
                ]);
                AdministrationContractForm::copyClausesFromTemplate($adminContract);

                $rentalContract = RentalContract::create([
                    'property_id'                => $property->id,
                    'administration_contract_id' => $adminContract->id,
                    'contract_template_id'       => 2,
                    'arrendatario_id'            => $arrendatario->id,
                    'tipo'                       => 'vivienda_urbana',
                    'canon_mensual'              => $canon,
                    'deposito'                   => 0,
                    'fecha_inicio'               => $fechaInicio,
                    'fecha_fin'                  => $fechaInicio->copy()->addMonths($meses),
                    'duracion_meses'             => $meses,
                    'tipo_garantia'              => 'ninguna',
                    'estado'                     => 'activo',
                    'notas'                      => 'Migrado desde base de datos de Victoria.',
                ]);
                RentalContractForm::copyClausesFromTemplate($rentalContract);

                RentalContractThird::create([
                    'rental_contract_id' => $rentalContract->id,
                    'third_id'           => $arrendatario->id,
                    'rol'                => 'arrendatario',
                ]);
            });
        } catch (\Throwable $e) {
            return [...$base, 'estado' => 'error', 'motivo' => 'Error al guardar: ' . $e->getMessage()];
        }

        return [...$base, 'estado' => 'creado', 'motivo' => 'Creado correctamente.'];
    }

    private function datosPersonaDesdeNombre(string $nombreCompleto, int $origenId, bool $esArrendatario): array
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));
        $mitad  = (int) ceil(count($partes) / 2);

        return [
            'business_origin_id' => $origenId,
            'tipo_persona'       => 'natural',
            'tipo_documento'     => 'CC',
            'es_arrendatario'    => $esArrendatario,
            'es_propietario'     => ! $esArrendatario,
            'primer_nombre'      => implode(' ', array_slice($partes, 0, $mitad)) ?: null,
            'primer_apellido'    => implode(' ', array_slice($partes, $mitad)) ?: null,
            'nacionalidad'       => 'Colombiana',
        ];
    }

    private function parseMoney(string $valor): float
    {
        $limpio = preg_replace('/[^\d.]/', '', str_replace(',', '', $valor));
        return $limpio === '' ? 0 : (float) $limpio;
    }

    private function parseFecha(string $valor): ?Carbon
    {
        if ($valor === '') return null;
        try {
            return Carbon::createFromFormat('m/d/Y', $valor);
        } catch (\Throwable) {
            try {
                return Carbon::parse($valor);
            } catch (\Throwable) {
                return null;
            }
        }
    }

    private function parseMeses(string $texto): ?int
    {
        if (preg_match('/(\d+)/', $texto, $m)) {
            return (int) $m[1];
        }
        return null;
    }
}
