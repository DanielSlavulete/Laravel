<?php

namespace App\Filament\Widgets;

use App\Models\Solicitud;
use App\Models\Socio;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SolicitudesStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Solicitudes pendientes', Solicitud::where('estado', 'pendiente')->count())
                ->description('Pendientes de revisar')
                ->color('warning'),

            Stat::make('Solicitudes aprobadas', Solicitud::where('estado', 'aprobada')->count())
                ->description('Solicitudes aceptadas')
                ->color('success'),

            Stat::make('Socios activos', Socio::where('estado', 'activo')->count())
                ->description('Socios actualmente activos')
                ->color('primary'),
        ];
    }
}
