<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?string $modelLabel = 'Audit Log';

    protected static ?string $pluralModelLabel = 'Audit Logs';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only - audit logs should not be manually created
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('action')
                    ->label('Action')
                    ->colors([
                        'success' => 'CREATE',
                        'info' => 'UPDATE',
                        'warning' => 'VIEW',
                        'danger' => 'DELETE',
                    ])
                    ->icons([
                        'heroicon-o-plus-circle' => 'CREATE',
                        'heroicon-o-pencil' => 'UPDATE',
                        'heroicon-o-eye' => 'VIEW',
                        'heroicon-o-trash' => 'DELETE',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('entity_type')
                    ->label('Entity Type')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-cube'),
                
                Tables\Columns\TextColumn::make('entity_id')
                    ->label('Entity ID')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-hashtag'),
                
                Tables\Columns\TextColumn::make('performedBy.full_name')
                    ->label('Performed By')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->badge()
                    ->color('gray')
                    ->placeholder('System'),
                
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-globe-alt')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('timestamp')
                    ->label('Timestamp')
                    ->dateTime('M j, Y \a\t g:i A')
                    ->sortable()
                    ->icon('heroicon-o-clock'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->label('Action')
                    ->options([
                        'CREATE' => 'Create',
                        'UPDATE' => 'Update',
                        'VIEW' => 'View',
                        'DELETE' => 'Delete',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('entity_type')
                    ->label('Entity Type')
                    ->options(fn () => AuditLog::query()
                        ->whereNotNull('entity_type')
                        ->distinct()
                        ->pluck('entity_type', 'entity_type')
                        ->toArray())
                    ->multiple()
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('performed_by_user_id')
                    ->label('Performed By')
                    ->relationship('performedBy', 'full_name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([
                // No bulk actions for audit logs
            ])
            ->defaultSort('timestamp', 'desc')
            ->emptyStateHeading('No audit logs yet')
            ->emptyStateDescription('Audit logs will appear here as actions are performed.')
            ->emptyStateIcon('heroicon-o-document-text');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Audit Log Overview')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('action')
                                    ->label('Action')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'CREATE' => 'success',
                                        'UPDATE' => 'info',
                                        'VIEW' => 'warning',
                                        'DELETE' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($state) => match ($state) {
                                        'CREATE' => 'heroicon-o-plus-circle',
                                        'UPDATE' => 'heroicon-o-pencil',
                                        'VIEW' => 'heroicon-o-eye',
                                        'DELETE' => 'heroicon-o-trash',
                                        default => 'heroicon-o-question-mark-circle',
                                    }),
                                
                                Infolists\Components\TextEntry::make('entity_type')
                                    ->label('Entity Type')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-cube'),
                                
                                Infolists\Components\TextEntry::make('entity_id')
                                    ->label('Entity ID')
                                    ->badge()
                                    ->color('primary')
                                    ->icon('heroicon-o-hashtag'),
                                
                                Infolists\Components\TextEntry::make('timestamp')
                                    ->label('Timestamp')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-o-clock')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('User Information')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('performedBy.full_name')
                                    ->label('Performed By')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('System'),
                                
                                Infolists\Components\TextEntry::make('ip_address')
                                    ->label('IP Address')
                                    ->icon('heroicon-o-globe-alt')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('Not recorded'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Details')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\TextEntry::make('details')
                            ->label('Details')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : 'No additional details')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull()
                            ->placeholder('No details available'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Audit logs are system-generated only
    }

    public static function canEdit($record): bool
    {
        return false; // Audit logs are read-only
    }

    public static function canDelete($record): bool
    {
        return false; // Audit logs should not be deleted
    }
}
