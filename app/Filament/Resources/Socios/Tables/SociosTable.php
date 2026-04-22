<?php

namespace App\Filament\Resources\Socios\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SociosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->copyable(),

                TextColumn::make('solicitud_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('nombre')
                    ->searchable(),

                TextColumn::make('apellidos')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Correo electrónico')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('telefono')
                    ->searchable(),

                TextColumn::make('tipo_documento')
                    ->searchable(),

                TextColumn::make('numero_documento')
                    ->searchable(),

                TextColumn::make('direccion')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('ciudad')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('provincia')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('codigo_postal')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('pais')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('tiene_hijos')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('numero_hijos')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('hijo_down')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('fecha_nacimiento_hijo_down')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tipo_socio')
                    ->searchable(),

                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'activo' => 'success',
                        'inactivo' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('fecha_alta')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('cuota_pagada_actual')
                    ->label('Cuota ' . now()->year)
                    ->boolean()
                    ->getStateUsing(function ($record) {
                        return $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;
                    }),

                TextColumn::make('cuantia_cuota_actual')
                    ->label('Cuantía')
                    ->getStateUsing(function ($record) {
                        return $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('cuantia');
                    })
                    ->formatStateUsing(
                        fn ($state) => $state !== null
                            ? number_format((float) $state, 2, ',', '.') . ' €'
                            : null
                    )
                    ->toggleable(),

                TextColumn::make('fecha_pago_cuota_actual')
                    ->label('Fecha pago')
                    ->getStateUsing(function ($record) {
                        return optional(
                            $record->cuotas()
                                ->where('anio', now()->year)
                                ->first()
                        )->fecha_pago;
                    })
                    ->date(),

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
                SelectFilter::make('estado')
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ]),

                SelectFilter::make('tipo_socio')
                    ->options([
                        'numerario' => 'Numerario',
                        'colaborador' => 'Colaborador',
                    ]),

                TernaryFilter::make('tiene_hijos')
                    ->label('Tiene hijos'),

                SelectFilter::make('cuota_actual')
                    ->label('Cuota ' . now()->year)
                    ->options([
                        'pagada' => 'Pagada',
                        'no_pagada' => 'No pagada',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;
                        $anio = now()->year;

                        if ($value === 'pagada') {
                            return $query->whereHas('cuotas', function (Builder $q) use ($anio) {
                                $q->where('anio', $anio)
                                    ->whereRaw('pagado = true');
                            });
                        }

                        if ($value === 'no_pagada') {
                            return $query->where(function (Builder $q) use ($anio) {
                                $q->whereDoesntHave('cuotas', function (Builder $sub) use ($anio) {
                                    $sub->where('anio', $anio);
                                })->orWhereHas('cuotas', function (Builder $sub) use ($anio) {
                                    $sub->where('anio', $anio)
                                        ->whereRaw('pagado = false');
                                });
                            });
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                Action::make('gestionar_cuota_actual')
                    ->label(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada ? 'Marcar no pagada' : 'Marcar pagada';
                    })
                    ->icon(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada
                            ? 'heroicon-o-x-circle'
                            : 'heroicon-o-check-circle';
                    })
                    ->color(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada ? 'danger' : 'success';
                    })
                    ->requiresConfirmation(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada;
                    })
                    ->modalHeading(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada
                            ? 'Marcar cuota como no pagada'
                            : 'Registrar pago de cuota';
                    })
                    ->modalDescription(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada
                            ? '¿Seguro que quieres marcar la cuota del año actual como no pagada?'
                            : 'Introduce la cuantía y la fecha real en la que se realizó el pago.';
                    })
                    ->modalSubmitActionLabel(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        return $pagada ? 'Sí, marcar no pagada' : 'Guardar pago';
                    })
                    ->form(function ($record) {
                        $pagada = $record->cuotas()
                            ->where('anio', now()->year)
                            ->value('pagado') ?? false;

                        if ($pagada) {
                            return [];
                        }

                        return [
                            TextInput::make('cuantia')
                                ->label('Cuantía (€)')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->step('0.01'),

                            DatePicker::make('fecha_pago')
                                ->label('Fecha de pago')
                                ->required()
                                ->default(now())
                                ->native(false),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        $anio = now()->year;

                        $cuota = $record->cuotas()
                            ->where('anio', $anio)
                            ->first();

                        if (! $cuota) {
                            $cuotaId = DB::table('cuotas')->insertGetId([
                                'socio_id' => $record->id,
                                'anio' => $anio,
                                'pagado' => DB::raw('false'),
                                'cuantia' => null,
                                'fecha_pago' => null,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $cuota = $record->cuotas()
                                ->where('id', $cuotaId)
                                ->first();
                        }

                        if ($cuota->pagado) {
                            DB::table('cuotas')
                                ->where('id', $cuota->id)
                                ->update([
                                    'pagado' => DB::raw('false'),
                                    'cuantia' => null,
                                    'fecha_pago' => null,
                                    'updated_at' => now(),
                                ]);

                            return;
                        }

                        DB::table('cuotas')
                            ->where('id', $cuota->id)
                            ->update([
                                'pagado' => DB::raw('true'),
                                'cuantia' => $data['cuantia'],
                                'fecha_pago' => $data['fecha_pago'],
                                'updated_at' => now(),
                            ]);
                    }),

                Action::make('toggle_estado')
                    ->label(fn ($record) => $record->estado === 'activo' ? 'Desactivar' : 'Activar')
                    ->icon(fn ($record) => $record->estado === 'activo'
                        ? 'heroicon-o-no-symbol'
                        : 'heroicon-o-check-circle'
                    )
                    ->color(fn ($record) => $record->estado === 'activo' ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->estado === 'activo'
                        ? 'Desactivar socio'
                        : 'Activar socio'
                    )
                    ->modalDescription(fn ($record) => $record->estado === 'activo'
                        ? '¿Seguro que quieres desactivar este socio?'
                        : '¿Seguro que quieres activar este socio?'
                    )
                    ->modalSubmitActionLabel(fn ($record) => $record->estado === 'activo'
                        ? 'Sí, desactivar'
                        : 'Sí, activar'
                    )
                    ->action(function ($record) {
                        $record->update([
                            'estado' => $record->estado === 'activo' ? 'inactivo' : 'activo',
                        ]);
                    }),

                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar socio')
                    ->modalDescription('¿Seguro que quieres eliminar este socio? Se eliminarán también sus cuotas (cascade).')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->toolbarActions([
                Action::make('recargar')
                    ->label('Recargar tabla')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->action(fn () => null),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}