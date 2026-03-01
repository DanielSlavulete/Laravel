<?php

namespace App\Filament\Resources\Solicituds\Tables;

use App\Actions\AprobarSolicitud;
use App\Actions\RechazarSolicitud;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SolicitudsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable(),
                TextColumn::make('apellidos')->searchable(),
                TextColumn::make('fecha_nacimiento')->date()->sortable(),
                TextColumn::make('telefono')->searchable(),
                TextColumn::make('tipo_documento')->searchable(),
                TextColumn::make('numero_documento')->searchable(),
                TextColumn::make('direccion')->searchable(),
                TextColumn::make('ciudad')->searchable(),
                TextColumn::make('provincia')->searchable(),
                TextColumn::make('codigo_postal')->searchable(),
                TextColumn::make('pais')->searchable(),
                IconColumn::make('tiene_hijos')->boolean(),
                TextColumn::make('numero_hijos')->numeric()->sortable(),
                IconColumn::make('hijo_down')->boolean(),
                TextColumn::make('fecha_nacimiento_hijo_down')->date()->sortable(),
                TextColumn::make('tipo_socio')->searchable(),
                TextColumn::make('estado')->searchable(),
                TextColumn::make('procesada_por')->numeric()->sortable(),
                TextColumn::make('procesada_en')->dateTime()->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                Action::make('aprobar')
                    ->label('Aprobar')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->estado === 'pendiente')
                    ->action(function ($record) {
                        try {
                            app(AprobarSolicitud::class)->handle($record, auth()->id());

                            Notification::make()
                                ->title('Solicitud aprobada correctamente')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('No se pudo aprobar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('rechazar')
                    ->label('Rechazar')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->visible(fn ($record) => $record->estado === 'pendiente')
                    ->form([
                        Textarea::make('motivo')
                            ->label('Motivo del rechazo')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function ($record, array $data) {
                        try {
                            app(RechazarSolicitud::class)->handle(
                                $record,
                                auth()->id(),
                                $data['motivo']
                            );

                            Notification::make()
                                ->title('Solicitud rechazada')
                                ->danger()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('No se pudo rechazar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}