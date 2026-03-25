<?php

namespace App\Filament\Resources\Socios\Pages;

use App\Filament\Resources\Socios\SocioResource;
use App\Models\Socio;
use Filament\Resources\Pages\CreateRecord;

class CreateSocio extends CreateRecord
{
    protected static string $resource = SocioResource::class;

    protected function handleRecordCreation(array $data): Socio
    {
        $data['tiene_hijos'] = ! empty($data['tiene_hijos']) ? 'true' : 'false';
        $data['hijo_down'] = ! empty($data['hijo_down']) ? 'true' : 'false';
        $data['numero_hijos'] = isset($data['numero_hijos']) ? (int) $data['numero_hijos'] : 0;

        return Socio::create($data);
    }
}