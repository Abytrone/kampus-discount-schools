<?php

namespace App\Filament\Resources\StudentResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class VerificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'verifications';

    protected static ?string $title = 'Verification History';

    protected static ?string $modelLabel = 'Verification';

    protected static ?string $pluralModelLabel = 'Verifications';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Verification Details')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Select::make('method')
                            ->label('Verification Method')
                            ->required()
                            ->options([
                                'SchoolUpload' => 'School Upload',
                                'SchoolAdminApproval' => 'School Admin Approval',
                                'AmbassadorVerification' => 'Ambassador Verification',
                                'SelfRegistration' => 'Self Registration',
                            ])
                            ->native(false)
                            ->default('SchoolAdminApproval'),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options([
                                'Pending' => 'Pending',
                                'Approved' => 'Approved',
                                'Rejected' => 'Rejected',
                            ])
                            ->native(false)
                            ->default('Pending')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'Approved' || $state === 'Rejected') {
                                    $set('verified_at', now());
                                    $set('verified_by_user_id', Auth::id());
                                }
                            }),
                        
                        Forms\Components\Select::make('verified_by_user_id')
                            ->label('Verified By')
                            ->relationship('verifiedBy', 'full_name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => Auth::id())
                            ->visible(fn ($get) => in_array($get('status'), ['Approved', 'Rejected'])),
                        
                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('Verified At')
                            ->default(now())
                            ->visible(fn ($get) => in_array($get('status'), ['Approved', 'Rejected'])),
                        
                        Forms\Components\Textarea::make('remarks')
                            ->label('Remarks')
                            ->rows(3)
                            ->placeholder('Additional notes or comments')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Approved',
                        'warning' => 'Pending',
                        'danger' => 'Rejected',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Approved',
                        'heroicon-o-clock' => 'Pending',
                        'heroicon-o-x-circle' => 'Rejected',
                    ])
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('method')
                    ->label('Method')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'SchoolUpload' => 'School Upload',
                        'SchoolAdminApproval' => 'School Admin',
                        'AmbassadorVerification' => 'Ambassador',
                        'SelfRegistration' => 'Self Registration',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'SchoolUpload',
                        'info' => 'SchoolAdminApproval',
                        'success' => 'AmbassadorVerification',
                        'warning' => 'SelfRegistration',
                    ])
                    ->icons([
                        'heroicon-o-arrow-up-tray' => 'SchoolUpload',
                        'heroicon-o-user' => 'SchoolAdminApproval',
                        'heroicon-o-shield-check' => 'AmbassadorVerification',
                        'heroicon-o-user-plus' => 'SelfRegistration',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('verifiedBy.full_name')
                    ->label('Verified By')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Not verified'),
                
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('M j, Y \a\t g:i A')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->placeholder('Not verified'),
                
                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->remarks)
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('method')
                    ->label('Method')
                    ->options([
                        'SchoolUpload' => 'School Upload',
                        'SchoolAdminApproval' => 'School Admin Approval',
                        'AmbassadorVerification' => 'Ambassador Verification',
                        'SelfRegistration' => 'Self Registration',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->label('Add Verification'),
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
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No verifications yet')
            ->emptyStateDescription('Create a verification record for this student.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Verification')
                    ->icon('heroicon-o-plus'),
            ]);
    }
}
