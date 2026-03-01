<?php

namespace App\Filament\Resources\Solicituds\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;

class SolicitudForm
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
                    ->unique(table: 'solicitudes', column: 'numero_documento', ignoreRecord: true)
                    ->rule(function (Get $get) {
                        return match ($get('tipo_documento')) {
                            'dni' => 'regex:/^[0-9]{8}[A-Za-z]$/',
                            'nie' => 'regex:/^[XYZ][0-9]{7}[A-Za-z]$/i',
                            'pasaporte' => 'regex:/^[A-Za-z0-9]{5,20}$/',
                            default => 'string',
                        };
                    })
                    ->helperText('DNI: 12345678Z · NIE: X1234567L · Pasaporte: alfanumérico'),

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
                    ->maxLength(15)
                    ->rule('regex:/^[A-Za-z0-9\- ]{3,15}$/'),

                TextInput::make('pais')
                    ->required()
                    ->maxLength(80),

                Toggle::make('tiene_hijos')
                    ->required()
                    ->live(),

                TextInput::make('numero_hijos')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(20)
                    ->visible(fn (Get $get) => (bool) $get('tiene_hijos'))
                    ->required(fn (Get $get) => (bool) $get('tiene_hijos')),

                Toggle::make('hijo_down')
                    ->required()
                    ->live(),

                DatePicker::make('fecha_nacimiento_hijo_down')
                    ->visible(fn (Get $get) => (bool) $get('hijo_down'))
                    ->required(fn (Get $get) => (bool) $get('hijo_down')),

                Select::make('tipo_socio')
                    ->required()
                    ->options([
                        'honorario' => 'Socio Honorario',
                        'colaborador' => 'Socio Colaborador',
                        'numerario' => 'Socio Numerario',
                    ]),

                TextInput::make('estado')
                    ->required()
                    ->default('pendiente')
                    ->disabled()
                    ->dehydrated(),

                Textarea::make('motivo_rechazo')
                    ->columnSpanFull()
                    ->visible(fn (Get $get) => $get('estado') === 'rechazada')
                    ->disabled(), // rellenamos desde la acción Rechazar

                TextInput::make('procesada_por')
                    ->numeric()
                    ->disabled(),

                DateTimePicker::make('procesada_en')
                    ->disabled(),
            ]);
    }
}