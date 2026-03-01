<?php

namespace App\Filament\Resources\Socios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
