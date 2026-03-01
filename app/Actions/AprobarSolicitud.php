<?php

namespace App\Actions;

use App\Models\Solicitud;
use App\Models\Socio;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AprobarSolicitud
{
    public function handle(Solicitud $solicitud, int $userId): Socio
    {
        // 1) Si esta solicitud ya tiene socio creado, devolvemos ese socio
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

        // 2) Si ya existe un socio con ese documento, no creamos otro
        $existingByDoc = Socio::where('numero_documento', $solicitud->numero_documento)->first();
        if ($existingByDoc) {
            throw new RuntimeException('Ya existe un socio con ese número de documento.');
        }

        return DB::transaction(function () use ($solicitud, $userId) {
            $socio = Socio::create([
                'solicitud_id' => $solicitud->id,
                'nombre' => $solicitud->nombre,
                'apellidos' => $solicitud->apellidos,
                'fecha_nacimiento' => $solicitud->fecha_nacimiento,
                'telefono' => $solicitud->telefono,
                'tipo_documento' => $solicitud->tipo_documento,
                'numero_documento' => $solicitud->numero_documento,
                'direccion' => $solicitud->direccion,
                'ciudad' => $solicitud->ciudad,
                'provincia' => $solicitud->provincia,
                'codigo_postal' => $solicitud->codigo_postal,
                'pais' => $solicitud->pais,
                'tiene_hijos' => $solicitud->tiene_hijos,
                'numero_hijos' => $solicitud->numero_hijos,
                'hijo_down' => $solicitud->hijo_down,
                'fecha_nacimiento_hijo_down' => $solicitud->fecha_nacimiento_hijo_down,
                'tipo_socio' => $solicitud->tipo_socio,
                'estado' => 'activo',
                'fecha_alta' => now(),
            ]);

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