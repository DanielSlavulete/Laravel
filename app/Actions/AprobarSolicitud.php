<?php

namespace App\Actions;

use App\Models\Solicitud;
use App\Models\Socio;
use App\Models\Cuota;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AprobarSolicitud
{
    public function handle(Solicitud $solicitud, int $userId): Socio
    {
        $existingBySolicitud = Socio::where('solicitud_id', $solicitud->id)->first();

        if ($existingBySolicitud) {
            if ($solicitud->estado !== 'aprobada') {
                $solicitud->update([
                    'estado' => 'aprobada',
                    'motivo_rechazo' => null,
                    'procesada_por' => $userId,
                    'procesada_en' => now(),
                ]);
            }

            return $existingBySolicitud;
        }

        $existingByDoc = Socio::where('numero_documento', $solicitud->numero_documento)->first();

        if ($existingByDoc) {
            throw new RuntimeException('Ya existe un socio con ese número de documento.');
        }

        return DB::transaction(function () use ($solicitud, $userId) {
            $socio = Socio::create([
                'solicitud_id' => $solicitud->id,
                'nombre' => $solicitud->nombre,
                'apellidos' => $solicitud->apellidos,
                'email' => $solicitud->email,
                'fecha_nacimiento' => $solicitud->fecha_nacimiento,
                'telefono' => $solicitud->telefono,
                'tipo_documento' => $solicitud->tipo_documento,
                'numero_documento' => $solicitud->numero_documento,
                'direccion' => $solicitud->direccion,
                'ciudad' => $solicitud->ciudad,
                'provincia' => $solicitud->provincia,
                'codigo_postal' => $solicitud->codigo_postal,
                'pais' => $solicitud->pais,
                'tiene_hijos' => $solicitud->tiene_hijos ? 'true' : 'false',
                'numero_hijos' => $solicitud->numero_hijos,
                'hijo_down' => $solicitud->hijo_down ? 'true' : 'false',
                'fecha_nacimiento_hijo_down' => $solicitud->fecha_nacimiento_hijo_down,
                'tipo_socio' => $solicitud->tipo_socio,
                'estado' => 'activo',
                'fecha_alta' => now(),
            ]);

            $anioActual = now()->year;

            Cuota::firstOrCreate(
                [
                    'socio_id' => $socio->id,
                    'anio' => $anioActual,
                ],
                [
                    'pagado' => 'false',
                    'fecha_pago' => null,
                ]
            );

            $solicitud->update([
                'estado' => 'aprobada',
                'motivo_rechazo' => null,
                'procesada_por' => $userId,
                'procesada_en' => now(),
            ]);

            return $socio;
        });
    }
}