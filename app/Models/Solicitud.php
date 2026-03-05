<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';

    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'fecha_nacimiento',
        'telefono',
        'tipo_documento',
        'numero_documento',
        'direccion',
        'ciudad',
        'provincia',
        'codigo_postal',
        'pais',
        'tiene_hijos',
        'numero_hijos',
        'hijo_down',
        'fecha_nacimiento_hijo_down',
        'tipo_socio',
        'estado',
        'motivo_rechazo',
        'procesada_por',
        'procesada_en',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_nacimiento_hijo_down' => 'date',
        'tiene_hijos' => 'boolean',
        'hijo_down' => 'boolean',
        'procesada_en' => 'datetime',
    ];

    public function procesador()
    {
        return $this->belongsTo(User::class, 'procesada_por');
    }

    public function socio()
    {
        return $this->hasOne(Socio::class);
    }
}