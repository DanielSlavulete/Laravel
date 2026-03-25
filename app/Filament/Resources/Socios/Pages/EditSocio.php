<?php

namespace App\Filament\Resources\Socios\Pages;

use App\Filament\Resources\Socios\SocioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditSocio extends EditRecord
{
    protected static string $resource = SocioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['tiene_hijos'] = ! empty($data['tiene_hijos']) ? 'true' : 'false';
        $data['hijo_down'] = ! empty($data['hijo_down']) ? 'true' : 'false';
        $data['numero_hijos'] = isset($data['numero_hijos']) ? (int) $data['numero_hijos'] : 0;

        $record->update($data);

        return $record;
    }
}