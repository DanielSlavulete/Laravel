<?php

namespace App\Filament\Resources\Cuotas\Pages;

use App\Filament\Resources\Cuotas\CuotaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditCuota extends EditRecord
{
    protected static string $resource = CuotaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! (bool) ($data['pagado'] ?? false)) {
            $data['cuantia'] = null;
            $data['fecha_pago'] = null;
        }

        return $data;
    }

    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $pagado = (bool) ($data['pagado'] ?? false);

        DB::table('cuotas')
            ->where('id', $record->id)
            ->update([
                'socio_id' => $data['socio_id'],
                'anio' => (int) $data['anio'],
                'pagado' => DB::raw($pagado ? 'true' : 'false'),
                'cuantia' => $pagado ? ($data['cuantia'] ?? null) : null,
                'fecha_pago' => $pagado ? ($data['fecha_pago'] ?? null) : null,
                'updated_at' => now(),
            ]);

        return $record->refresh();
    }
}