<?php

namespace App\Filament\Resources\Cuotas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CuotaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('socio_id')
                    ->required()
                    ->numeric(),
                TextInput::make('anio')
                    ->required()
                    ->numeric(),
                Toggle::make('pagado')
                    ->required(),
                DateTimePicker::make('fecha_pago'),
            ]);
    }
}
