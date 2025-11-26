<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->description('Basic user account details')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('full_name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Enter full name'),
                                
                                Forms\Components\TextInput::make('email')
                                    ->label('Email Address')
                                    ->required()
                                    ->email()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('user@example.com'),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('+233 XX XXX XXXX')
                                    ->icon('heroicon-o-phone'),
                                
                                Forms\Components\Select::make('role')
                                    ->label('Role')
                                    ->required()
                                    ->options([
                                        'KD_Admin' => 'KampusDiscount Admin',
                                        'School_Admin' => 'School Admin',
                                        'Ambassador' => 'Ambassador',
                                        'ReadOnly_Partner' => 'Read-Only Partner',
                                    ])
                                    ->native(false)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, callable $set) => 
                                        $state === 'KD_Admin' || $state === 'ReadOnly_Partner' 
                                            ? $set('school_id', null) 
                                            : null
                                    ),
                            ]),
                    ]),
                
                Forms\Components\Section::make('School Assignment')
                    ->description('Assign user to a school (for School Admins and Ambassadors)')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Select::make('school_id')
                            ->label('School')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => in_array($get('role'), ['School_Admin', 'Ambassador']))
                            ->required(fn ($get) => in_array($get('role'), ['School_Admin', 'Ambassador'])),
                    ]),
                
                Forms\Components\Section::make('Account Status')
                    ->description('User account status and password')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive',
                                    ])
                                    ->default('Active')
                                    ->native(false),
                                
                                Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                                    ->minLength(8)
                                    ->helperText('Leave empty to keep current password')
                                    ->placeholder('Enter new password'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-user'),
                
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope')
                    ->copyable(),
                
                Tables\Columns\TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'KD_Admin' => 'KD Admin',
                        'School_Admin' => 'School Admin',
                        'Ambassador' => 'Ambassador',
                        'ReadOnly_Partner' => 'Partner',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'KD_Admin',
                        'primary' => 'School_Admin',
                        'success' => 'Ambassador',
                        'info' => 'ReadOnly_Partner',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'KD_Admin',
                        'heroicon-o-user' => 'School_Admin',
                        'heroicon-o-user-group' => 'Ambassador',
                        'heroicon-o-building-office' => 'ReadOnly_Partner',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->school?->name)
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-academic-cap')
                    ->placeholder('N/A'),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Active',
                        'danger' => 'Inactive',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Active',
                        'heroicon-o-x-circle' => 'Inactive',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'KD_Admin' => 'KampusDiscount Admin',
                        'School_Admin' => 'School Admin',
                        'Ambassador' => 'Ambassador',
                        'ReadOnly_Partner' => 'Read-Only Partner',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name')
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
            ->emptyStateHeading('No users yet')
            ->emptyStateDescription('Get started by creating your first user.')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create User')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('User Overview')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('full_name')
                                    ->label('Full Name')
                                    ->icon('heroicon-o-user')
                                    ->weight('bold')
                                    ->size('lg'),
                                
                                Infolists\Components\TextEntry::make('email')
                                    ->label('Email Address')
                                    ->icon('heroicon-o-envelope')
                                    ->url(fn ($record) => 'mailto:' . $record->email)
                                    ->copyable(),
                                
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('Phone Number')
                                    ->icon('heroicon-o-phone')
                                    ->url(fn ($record) => $record->phone ? 'tel:' . $record->phone : null)
                                    ->copyable()
                                    ->placeholder('Not provided'),
                                
                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($record) => $record->status === 'Active' ? 'success' : 'danger')
                                    ->icon(fn ($record) => $record->status === 'Active' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Role & Permissions')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('role')
                                    ->label('Role')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'KD_Admin' => 'KampusDiscount Admin',
                                        'School_Admin' => 'School Admin',
                                        'Ambassador' => 'Ambassador',
                                        'ReadOnly_Partner' => 'Read-Only Partner',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'KD_Admin' => 'danger',
                                        'School_Admin' => 'primary',
                                        'Ambassador' => 'success',
                                        'ReadOnly_Partner' => 'info',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($state) => match ($state) {
                                        'KD_Admin' => 'heroicon-o-shield-check',
                                        'School_Admin' => 'heroicon-o-user',
                                        'Ambassador' => 'heroicon-o-user-group',
                                        'ReadOnly_Partner' => 'heroicon-o-building-office',
                                        default => 'heroicon-o-question-mark-circle',
                                    }),
                                
                                Infolists\Components\TextEntry::make('school.name')
                                    ->label('Assigned School')
                                    ->icon('heroicon-o-academic-cap')
                                    ->badge()
                                    ->color('primary')
                                    ->url(fn ($record) => $record->school ? route('filament.admin.resources.schools.view', $record->school) : null)
                                    ->placeholder('Not assigned'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Account Information')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-o-clock')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
