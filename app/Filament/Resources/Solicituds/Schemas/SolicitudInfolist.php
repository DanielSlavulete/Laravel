<?php

namespace App\Filament\Resources\Solicituds\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SolicitudInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('apellidos'),
                TextEntry::make('fecha_nacimiento')
                    ->date(),
                TextEntry::make('telefono'),
                TextEntry::make('tipo_documento'),
                TextEntry::make('numero_documento'),
                TextEntry::make('direccion'),
                TextEntry::make('ciudad'),
                TextEntry::make('provincia'),
                TextEntry::make('codigo_postal'),
                TextEntry::make('pais'),
                IconEntry::make('tiene_hijos')
                    ->boolean(),
                TextEntry::make('numero_hijos')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('hijo_down')
                    ->boolean(),
                TextEntry::make('fecha_nacimiento_hijo_down')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('tipo_socio'),
                TextEntry::make('estado'),
                TextEntry::make('motivo_rechazo')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('procesada_por')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('procesada_en')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
