<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSolicitudRequest;
use App\Models\Socio;
use App\Models\Solicitud;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class SolicitudController extends Controller
{
    public function store(StoreSolicitudRequest $request): JsonResponse
    {
        // Si Forminator está haciendo la prueba del webhook, no intentamos crear solicitud
        if ($request->header('x-hook-test') === 'true') {
            return response()->json([
                'ok' => true,
                'message' => 'Webhook test recibido correctamente.',
            ], 200);
        }

        $validated = $request->validated();

        $tieneHijos = $validated['radio_2'] === '1';
        $hijoDown = ($validated['radio_3'] ?? null) === '1';

        $fechaNacimientoSolicitante = Carbon::createFromFormat(
            'd-m-Y',
            $validated['date_1']
        )->format('Y-m-d');

        $numeroHijos = $tieneHijos && ! empty($validated['number_1'])
            ? (int) $validated['number_1']
            : null;

        $fechaNacimientoHijo = $hijoDown && ! empty($validated['date_2'])
            ? Carbon::createFromFormat('d-m-Y', $validated['date_2'])->format('Y-m-d')
            : null;

        $telefonoNormalizado = $this->normalizarTelefono($validated['phone_1'] ?? null);
        $documentoNormalizado = $this->normalizarDocumento($validated['text_1'] ?? null);
        $emailNormalizado = mb_strtolower(trim($validated['email_1']));

        // Comprobar duplicados en solicitudes
        $duplicadoSolicitud = Solicitud::where('numero_documento', $documentoNormalizado)
            ->orWhere('email', $emailNormalizado)
            ->exists();

        if ($duplicadoSolicitud) {
            return response()->json([
                'ok' => false,
                'message' => 'Ya existe una solicitud con este documento o correo electrónico.',
            ], 409);
        }

        // Comprobar duplicados en socios
        $duplicadoSocio = Socio::where('numero_documento', $documentoNormalizado)
            ->orWhere('email', $emailNormalizado)
            ->exists();

        if ($duplicadoSocio) {
            return response()->json([
                'ok' => false,
                'message' => 'Ya existe un socio con este documento o correo electrónico.',
            ], 409);
        }

        $solicitud = Solicitud::create([
            'nombre' => trim($validated['name_1_first_name']),
            'apellidos' => trim($validated['name_1_last_name']),
            'email' => $emailNormalizado,
            'fecha_nacimiento' => $fechaNacimientoSolicitante,
            'telefono' => $telefonoNormalizado,

            'tipo_documento' => $validated['radio_1'],
            'numero_documento' => $documentoNormalizado,

            'direccion' => trim($validated['address_1_street_address']),
            'ciudad' => trim($validated['address_1_city']),
            'provincia' => trim($validated['address_1_state']),
            'codigo_postal' => trim($validated['address_1_zip']),
            'pais' => trim($validated['address_1_country']),

            'tiene_hijos' => $tieneHijos ? 'true' : 'false',
            'numero_hijos' => $numeroHijos,
            'hijo_down' => $hijoDown ? 'true' : 'false',
            'fecha_nacimiento_hijo_down' => $fechaNacimientoHijo,

            'tipo_socio' => $validated['radio_4'],
            'estado' => 'pendiente',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Solicitud recibida correctamente.',
            'solicitud_id' => $solicitud->id,
        ], 201);
    }

    private function normalizarTelefono(?string $telefono): ?string
    {
        if (! $telefono) {
        }

        $telefono = trim($telefono);

        return preg_replace('/(?!^\+)[^\d]/', '', $telefono) ?: null;
    }

    private function normalizarDocumento(?string $documento): ?string
    {
        if (! $documento) {
            return null;
        }

        return strtoupper(trim($documento));
    }
}