<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VerificationResource\Pages;
use App\Models\Verification;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;

class VerificationResource extends Resource
{
    protected static ?string $model = Verification::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Verifications';

    protected static ?string $modelLabel = 'Verification';

    protected static ?string $pluralModelLabel = 'Verifications';

    protected static ?string $navigationGroup = 'Students';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Verification Details')
                    ->icon('heroicon-o-shield-check')
            ->schema([
                Forms\Components\Select::make('student_id')
                            ->label('Student')
                            ->relationship('student', 'kd_shs_id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->kd_shs_id} - {$record->first_name} {$record->last_name}")
                            ->searchable(['kd_shs_id', 'first_name', 'last_name'])
                            ->preload()
                    ->required(),
                        
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.kd_shs_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->icon('heroicon-o-identification')
                    ->url(fn ($record) => route('filament.admin.resources.students.view', $record->student)),
                
                Tables\Columns\TextColumn::make('student.full_name')
                    ->label('Student Name')
                    ->getStateUsing(fn ($record) => trim("{$record->student->first_name} {$record->student->other_names} {$record->student->last_name}"))
                    ->searchable(['student.first_name', 'student.last_name'])
                    ->sortable()
                    ->icon('heroicon-o-user'),
                
                Tables\Columns\TextColumn::make('student.school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-academic-cap')
                    ->toggleable(),
                
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
                    ->placeholder('Not verified')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime('M j, Y \a\t g:i A')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->placeholder('Not verified')
                    ->toggleable(),
                
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
                
                Tables\Filters\SelectFilter::make('student.school_id')
                    ->label('School')
                    ->relationship('student.school', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->icon('heroicon-o-eye'),
                    Tables\Actions\EditAction::make()
                        ->icon('heroicon-o-pencil'),
                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash'),
                ])
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->label('Actions')
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No verifications yet')
            ->emptyStateDescription('Verification records will appear here.')
            ->emptyStateIcon('heroicon-o-shield-check')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Verification')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Verification Overview')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('student.kd_shs_id')
                                    ->label('Student ID')
                                    ->icon('heroicon-o-identification')
                                    ->copyable()
                                    ->badge()
                                    ->color('primary')
                                    ->url(fn ($record) => route('filament.admin.resources.students.view', $record->student)),
                                
                                Infolists\Components\TextEntry::make('student.full_name')
                                    ->label('Student Name')
                                    ->getStateUsing(fn ($record) => trim("{$record->student->first_name} {$record->student->other_names} {$record->student->last_name}"))
                                    ->icon('heroicon-o-user')
                                    ->weight('bold')
                                    ->size('lg'),
                                
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'Approved' => 'success',
                                        'Pending' => 'warning',
                                        'Rejected' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($state) => match ($state) {
                                        'Approved' => 'heroicon-o-check-circle',
                                        'Pending' => 'heroicon-o-clock',
                                        'Rejected' => 'heroicon-o-x-circle',
                                        default => 'heroicon-o-question-mark-circle',
                                    }),
                                
                                Infolists\Components\TextEntry::make('method')
                                    ->label('Method')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'SchoolUpload' => 'School Upload',
                                        'SchoolAdminApproval' => 'School Admin Approval',
                                        'AmbassadorVerification' => 'Ambassador Verification',
                                        'SelfRegistration' => 'Self Registration',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Verification Details')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('verifiedBy.full_name')
                                    ->label('Verified By')
                                    ->icon('heroicon-o-user')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('Not verified'),
                                
                                Infolists\Components\TextEntry::make('verified_at')
                                    ->label('Verified At')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('Not verified'),
                                
                                Infolists\Components\TextEntry::make('remarks')
                                    ->label('Remarks')
                                    ->icon('heroicon-o-document-text')
                                    ->columnSpanFull()
                                    ->placeholder('No remarks'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVerifications::route('/'),
            'create' => Pages\CreateVerification::route('/create'),
            'view' => Pages\ViewVerification::route('/{record}'),
            'edit' => Pages\EditVerification::route('/{record}/edit'),
        ];
    }
}
