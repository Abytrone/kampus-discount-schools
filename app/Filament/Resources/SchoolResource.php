<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\Pages;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Schools';

    protected static ?string $modelLabel = 'School';

    protected static ?string $pluralModelLabel = 'Schools';

    protected static ?string $navigationGroup = 'Institutions';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view_any_school') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create_school') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // School admins can only edit their own school
        if ($user->hasRole('school_admin')) {
            return $user->can('update_school') &&
                   $user->school_id === $record->id;
        }

        return $user->can('update_school');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // School admins cannot delete schools
        if ($user->hasRole('school_admin')) {
            return false;
        }

        return $user->can('delete_school');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        // School admins can only see their own school
        if ($user && $user->hasRole('school_admin') && $user->school_id) {
            $query->where('id', $user->school_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('School Information')
                    ->description('Official school details from GES database')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('school_code')
                                    ->label('GES School Code')
                                    ->required()
                                    ->numeric()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(10)
                                    ->placeholder('e.g., 50102')
                                    ->helperText('Official Ghana Education Service school code')
                                    ->disabled(fn ($record) => $record !== null && Auth::user()?->hasRole('school_admin'))
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('name')
                                    ->label('School Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Full official name of the school')
                                    ->columnSpan(2),
                            ]),

                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\Select::make('category')
                                    ->label('Category')
                                    ->options([
                                        'A' => 'Category A',
                                        'B' => 'Category B',
                                        'C' => 'Category C',
                                        'D' => 'Category D',
                                    ])
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('school_type')
                                    ->label('School Type')
                                    ->required()
                                    ->options([
                                        'SHS' => 'Senior High School',
                                        'Technical' => 'Technical School',
                                        'Vocational' => 'Vocational School',
                                        'STEM' => 'STEM School',
                                    ])
                                    ->default('SHS')
                                    ->native(false)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('gender_type')
                                    ->label('Gender Type')
                                    ->required()
                                    ->options([
                                        'Boys' => 'Boys',
                                        'Girls' => 'Girls',
                                        'Mixed' => 'Mixed',
                                    ])
                                    ->default('Mixed')
                                    ->native(false)
                                    ->columnSpan(1),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->required()
                                    ->options([
                                        'Active' => 'Active',
                                        'Inactive' => 'Inactive',
                                    ])
                                    ->default('Active')
                                    ->native(false)
                                    ->disabled(fn ($record) => $record !== null && Auth::user()?->hasRole('school_admin'))
                                    ->columnSpan(1),
                            ]),
                    ]),

                Forms\Components\Section::make('Location Details')
                    ->description('Geographic information')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('region')
                                    ->label('Region')
                                    ->options(fn () => School::query()
                                        ->whereNotNull('region')
                                        ->distinct()
                                        ->orderBy('region')
                                        ->pluck('region', 'region')
                                        ->toArray())
                                    ->searchable()
                                    ->reactive()
                                    ->columnSpan(1),

                                Forms\Components\Select::make('district')
                                    ->label('District')
                                    ->options(function ($get) {
                                        $region = $get('region');
                                        if (!$region) {
                                            return School::query()
                                                ->whereNotNull('district')
                                                ->distinct()
                                                ->orderBy('district')
                                                ->pluck('district', 'district')
                                                ->toArray();
                                        }
                                        return School::query()
                                            ->where('region', $region)
                                            ->whereNotNull('district')
                                            ->distinct()
                                            ->orderBy('district')
                                            ->pluck('district', 'district')
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('location')
                                    ->label('Location/Town')
                                    ->maxLength(200)
                                    ->placeholder('Specific town or location')
                                    ->columnSpan(1),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('Full Address')
                            ->rows(2)
                            ->placeholder('Complete physical address')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Academic Programmes')
                    ->description('Programmes offered by the school')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Forms\Components\TagsInput::make('programmes')
                            ->label('Programmes Offered')
                            ->placeholder('Type and press Enter to add a programme')
                            ->helperText('Add programmes offered by this school')
                            ->suggestions([
                                'General Arts',
                                'General Science',
                                'Business',
                                'Home Economics',
                                'Visual Arts',
                                'Agriculture',
                                'Science',
                                'Arts',
                                'Technical',
                                'Vocational',
                            ])
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('num_programmes')
                            ->label('Number of Programmes')
                            ->numeric()
                            ->default(fn ($get) => count($get('programmes') ?? []))
                            ->minValue(0)
                            ->maxValue(20)
                            ->helperText('Automatically calculated from programmes list')
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),

                        Forms\Components\Select::make('status_type')
                            ->label('School Status Type')
                            ->options([
                                'Day' => 'Day School',
                                'Boarding' => 'Boarding School',
                                'Day/Boarding' => 'Day & Boarding',
                            ])
                            ->native(false)
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Contact Information')
                    ->description('Ways to reach the school')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('+233 XX XXX XXXX')
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('contact_email')
                                    ->label('Email Address')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('school@example.com')
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school_code')
                    ->label('GES Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('School code copied!')
                    ->icon('heroicon-o-hashtag')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('name')
                    ->label('School Name')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->name)
                    ->wrap()
                    ->weight('medium')
                    ->icon('heroicon-o-academic-cap'),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->colors([
                        'danger' => 'A',
                        'warning' => 'B',
                        'info' => 'C',
                        'gray' => fn ($state) => in_array($state, ['D', 'E', 'F']),
                    ])
                    ->icons([
                        'heroicon-o-star' => 'A',
                        'heroicon-o-star' => 'B',
                        'heroicon-o-star' => 'C',
                    ])
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('gender_type')
                    ->label('Gender')
                    ->badge()
                    ->colors([
                        'info' => 'Boys',
                        'pink' => 'Girls',
                        'success' => 'Mixed',
                    ])
                    ->icons([
                        'heroicon-o-user' => 'Boys' || 'Girls',
                        'heroicon-o-user-group' => 'Mixed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('region')
                    ->label('Region')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->icon('heroicon-o-map-pin')
                    ->limit(20),

                Tables\Columns\TextColumn::make('district')
                    ->label('District')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->location)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('programmes')
                    ->label('Programmes')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-book-open')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('num_programmes')
                    ->label('Programmes')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-academic-cap')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'Day/Boarding' => 'Day & Boarding',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'Day',
                        'success' => 'Boarding',
                        'info' => 'Day/Boarding',
                    ])
                    ->icons([
                        'heroicon-o-sun' => 'Day',
                        'heroicon-o-home' => 'Boarding',
                        'heroicon-o-building-office' => 'Day/Boarding',
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('total_students_count')
                    ->label('Students')
                    ->getStateUsing(function ($record) {
                        try {
                            return $record->students()->count();
                        } catch (\Exception $e) {
                            return 0;
                        }
                    })
                    ->sortable()
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-user-group')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
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
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Active' => 'Active',
                        'Inactive' => 'Inactive',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->options([
                        'A' => 'Category A',
                        'B' => 'Category B',
                        'C' => 'Category C',
                        'D' => 'Category D',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('gender_type')
                    ->label('Gender Type')
                    ->options([
                        'Boys' => 'Boys',
                        'Girls' => 'Girls',
                        'Mixed' => 'Mixed',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('school_type')
                    ->label('School Type')
                    ->options([
                        'SHS' => 'Senior High School',
                        'Technical' => 'Technical School',
                        'Vocational' => 'Vocational School',
                        'STEM' => 'STEM School',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('status_type')
                    ->label('Status Type')
                    ->options([
                        'Day' => 'Day School',
                        'Boarding' => 'Boarding School',
                        'Day/Boarding' => 'Day & Boarding',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('region')
                    ->label('Region')
                    ->options(fn () => School::query()
                        ->whereNotNull('region')
                        ->distinct()
                        ->orderBy('region')
                        ->pluck('region', 'region')
                        ->toArray())
                    ->multiple()
                    ->searchable(),

                Tables\Filters\SelectFilter::make('district')
                    ->label('District')
                    ->options(fn () => School::query()
                        ->whereNotNull('district')
                        ->distinct()
                        ->orderBy('district')
                        ->pluck('district', 'district')
                        ->toArray())
                    ->multiple()
                    ->searchable(),
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
            ->defaultSort('school_code', 'asc')
            ->emptyStateHeading('No schools yet')
            ->emptyStateDescription('Schools will be loaded from the GES database.')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create School')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('School Overview')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('school_code')
                                    ->label('GES School Code')
                                    ->icon('heroicon-o-hashtag')
                                    ->copyable()
                                    ->badge()
                                    ->color('primary')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('name')
                                    ->label('School Name')
                                    ->icon('heroicon-o-academic-cap')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->columnSpan(2),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('category')
                                    ->label('Category')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'A' => 'danger',
                                        'B' => 'warning',
                                        'C' => 'info',
                                        default => 'gray',
                                    })
                                    ->icon('heroicon-o-star'),

                                Infolists\Components\TextEntry::make('school_type')
                                    ->label('School Type')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-building-office'),

                                Infolists\Components\TextEntry::make('gender_type')
                                    ->label('Gender Type')
                                    ->badge()
                                    ->color(fn ($state) => match ($state) {
                                        'Boys' => 'info',
                                        'Girls' => 'pink',
                                        'Mixed' => 'success',
                                        default => 'gray',
                                    })
                                    ->icon(fn ($state) => $state === 'Mixed' ? 'heroicon-o-user-group' : 'heroicon-o-user'),

                                Infolists\Components\TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($record) => $record->status === 'Active' ? 'success' : 'danger')
                                    ->icon(fn ($record) => $record->status === 'Active' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Location Information')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('region')
                                    ->label('Region')
                                    ->icon('heroicon-o-map-pin')
                                    ->badge()
                                    ->color('primary')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('district')
                                    ->label('District')
                                    ->icon('heroicon-o-map-pin')
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('location')
                                    ->label('Location/Town')
                                    ->icon('heroicon-o-map-pin')
                                    ->badge()
                                    ->color('gray'),

                                Infolists\Components\TextEntry::make('status_type')
                                    ->label('School Status')
                                    ->formatStateUsing(fn ($state) => match ($state) {
                                        'Day/Boarding' => 'Day & Boarding',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-building-office'),

                                Infolists\Components\TextEntry::make('address')
                                    ->label('Full Address')
                                    ->icon('heroicon-o-home')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Academic Programmes')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('num_programmes')
                                    ->label('Number of Programmes')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-academic-cap')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('programmes')
                                    ->label('Programmes Offered')
                                    ->formatStateUsing(function ($record) {
                                        if (empty($record->programmes) || !is_array($record->programmes)) {
                                            return 'No programmes listed';
                                        }
                                        return collect($record->programmes)->map(fn ($p) => trim($p))->filter()->values();
                                    })
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-book-open')
                                    ->columnSpanFull()
                                    ->listWithLineBreaks(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Contact Information')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('contact_phone')
                                    ->label('Phone Number')
                                    ->icon('heroicon-o-phone')
                                    ->url(fn ($record) => $record->contact_phone ? 'tel:' . $record->contact_phone : null)
                                    ->copyable(),

                                Infolists\Components\TextEntry::make('contact_email')
                                    ->label('Email Address')
                                    ->icon('heroicon-o-envelope')
                                    ->url(fn ($record) => $record->contact_email ? 'mailto:' . $record->contact_email : null)
                                    ->copyable(),
                            ]),
                    ]),

                Infolists\Components\Section::make('Statistics')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_students_count')
                                    ->label('Total Students')
                                    ->getStateUsing(function ($record) {
                                        try {
                                            return $record->students()->count();
                                        } catch (\Exception $e) {
                                            return 0;
                                        }
                                    })
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-user-group')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('active_students_count')
                                    ->label('Active Students')
                                    ->getStateUsing(function ($record) {
                                        try {
                                            return $record->students()->where('admission_status', 'Active')->count();
                                        } catch (\Exception $e) {
                                            return 0;
                                        }
                                    })
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-check-circle')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Created')
                                    ->dateTime('F j, Y \a\t g:i A')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),
            ]);
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
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'view' => Pages\ViewSchool::route('/{record}'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
