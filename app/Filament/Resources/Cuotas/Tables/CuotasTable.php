<?php

namespace App\Filament\Resources\Cuotas\Tables;

use App\Models\Cuota;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CuotasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('socio.nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('socio.apellidos')
                    ->label('Apellidos')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('socio_id')
                    ->label('ID Socio')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('anio')
                    ->label('Año')
                    ->numeric(thousandsSeparator: '', decimalPlaces: 0)
                    ->sortable(),

                IconColumn::make('pagado')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('fecha_pago')
                    ->label('Fecha pago')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar cuota')
                    ->modalDescription('¿Seguro que quieres eliminar esta cuota?')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->toolbarActions([

                Action::make('generar_cuotas')
                    ->label('Generar cuotas ' . now()->year)
                    ->icon('heroicon-o-calendar')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Generar cuotas anuales')
                    ->modalDescription('Se generarán las cuotas del año actual para los socios que aún no la tengan.')
                    ->modalSubmitActionLabel('Generar')
                    ->action(function () {

                        \Artisan::call('cuotas:generar-anuales');

                        Notification::make()
                            ->title('Cuotas generadas correctamente')
                            ->success()
                            ->send();
                    }),

                Action::make('exportar_cuotas_csv')
                    ->label('Exportar cuotas CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {

                        $filename = 'cuotas-' . now()->format('Y-m-d_H-i-s') . '.csv';

                        return response()->streamDownload(function () {

                            $handle = fopen('php://output', 'w');

                            fputcsv($handle, [
                                'ID',
                                'ID Socio',
                                'Nombre',
                                'Apellidos',
                                'Año',
                                'Pagada',
                                'Fecha pago',
                                'Creada',
                                'Actualizada',
                            ], ';');

                            Cuota::query()
                                ->with('socio')
                                ->orderBy('id')
                                ->chunk(200, function ($cuotas) use ($handle) {

                                    foreach ($cuotas as $cuota) {

                                        fputcsv($handle, [
                                            $cuota->id,
                                            $cuota->socio_id,
                                            $cuota->socio?->nombre,
                                            $cuota->socio?->apellidos,
                                            $cuota->anio,
                                            $cuota->pagado ? 'Sí' : 'No',
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

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}