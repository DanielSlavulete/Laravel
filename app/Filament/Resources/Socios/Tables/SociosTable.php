<?php

namespace App\Filament\Resources\Socios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class SociosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('solicitud_id')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('nombre')
                    ->searchable(),

                TextColumn::make('apellidos')
                    ->searchable(),

                TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable(),

                TextColumn::make('telefono')
                    ->searchable(),

                TextColumn::make('tipo_documento')
                    ->searchable(),

                TextColumn::make('numero_documento')
                    ->searchable(),

                TextColumn::make('direccion')
                    ->searchable(),

                TextColumn::make('ciudad')
                    ->searchable(),

                TextColumn::make('provincia')
                    ->searchable(),

                TextColumn::make('codigo_postal')
                    ->searchable(),

                TextColumn::make('pais')
                    ->searchable(),

                IconColumn::make('tiene_hijos')
                    ->boolean(),

                TextColumn::make('numero_hijos')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('hijo_down')
                    ->boolean(),

                TextColumn::make('fecha_nacimiento_hijo_down')
                    ->date()
                    ->sortable(),

                TextColumn::make('tipo_socio')
                    ->searchable(),

                TextColumn::make('estado')
                    ->searchable(),

                TextColumn::make('fecha_alta')
                    ->dateTime()
                    ->sortable(),


                ToggleColumn::make('cuota_pagada')
                    ->label('Cuota ' . now()->year)
                    ->getStateUsing(function ($record) {
                        $anio = now()->year;

                        $cuota = $record->cuotas()
                            ->where('anio', $anio)
                            ->first();

                        return (bool) optional($cuota)->pagado;
                    })
                    ->updateStateUsing(function ($record, bool $state) {
                        $anio = now()->year;

                        $cuota = $record->cuotas()->firstOrCreate(['anio' => $anio]);

                        $cuota->pagado = $state;
                        $cuota->fecha_pago = $state ? now() : null;
                        $cuota->save();

                        return $state;
                    }),


                TextColumn::make('fecha_pago_cuota_actual')
                    ->label('Fecha pago')
                    ->getStateUsing(function ($record) {
                        $anio = now()->year;

                        return optional(
                            $record->cuotas()->where('anio', $anio)->first()
                        )->fecha_pago;
                    })
                    ->dateTime(),

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
                    ->modalHeading('Eliminar socio')
                    ->modalDescription('¿Seguro que quieres eliminar este socio? Se eliminarán también sus cuotas (cascade).')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}