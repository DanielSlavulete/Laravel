<?php

namespace App\Filament\Resources\Cuotas\Pages;

use App\Filament\Resources\Cuotas\CuotaResource;
use App\Models\Cuota;
use App\Models\Socio;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListCuotas extends ListRecords
{
    protected static string $resource = CuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear cuota'),

            Action::make('importar')
                ->label('Importar CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('archivo')
                        ->label('Archivo CSV')
                        ->required()
                        ->disk('local')
                        ->directory('imports/cuotas')
                        ->acceptedFileTypes([
                            'text/csv',
                            'text/plain',
                            'application/csv',
                            'application/vnd.ms-excel',
                        ]),
                ])
                ->action(function (array $data) {
                    $path = $data['archivo'] ?? null;

                    if (! $path || ! Storage::disk('local')->exists($path)) {
                        Notification::make()
                            ->title('No se pudo leer el archivo CSV')
                            ->danger()
                            ->send();

                        return;
                    }

                    $fullPath = Storage::disk('local')->path($path);
                    $handle = fopen($fullPath, 'r');

                    if (! $handle) {
                        Notification::make()
                            ->title('No se pudo abrir el archivo CSV')
                            ->danger()
                            ->send();

                        return;
                    }

                    $header = fgetcsv($handle, 0, ';');

                    if (! $header) {
                        fclose($handle);

                        Notification::make()
                            ->title('El archivo CSV está vacío o no tiene cabecera')
                            ->danger()
                            ->send();

                        return;
                    }

                    $header = array_map(
                        fn ($value) => trim(mb_strtolower((string) $value)),
                        $header
                    );

                    $requiredColumns = ['email', 'anio', 'pagado', 'cuantia', 'fecha_pago'];

                    foreach ($requiredColumns as $column) {
                        if (! in_array($column, $header, true)) {
                            fclose($handle);

                            Notification::make()
                                ->title("Falta la columna obligatoria: {$column}")
                                ->danger()
                                ->send();

                            return;
                        }
                    }

                    $created = 0;
                    $skipped = 0;

                    while (($row = fgetcsv($handle, 0, ';')) !== false) {
                        if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                            continue;
                        }

                        $rowData = [];

                        foreach ($header as $index => $columnName) {
                            $rowData[$columnName] = isset($row[$index]) ? trim((string) $row[$index]) : null;
                        }

                        $email = $rowData['email'] ?? null;
                        $anio = $rowData['anio'] ?? null;
                        $pagadoRaw = $rowData['pagado'] ?? null;
                        $cuantiaRaw = $rowData['cuantia'] ?? null;
                        $fechaPagoRaw = $rowData['fecha_pago'] ?? null;

                        if (! $email || ! $anio || $pagadoRaw === null || $pagadoRaw === '') {
                            $skipped++;
                            continue;
                        }

                        $socio = Socio::query()->where('email', $email)->first();

                        if (! $socio) {
                            $skipped++;
                            continue;
                        }

                        $pagado = $this->normalizeBoolean($pagadoRaw);

                        if ($pagado === null) {
                            $skipped++;
                            continue;
                        }

                        $cuantia = null;
                        $fechaPago = null;

                        if ($pagado) {
                            if ($cuantiaRaw === null || $cuantiaRaw === '' || $fechaPagoRaw === null || $fechaPagoRaw === '') {
                                $skipped++;
                                continue;
                            }

                            $cuantia = str_replace(',', '.', $cuantiaRaw);
                            $fechaPago = $this->parseDate($fechaPagoRaw);

                            if ($fechaPago === null) {
                                $skipped++;
                                continue;
                            }
                        }

                        $cuotaExistente = Cuota::query()
                            ->where('socio_id', $socio->id)
                            ->where('anio', (int) $anio)
                            ->first();

                        if ($cuotaExistente) {
                            $skipped++;
                            continue;
                        }

                        Cuota::create([
                            'socio_id' => $socio->id,
                            'anio' => (int) $anio,
                            'pagado' => $pagado,
                            'cuantia' => $cuantia,
                            'fecha_pago' => $fechaPago,
                        ]);

                        $created++;
                    }

                    fclose($handle);

                    Notification::make()
                        ->title('Importación completada')
                        ->body("Creadas: {$created} | Omitidas: {$skipped}")
                        ->success()
                        ->send();
                }),

            Action::make('exportar')
                ->label('Exportar')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Select::make('tipo')
                        ->label('¿Qué quieres exportar?')
                        ->options([
                            'todo' => 'Todo',
                            'filtrado' => 'Solo resultados filtrados',
                        ])
                        ->required()
                        ->default('todo'),
                ])
                ->action(function (array $data) {
                    $filename = 'cuotas-' . now()->format('Y-m-d_H-i-s') . '.csv';

                    return response()->streamDownload(function () use ($data) {
                        $handle = fopen('php://output', 'w');

                        fputcsv($handle, [
                            'ID',
                            'ID Socio',
                            'Nombre',
                            'Apellidos',
                            'Año',
                            'Pagada',
                            'Cuantía',
                            'Fecha pago',
                            'Creada',
                            'Actualizada',
                        ], ';');

                        if ($data['tipo'] === 'filtrado') {
                            $query = $this->getFilteredTableQuery()->with('socio');
                        } else {
                            $query = Cuota::query()->with('socio');
                        }

                        $query->orderBy('id')->chunk(200, function ($cuotas) use ($handle) {
                            foreach ($cuotas as $cuota) {
                                fputcsv($handle, [
                                    $cuota->id,
                                    $cuota->socio_id,
                                    $cuota->socio?->nombre,
                                    $cuota->socio?->apellidos,
                                    $cuota->anio,
                                    $cuota->pagado ? 'Sí' : 'No',
                                    $cuota->cuantia,
                                    $cuota->fecha_pago,
                                    $cuota->created_at,
                                    $cuota->updated_at,
                                ], ';');
                            }
                        });

                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),
        ];
    }

    private function normalizeBoolean(string $value): ?bool
    {
        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            '1', 'true', 'sí', 'si', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}