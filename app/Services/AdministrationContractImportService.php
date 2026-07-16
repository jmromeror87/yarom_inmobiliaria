<?php

namespace App\Services;

use App\Exports\AdministrationContracts\AdministrationContractTemplateExporter;
use App\Models\AdministrationContract;
use App\Models\Property;
use App\Models\Third;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Importa (o valida sin importar) contratos de administración desde el Excel
 * generado por AdministrationContractTemplateExporter. El propietario se
 * resuelve por número de documento y el inmueble por dirección exacta,
 * ambos deben existir previamente.
 */
class AdministrationContractImportService
{
    private array $filas = [];

    private const PLANTILLA_ID = 1; // "Contrato de Administración de Inmueble"

    private const ESTADOS_VALIDOS = [
        'borrador','enviado_propietario','en_revision','aprobado',
        'firmado','activo','terminado','cancelado',
    ];

    public function importFrom(string $filePath, bool $dryRun = false): array
    {
        $this->filas = [];

        $spreadsheet = IOFactory::load($filePath);
        $sheet       = $spreadsheet->getSheetByName('Contratos') ?? $spreadsheet->getSheet(0);

        $columnKeys = array_keys(AdministrationContractTemplateExporter::COLUMNS);
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
        $direccion = (string) ($data['inmueble_direccion'] ?? '');

        if ($documento === '') {
            $this->registrar($row, '', $direccion, 'error', 'Falta el número de documento del propietario.');
            return;
        }

        $propietario = Third::where('numero_documento', $documento)->where('es_propietario', true)->first();
        if (! $propietario) {
            $this->registrar($row, $documento, $direccion, 'error', 'No existe ningún Propietario con ese número de documento.');
            return;
        }

        if ($direccion === '') {
            $this->registrar($row, $documento, $direccion, 'error', 'Falta la dirección del inmueble.');
            return;
        }

        $candidatos = Property::where('propietario_id', $propietario->id)
            ->where('direccion', $direccion)
            ->get();

        if ($candidatos->isEmpty()) {
            $this->registrar($row, $documento, $direccion, 'error', 'No se encontró ningún inmueble con esa dirección exacta para este propietario.');
            return;
        }

        $idsConContratoVigente = AdministrationContract::whereIn('property_id', $candidatos->pluck('id'))
            ->whereIn('estado', ['activo', 'firmado', 'aprobado'])
            ->pluck('property_id')
            ->all();

        $disponibles = $candidatos->whereNotIn('id', $idsConContratoVigente);

        if ($disponibles->isEmpty()) {
            $this->registrar($row, $documento, $direccion, 'error', 'Este inmueble ya tiene un contrato de administración vigente — no se creó otro.');
            return;
        }

        $property = $disponibles->count() === 1
            ? $disponibles->first()
            // Varias unidades comparten la misma dirección/propietario (ej. edificio con
            // varios locales) — se desambigua por el canon, que viene de la misma fila
            // de origen que este contrato.
            : ($disponibles->first(fn ($p) => (float) $p->canon_arriendo === (float) ($data['canon_pactado'] ?? 0)) ?? $disponibles->first());

        $fechaInicio = $this->parsearFecha($data['fecha_inicio'] ?? null);
        if (! $fechaInicio) {
            $this->registrar($row, $documento, $direccion, 'error', 'Fecha de inicio vacía o con formato inválido (use AAAA-MM-DD).');
            return;
        }

        $canon = $data['canon_pactado'] ?? null;
        if (! $canon) {
            $this->registrar($row, $documento, $direccion, 'error', 'Falta el canon pactado.');
            return;
        }

        $comision = $data['comision_porcentaje'] ?? null;
        if ($comision === null || $comision === '') {
            $this->registrar($row, $documento, $direccion, 'error', 'Falta el porcentaje de comisión.');
            return;
        }

        if ($dryRun) {
            $this->registrar($row, $documento, $direccion, 'valido', 'Listo para importar.');
            return;
        }

        try {
            $this->createContract($property, $propietario, $fechaInicio, $canon, $comision, $data);
            $this->registrar($row, $documento, $direccion, 'creado', 'Contrato creado correctamente.');
        } catch (\Throwable $e) {
            $this->registrar($row, $documento, $direccion, 'error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    private function registrar(int $row, string $documento, string $direccion, string $estado, string $motivo): void
    {
        $this->filas[] = [
            'fila'                  => $row,
            'propietario_documento' => $documento,
            'inmueble_direccion'    => $direccion,
            'estado'                => $estado,
            'motivo'                => $motivo,
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

    private function createContract(Property $property, Third $propietario, Carbon $fechaInicio, $canon, $comision, array $data): AdministrationContract
    {
        $estado = in_array($data['estado'] ?? null, self::ESTADOS_VALIDOS) ? $data['estado'] : 'activo';

        return AdministrationContract::create([
            'contract_template_id' => self::PLANTILLA_ID,
            'property_id'          => $property->id,
            'propietario_id'       => $propietario->id,
            'tipo_contrato'        => 'administracion_arriendo',
            'fecha_inicio'         => $fechaInicio,
            'fecha_fin'            => $fechaInicio->copy()->addYear(),
            'renovacion'           => 'automatica',
            'canon_pactado'        => $canon,
            'comision_porcentaje'  => $comision,
            'estado'               => $estado,
            'notas'                => $data['notas'] ?: null,
        ]);
    }

    private function parsearFecha(mixed $valor): ?Carbon
    {
        if (empty($valor)) {
            return null;
        }

        try {
            if (is_numeric($valor)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($valor));
            }
            return Carbon::parse((string) $valor);
        } catch (\Throwable) {
            return null;
        }
    }
}
