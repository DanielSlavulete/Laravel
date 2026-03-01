<?php

namespace App\Filament\Resources\Solicituds;

use App\Filament\Resources\Solicituds\Pages\CreateSolicitud;
use App\Filament\Resources\Solicituds\Pages\EditSolicitud;
use App\Filament\Resources\Solicituds\Pages\ListSolicituds;
use App\Filament\Resources\Solicituds\Pages\ViewSolicitud;
use App\Filament\Resources\Solicituds\Schemas\SolicitudForm;
use App\Filament\Resources\Solicituds\Schemas\SolicitudInfolist;
use App\Filament\Resources\Solicituds\Tables\SolicitudsTable;
use App\Models\Solicitud;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SolicitudResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return SolicitudForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SolicitudInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SolicitudsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSolicituds::route('/'),
            'create' => CreateSolicitud::route('/create'),
            'view' => ViewSolicitud::route('/{record}'),
            'edit' => EditSolicitud::route('/{record}/edit'),
        ];
    }
}
