<?php

namespace App\Services;

use App\Exports\Properties\PropertyTemplateExporter;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Third;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa (o valida sin importar) inmuebles desde el Excel generado por
 * PropertyTemplateExporter. El propietario se resuelve por número de documento
 * (debe existir previamente como Tercero).
 */
class PropertyImportService
{
    private array $filas = [];

    private const DESTINACION_POR_TIPO = [
        'local comercial' => 'comercial',
        'oficina'         => 'oficina',
        'bodega'          => 'bodega',
    ];

    public function importFrom(string $filePath, bool $dryRun = false): array
    {
        $this->filas = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheetByName('Inmuebles') ?? $spreadsheet->getSheet(0);

        $columnKeys = array_keys(PropertyTemplateExporter::COLUMNS);
        $columnMap  = [];
        $col = 1;
        foreach ($columnKeys as $key) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $columnMap[$letter] = $key;
            $col++;
        }

        $highestRow = $sheet->getHighestDataRow();

        for ($row = 2; $row <= $highestRow; $row++) {
            $data = [];
            foreach ($columnMap as $letter => $key) {
                $value = $sheet->getCell("{$letter}{$row}")->getValue();
                $data[$key] = is_string($value) ? trim($value) : $value;
            }

            if (empty(array_filter($data, fn ($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $this->procesarFila($row, $data, $dryRun);
        }

        return $this->resumen();
    }

    private function procesarFila(int $row, array $data, bool $dryRun): void
    {
        $documento = (string) ($data['propietario_documento'] ?? '');
        $direccion = (string) ($data['direccion'] ?? '');

        if ($documento === '') {
            $this->registrar($row, '', $direccion, 'error', 'Falta el número de documento del propietario.');
            return;
        }

        $propietario = Third::where('numero_documento', $documento)->where('es_propietario', true)->first();
        if (! $propietario) {
            $existeComoOtroRol = Third::where('numero_documento', $documento)->exists();
            $motivo = $existeComoOtroRol
                ? 'El tercero existe pero no está marcado como Propietario.'
                : 'No existe ningún Tercero con ese número de documento — cárguelo primero en el módulo de Terceros.';
            $this->registrar($row, $documento, $direccion, 'error', $motivo);
            return;
        }

        if ($direccion === '') {
            $this->registrar($row, $documento, $direccion, 'error', 'Falta la dirección del inmueble.');
            return;
        }

        $tipoNombre = (string) ($data['tipo_inmueble'] ?? '');
        $tipo = PropertyType::whereRaw('LOWER(nombre) = ?', [Str::lower($tipoNombre)])->first();
        if (! $tipo) {
            $this->registrar($row, $documento, $direccion, 'error', "Tipo de inmueble \"{$tipoNombre}\" no existe en el sistema (ver Configuración > Tipos de inmueble).");
            return;
        }

        if ($dryRun) {
            $this->registrar($row, $documento, $direccion, 'valido', 'Listo para importar.');
            return;
        }

        try {
            $this->createProperty($data, $propietario, $tipo);
            $this->registrar($row, $documento, $direccion, 'creado', 'Inmueble creado correctamente.');
        } catch (\Throwable $e) {
            $this->registrar($row, $documento, $direccion, 'error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    private function registrar(int $row, string $documento, string $direccion, string $estado, string $motivo): void
    {
        $this->filas[] = [
            'fila'                   => $row,
            'propietario_documento'  => $documento,
            'direccion'              => $direccion,
            'estado'                 => $estado,
            'motivo'                 => $motivo,
        ];
    }

    private function resumen(): array
    {
        $porEstado = fn (string $estado) => array_values(array_filter($this->filas, fn ($f) => $f['estado'] === $estado));

        return [
            'filas'   => $this->filas,
            'creados' => $porEstado('creado'),
            'validos' => $porEstado('valido'),
            'errores' => $porEstado('error'),
        ];
    }

    private function createProperty(array $data, Third $propietario, PropertyType $tipo): Property
    {
        $municipioId    = $this->resolveMunicipioId($data['municipio'] ?? null, $data['departamento'] ?? null);
        $departamentoId = $this->resolveDepartamentoId($data['departamento'] ?? null);

        $destinacion = self::DESTINACION_POR_TIPO[Str::lower($tipo->nombre)] ?? 'vivienda_familiar';

        $estado = in_array($data['estado'] ?? null, [
            'en_captacion','documentos_pendientes','disponible','arrendado',
            'en_venta','vendido','en_mantenimiento','inactivo',
        ]) ? $data['estado'] : 'en_captacion';

        return Property::create([
            'property_type_id'   => $tipo->id,
            'propietario_id'     => $propietario->id,
            'destinacion'        => $destinacion,
            'direccion'          => $data['direccion'],
            'barrio'             => $data['barrio'] ?: null,
            'municipio_id'       => $municipioId,
            'departamento_id'    => $departamentoId,
            'estrato'            => $data['estrato'] ?: 1,
            'area_construida_m2' => $data['area_construida_m2'] ?: null,
            'area_privada_m2'    => $data['area_privada_m2'] ?: null,
            'habitaciones'       => $data['habitaciones'] ?: 0,
            'banos'              => $data['banos'] ?: 0,
            'garajes'            => $data['garajes'] ?: 0,
            'canon_arriendo'     => $data['canon_arriendo'] ?: null,
            'cuota_administracion' => $data['cuota_administracion'] ?: 0,
            'disponible_arriendo'  => $this->toBool($data['disponible_arriendo'] ?? null, default: true),
            'disponible_venta'     => $this->toBool($data['disponible_venta'] ?? null, default: false),
            'estado'               => $estado,
            'notas_internas'       => $data['notas_internas'] ?: null,
        ]);
    }

    private function toBool(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }
        return Str::upper((string) $value) === 'SI';
    }

    private function resolveDepartamentoId(?string $nombre): ?int
    {
        if (! $nombre) return null;
        return Departamento::whereRaw('LOWER(nombre) = ?', [Str::lower(trim($nombre))])->value('id');
    }

    private function resolveMunicipioId(?string $nombre, ?string $departamentoNombre): ?int
    {
        if (! $nombre) return null;
        $query = Municipio::whereRaw('LOWER(nombre) = ?', [Str::lower(trim($nombre))]);
        if ($departamentoNombre) {
            $depId = $this->resolveDepartamentoId($departamentoNombre);
            if ($depId) {
                $query->where('departamento_id', $depId);
            }
        }
        return $query->value('id');
    }
}
