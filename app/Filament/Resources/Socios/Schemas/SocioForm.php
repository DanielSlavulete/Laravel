<?php

namespace App\Filament\Resources\Socios\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class SocioForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('nombre')
                    ->required()
                    ->maxLength(100),

                TextInput::make('apellidos')
                    ->required()
                    ->maxLength(150),

                DatePicker::make('fecha_nacimiento')
                    ->required(),

                TextInput::make('telefono')
                    ->required()
                    ->tel()
                    ->maxLength(30)
                    ->rule('regex:/^[0-9+\s()-]{7,30}$/'),

                Select::make('tipo_documento')
                    ->required()
                    ->options([
                        'dni' => 'DNI',
                        'nie' => 'NIE',
                        'pasaporte' => 'Pasaporte',
                    ])
                    ->live(),

                TextInput::make('numero_documento')
                    ->required()
                    ->maxLength(20)
                    ->unique(table: 'socios', column: 'numero_documento', ignoreRecord: true)
                    ->rule(function (Get $get) {
                        return match ($get('tipo_documento')) {
                            'dni' => 'regex:/^[0-9]{8}[A-Za-z]$/',
                            'nie' => 'regex:/^[XYZ][0-9]{7}[A-Za-z]$/i',
                            'pasaporte' => 'regex:/^[A-Za-z0-9]{5,20}$/',
                            default => 'string',
                        };
                    }),

                TextInput::make('direccion')
                    ->required()
                    ->maxLength(200),

                TextInput::make('ciudad')
                    ->required()
                    ->maxLength(100),

                TextInput::make('provincia')
                    ->required()
                    ->maxLength(100),

                TextInput::make('codigo_postal')
                    ->required()
                    ->maxLength(15),

                TextInput::make('pais')
                    ->required()
                    ->maxLength(80),

                Toggle::make('tiene_hijos')
                    ->label('¿Tiene hijos?')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state) {
                            // si NO tiene hijos, limpiamos dependientes
                            $set('numero_hijos', null);
                            $set('hijo_down', false);
                            $set('fecha_nacimiento_hijo_down', null);
                        }
                    }),

                TextInput::make('numero_hijos')
                    ->label('Número de hijos')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos')),

                Toggle::make('hijo_down')
                    ->label('¿Algún hijo con síndrome de Down?')
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->live()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state) {
                            $set('fecha_nacimiento_hijo_down', null);
                        }
                    }),

                DatePicker::make('fecha_nacimiento_hijo_down')
                    ->label('Fecha nacimiento hijo/a con Down')
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos') && (bool) $get('hijo_down'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos') && (bool) $get('hijo_down')),

                Select::make('tipo_socio')
                    ->required()
                    ->options([
                        'honorario' => 'Socio Honorario',
                        'colaborador' => 'Socio Colaborador',
                        'numerario' => 'Socio Numerario',
                    ]),

                Select::make('estado')
                    ->required()
                    ->options([
                        'activo' => 'Activo',
                        'inactivo' => 'Inactivo',
                    ])
                    ->default('activo'),

            ]);
    }
}