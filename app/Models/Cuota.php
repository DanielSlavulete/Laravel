<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{
    protected $table = 'cuotas';

    protected $fillable = [
        'socio_id',
        'anio',
        'pagado',
        'cuantia',
        'fecha_pago',
    ];

    protected $casts = [
        'anio' => 'integer',
        'pagado' => 'boolean',
        'cuantia' => 'decimal:2',
        'fecha_pago' => 'datetime',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }
}