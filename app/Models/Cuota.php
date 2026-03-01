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
        'fecha_pago',
    ];

    protected $casts = [
        'anio' => 'integer',
        'pagado' => 'boolean',
        'fecha_pago' => 'datetime',
    ];

    public function socio()
    {
        return $this->belongsTo(Socio::class);
    }
}
