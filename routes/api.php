<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\SolicitudController;

// Ruta real que recibe el formulario, ponemos que sea 3 solicitudes maximo por 1 dia (solicitudes que vengan de una misma IP, no globales)
Route::post('/solicitudes', [SolicitudController::class, 'store'])
    ->middleware(['wp.api.key', 'throttle:10,1']);
