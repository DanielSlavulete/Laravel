<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\SolicitudesStats;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\AccountWidget;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Panel de administración';

    protected function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
        ];
    }

    public function getWidgets(): array
    {
        return [
            SolicitudesStats::class,
        ];
    }
}