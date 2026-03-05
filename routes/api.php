<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\SolicitudController;


Route::post('/solicitudes', [SolicitudController::class, 'store']);

Route::post('/debug-forminator', function (Request $request) {
    Log::info('FORMINATOR DEBUG', [
        'content_type' => $request->header('content-type'),
        'all' => $request->all(),
    ]);

    return response()->json([
        'received' => true,
        'content_type' => $request->header('content-type'),
        'all' => $request->all(),
    ]);
});