<?php

namespace App\Actions;

use App\Models\Solicitud;
use Illuminate\Support\Facades\DB;

class RechazarSolicitud
{
    public function handle(Solicitud $solicitud, int $userId, string $motivo): void
    {
        DB::transaction(function () use ($solicitud, $userId, $motivo) {

            $solicitud->update([
                'estado' => 'rechazada',
                'motivo_rechazo' => $motivo,
                'procesada_por' => $userId,
                'procesada_en' => now(),
            ]);

        });
    }
}