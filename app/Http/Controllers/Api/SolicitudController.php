<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SolicitudController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name_1_first_name' => 'required|string|max:100',
            'name_1_last_name'  => 'required|string|max:150',
            'email_1'           => 'nullable|email|max:150',
            'date_1'            => 'required|string', // d-m-Y
            'phone_1'           => 'required|string|max:30',

            'radio_1'           => 'required|in:dni,nie,pasaporte',
            'text_1'            => 'required|string|max:20',

            'address_1_street_address' => 'required|string|max:200',
            'address_1_city'           => 'required|string|max:100',
            'address_1_state'          => 'required|string|max:100',
            'address_1_zip'            => 'required|string|max:15',
            'address_1_country'        => 'required|string|max:80',

            'radio_2'   => 'required|in:0,1',      // tiene_hijos
            'number_1'  => 'nullable|integer|min:1|max:20',

            'radio_3'   => 'required|in:0,1',      // hijo_down
            'date_2'    => 'nullable|string',      // d-m-Y si aplica

            'radio_4'   => 'required|in:honorario,colaborador,numerario',
        ]);

        $tieneHijos = ((int) $data['radio_2']) === 1;
        $hijoDown   = ((int) $data['radio_3']) === 1;

        $fechaNacimiento = Carbon::createFromFormat('d-m-Y', $data['date_1'])->startOfDay();

        $fechaNacimientoHijoDown = null;
        if ($tieneHijos && $hijoDown && ! empty($data['date_2'])) {
            $fechaNacimientoHijoDown = Carbon::createFromFormat('d-m-Y', $data['date_2'])->startOfDay();
        }

        $solicitud = Solicitud::create([
            'nombre' => $data['name_1_first_name'],
            'apellidos' => $data['name_1_last_name'],
            'email' => $data['email_1'] ?? null,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono' => $data['phone_1'],

            'tipo_documento' => $data['radio_1'],
            'numero_documento' => $data['text_1'],

            'direccion' => $data['address_1_street_address'],
            'ciudad' => $data['address_1_city'],
            'provincia' => $data['address_1_state'],
            'codigo_postal' => $data['address_1_zip'],
            'pais' => $data['address_1_country'],

            'tiene_hijos' => $tieneHijos,
            'numero_hijos' => $tieneHijos ? ($data['number_1'] ?? null) : null,

            'hijo_down' => $tieneHijos ? $hijoDown : false,
            'fecha_nacimiento_hijo_down' => $fechaNacimientoHijoDown,

            'tipo_socio' => $data['radio_4'],

            'estado' => 'pendiente', // si tu tabla lo tiene
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud creada correctamente',
            'id' => $solicitud->id,
        ], 201);
    }
}
   
