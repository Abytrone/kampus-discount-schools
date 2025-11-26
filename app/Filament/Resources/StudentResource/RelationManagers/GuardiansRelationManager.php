<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuardiansRelationManager extends RelationManager
{
    protected static string $relationship = 'guardians';

    protected static ?string $title = 'Guardians';

    protected static ?string $modelLabel = 'Guardian';

    protected static ?string $pluralModelLabel = 'Guardians';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Guardian Information')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(200)
                            ->placeholder('Enter guardian full name'),
                        
                        Forms\Components\Select::make('relationship')
                            ->label('Relationship')
                            ->required()
                            ->options([
                                'Father' => 'Father',
                                'Mother' => 'Mother',
                                'Guardian' => 'Guardian',
                                'Uncle' => 'Uncle',
                                'Aunt' => 'Aunt',
                                'Grandfather' => 'Grandfather',
                                'Grandmother' => 'Grandmother',
                                'Brother' => 'Brother',
                                'Sister' => 'Sister',
                                'Other' => 'Other',
                            ])
                            ->native(false)
                            ->default('Guardian'),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('+233 XX XXX XXXX')
                                    ->icon('heroicon-o-phone'),
                                
                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('guardian@example.com')
                                    ->icon('heroicon-o-envelope'),
                            ]),
                        
                        Forms\Components\Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->placeholder('Guardian address')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-user-circle'),
                
                Tables\Columns\BadgeColumn::make('relationship')
                    ->label('Relationship')
                    ->colors([
                        'primary' => 'Father',
                        'pink' => 'Mother',
                        'success' => 'Guardian',
                        'info' => 'Uncle',
                        'warning' => 'Aunt',
                        'gray' => fn ($state) => in_array($state, ['Grandfather', 'Grandmother', 'Brother', 'Sister', 'Other']),
                    ])
                    ->icons([
                        'heroicon-o-user' => 'Father',
                        'heroicon-o-user' => 'Mother',
                        'heroicon-o-shield-check' => 'Guardian',
                        'heroicon-o-user-group' => 'Uncle',
                        'heroicon-o-user-group' => 'Aunt',
                        'heroicon-o-user' => fn ($state) => in_array($state, ['Grandfather', 'Grandmother', 'Brother', 'Sister', 'Other']),
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->url(fn ($record) => $record->phone ? 'tel:' . $record->phone : null)
                    ->copyable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-o-envelope')
                    ->url(fn ($record) => $record->email ? 'mailto:' . $record->email : null)
                    ->copyable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->address)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Added')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('relationship')
                    ->label('Relationship')
                    ->options([
                        'Father' => 'Father',
                        'Mother' => 'Mother',
                        'Guardian' => 'Guardian',
                        'Uncle' => 'Uncle',
                        'Aunt' => 'Aunt',
                        'Grandfather' => 'Grandfather',
                        'Grandmother' => 'Grandmother',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash'),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->label('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No guardians yet')
            ->emptyStateDescription('Add a guardian for this student.')
            ->emptyStateIcon('heroicon-o-user-circle')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Guardian')
                    ->icon('heroicon-o-plus'),
            ]);
    }
}
