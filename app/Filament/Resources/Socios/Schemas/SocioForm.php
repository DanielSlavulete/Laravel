<?php

namespace App\Filament\Resources\Socios\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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

                Hidden::make('solicitud_id')
                    ->default(null),

                Hidden::make('fecha_alta')
                    ->default(now()),

                TextInput::make('nombre')
                    ->required()
                    ->maxLength(100)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                TextInput::make('apellidos')
                    ->required()
                    ->maxLength(150)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                TextInput::make('email')
                    ->label('Correo electrónico')
                    ->email()
                    ->required()
                    ->maxLength(150)
                    ->unique(table: 'socios', column: 'email', ignoreRecord: true)
                    ->dehydrateStateUsing(fn ($state) => strtolower(trim((string) $state))),

                DatePicker::make('fecha_nacimiento')
                    ->required()
                    ->maxDate(now()->subYears(18)),

                TextInput::make('telefono')
                    ->required()
                    ->tel()
                    ->maxLength(9)
                    ->minLength(9)
                    ->rule('regex:/^[6789][0-9]{8}$/'),

                Select::make('tipo_documento')
                    ->required()
                    ->options([
                        'dni' => 'DNI',
                        'nie' => 'NIE',
                        'pasaporte' => 'Pasaporte',
                    ])
                    ->live(),

                TextInput::make('numero_documento')
                    ->label('Documento')
                    ->required()
                    ->maxLength(20)
                    ->unique(table: 'socios', column: 'numero_documento', ignoreRecord: true)
                    ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))
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
                    ->maxLength(200)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                TextInput::make('ciudad')
                    ->required()
                    ->maxLength(100)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                TextInput::make('provincia')
                    ->required()
                    ->maxLength(100)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                TextInput::make('codigo_postal')
                    ->label('Código postal')
                    ->required()
                    ->maxLength(15)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                TextInput::make('pais')
                    ->required()
                    ->maxLength(80)
                    ->dehydrateStateUsing(fn ($state) => trim((string) $state)),

                Toggle::make('tiene_hijos')
                    ->label('¿Tiene hijos?')
                    ->required()
                    ->default(false)
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ? true : false)
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state) {
                            $set('numero_hijos', 0);
                            $set('hijo_down', false);
                            $set('fecha_nacimiento_hijo_down', null);
                        }
                }),

                TextInput::make('numero_hijos')
                    ->label('Número de hijos')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->default(0)
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos')),

                Toggle::make('hijo_down')
                    ->label('¿Algún hijo con síndrome de Down?')
                    ->default(false)
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->live()
                    ->dehydrateStateUsing(fn ($state) => $state ? true : false)
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state) {
                            $set('fecha_nacimiento_hijo_down', null);
                        }
                    }),

                DatePicker::make('fecha_nacimiento_hijo_down')
                    ->label('Fecha de nacimiento del hijo/a con síndrome de Down')
                    ->maxDate(now())
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos') && (bool) $get('hijo_down'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos') && (bool) $get('hijo_down')),

                Select::make('tipo_socio')
                    ->label('Tipo de socio')
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