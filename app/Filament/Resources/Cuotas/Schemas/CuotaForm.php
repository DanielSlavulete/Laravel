<?php

namespace App\Filament\Resources\Cuotas\Schemas;

use App\Models\Socio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CuotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos de la cuota')
                    ->schema([
                        Select::make('socio_id')
                            ->label('Socio')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search): array {
                                return Socio::query()
                                    ->where('nombre', 'ilike', "%{$search}%")
                                    ->orWhere('apellidos', 'ilike', "%{$search}%")
                                    ->orWhere('email', 'ilike', "%{$search}%")
                                    ->orderBy('nombre')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(function (Socio $socio) {
                                        return [
                                            $socio->id => "{$socio->nombre} {$socio->apellidos} - {$socio->email}",
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(function ($value): ?string {
                                $socio = Socio::find($value);

                                if (! $socio) {
                                    return null;
                                }

                                return "{$socio->nombre} {$socio->apellidos} - {$socio->email}";
                            }),

                        TextInput::make('anio')
                            ->label('Año')
                            ->numeric()
                            ->required()
                            ->default(now()->year)
                            ->minValue(2000)
                            ->maxValue(now()->year + 10),

                        Toggle::make('pagado')
                            ->label('Pagada')
                            ->default(false)
                            ->live(),

                        TextInput::make('cuantia')
                            ->label('Cuantía (€)')
                            ->numeric()
                            ->step('0.01')
                            ->minValue(0)
                            ->nullable()
                            ->required(fn ($get) => (bool) $get('pagado'))
                            ->visible(fn ($get) => (bool) $get('pagado')),

                        DatePicker::make('fecha_pago')
                            ->label('Fecha de pago')
                            ->native(false)
                            ->nullable()
                            ->required(fn ($get) => (bool) $get('pagado'))
                            ->visible(fn ($get) => (bool) $get('pagado')),
                    ])
                    ->columns(2),
            ]);
    }
}