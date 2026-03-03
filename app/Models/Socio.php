<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    protected $table = 'socios';

    protected $fillable = [
        'solicitud_id',
        'nombre',
        'apellidos',
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
        'fecha_alta',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_nacimiento_hijo_down' => 'date',
        'tiene_hijos' => 'boolean',
        'hijo_down' => 'boolean',
        'fecha_alta' => 'datetime',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class);
    }
    
}
