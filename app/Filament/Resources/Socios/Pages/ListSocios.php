<?php

namespace App\Filament\Resources\Socios\Pages;

use App\Filament\Resources\Socios\SocioResource;
use App\Models\Socio;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListSocios extends ListRecords
{
    protected static string $resource = SocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear socio'),

            Action::make('importar')
                ->label('Importar CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('archivo')
                        ->label('Archivo CSV')
                        ->required()
                        ->disk('local')
                        ->directory('imports/socios')
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

                    $header = array_map(function ($value) {
                        return trim(mb_strtolower((string) $value));
                    }, $header);

                    $requiredColumns = [
                        'nombre',
                        'apellidos',
                        'email',
                        'tipo_documento',
                        'numero_documento',
                        'tipo_socio',
                    ];

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
                    $updated = 0;
                    $skipped = 0;

                    while (($row = fgetcsv($handle, 0, ';')) !== false) {
                        if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                            continue;
                        }

                        $rowData = [];

                        foreach ($header as $index => $columnName) {
                            $rowData[$columnName] = isset($row[$index]) ? trim((string) $row[$index]) : null;
                        }

                        $nombre = $rowData['nombre'] ?? null;
                        $apellidos = $rowData['apellidos'] ?? null;
                        $email = $rowData['email'] ?? null;
                        $tipoDocumento = $rowData['tipo_documento'] ?? null;
                        $numeroDocumento = $rowData['numero_documento'] ?? null;
                        $tipoSocio = $rowData['tipo_socio'] ?? null;

                        if (! $nombre || ! $apellidos || ! $email || ! $tipoDocumento || ! $numeroDocumento || ! $tipoSocio) {
                            $skipped++;
                            continue;
                        }

                        $existing = Socio::query()
                            ->where('numero_documento', $numeroDocumento)
                            ->first();

                        Socio::updateOrCreate(
                            [
                                'numero_documento' => $numeroDocumento,
                            ],
                            [
                                'nombre' => $nombre,
                                'apellidos' => $apellidos,
                                'email' => $email,
                                'fecha_nacimiento' => $this->nullIfEmpty($rowData['fecha_nacimiento'] ?? null),
                                'telefono' => $this->nullIfEmpty($rowData['telefono'] ?? null),
                                'tipo_documento' => $tipoDocumento,
                                'direccion' => $this->nullIfEmpty($rowData['direccion'] ?? null),
                                'ciudad' => $this->nullIfEmpty($rowData['ciudad'] ?? null),
                                'provincia' => $this->nullIfEmpty($rowData['provincia'] ?? null),
                                'codigo_postal' => $this->nullIfEmpty($rowData['codigo_postal'] ?? null),
                                'pais' => $this->nullIfEmpty($rowData['pais'] ?? null),
                                'tiene_hijos' => $this->normalizeBoolean($rowData['tiene_hijos'] ?? '0') ?? false,
                                'numero_hijos' => (int) ($rowData['numero_hijos'] ?? 0),
                                'hijo_down' => $this->normalizeBoolean($rowData['hijo_down'] ?? '0') ?? false,
                                'fecha_nacimiento_hijo_down' => $this->nullIfEmpty($rowData['fecha_nacimiento_hijo_down'] ?? null),
                                'tipo_socio' => $tipoSocio,
                                'estado' => $this->nullIfEmpty($rowData['estado'] ?? null) ?? 'activo',
                                'fecha_alta' => $this->nullIfEmpty($rowData['fecha_alta'] ?? null) ?? now(),
                            ]
                        );

                        if ($existing) {
                            $updated++;
                        } else {
                            $created++;
                        }
                    }

                    fclose($handle);

                    Notification::make()
                        ->title('Importación completada')
                        ->body("Creados: {$created} | Actualizados: {$updated} | Omitidos: {$skipped}")
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
                    $filename = 'socios-' . now()->format('Y-m-d_H-i-s') . '.csv';

                    return response()->streamDownload(function () use ($data) {
                        $handle = fopen('php://output', 'w');

                        fputcsv($handle, [
                            'ID',
                            'Nombre',
                            'Apellidos',
                            'Correo',
                            'Teléfono',
                            'Tipo documento',
                            'Documento',
                            'Ciudad',
                            'Provincia',
                            'Tipo socio',
                            'Estado',
                            'Tiene hijos',
                            'Número hijos',
                            'Hijo con síndrome Down',
                            'Fecha nacimiento hijo Down',
                            'Fecha alta',
                        ], ';');

                        if ($data['tipo'] === 'filtrado') {
                            $query = $this->getFilteredTableQuery();
                        } else {
                            $query = Socio::query();
                        }

                        $query->orderBy('id')->chunk(200, function ($socios) use ($handle) {
                            foreach ($socios as $socio) {
                                fputcsv($handle, [
                                    $socio->id,
                                    $socio->nombre,
                                    $socio->apellidos,
                                    $socio->email,
                                    $socio->telefono,
                                    $socio->tipo_documento,
                                    $socio->numero_documento,
                                    $socio->ciudad,
                                    $socio->provincia,
                                    $socio->tipo_socio,
                                    $socio->estado,
                                    $socio->tiene_hijos ? 'Sí' : 'No',
                                    $socio->numero_hijos,
                                    $socio->hijo_down ? 'Sí' : 'No',
                                    $socio->fecha_nacimiento_hijo_down,
                                    $socio->fecha_alta,
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

    private function normalizeBoolean(?string $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        $normalized = mb_strtolower(trim($value));

        return match ($normalized) {
            '1', 'true', 'sí', 'si', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }

    private function nullIfEmpty(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}