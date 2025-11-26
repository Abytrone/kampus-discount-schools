<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use App\Models\School;
use App\Services\KdShsIdService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Students';

    protected static ?string $modelLabel = 'Student';

    protected static ?string $pluralModelLabel = 'Students';

    protected static ?string $navigationGroup = 'Students';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view_any_student') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create_student') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // School admins can only edit students from their school
        if ($user->hasRole('school_admin')) {
            return $user->can('update_student') && 
                   $user->school_id === $record->school_id;
        }
        
        return $user->can('update_student');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        // School admins can delete students from their school
        if ($user->hasRole('school_admin')) {
            return $user->can('delete_student') && 
                   $user->school_id === $record->school_id;
        }
        
        return $user->can('delete_student');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if (!$user) return $query;

        // School admins can only see students from their school
        if ($user->hasRole('school_admin') && $user->school_id) {
            $query->where('school_id', $user->school_id);
        }

        // ReadOnly partners can only view active, approved students
        if ($user->hasRole('readonly_partner')) {
            $query->where('admission_status', 'Active')
                  ->whereHas('latestVerification', function ($q) {
                      $q->where('status', 'Approved');
                  });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Personal Information')
                    ->description('Student personal details and identification')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\Grid::make(3)
            ->schema([
                                Forms\Components\TextInput::make('kd_shs_id')
                                    ->label('GES-SHS ID')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('GES-SHS-50102-000001')
                                    ->helperText('Auto-generated if left empty')
                                    ->disabled(fn ($record) => $record !== null)
                                    ->dehydrated()
                                    ->columnSpan(1),
                                
                                Forms\Components\Select::make('school_id')
                                    ->label('School')
                                    ->relationship('school', 'name')
                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if (!$get('kd_shs_id') && $state) {
                                            $school = School::find($state);
                                            if ($school) {
                                                $idService = new KdShsIdService();
                                                $set('kd_shs_id', $idService->generateBySchoolId($school->id));
                                            }
                                        }
                                    })
                                    ->columnSpan(2),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                Forms\Components\TextInput::make('first_name')
                                    ->label('First Name')
                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(1),
                                
                Forms\Components\TextInput::make('last_name')
                                    ->label('Last Name')
                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan(1),
                                
                Forms\Components\TextInput::make('other_names')
                                    ->label('Other Names')
                    ->maxLength(100)
                                    ->columnSpan(1),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('gender')
                                    ->label('Gender')
                                    ->required()
                                    ->options([
                                        'M' => 'Male',
                                        'F' => 'Female',
                                    ])
                                    ->native(false)
                                    ->columnSpan(1),
                                
                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('Date of Birth')
                                    ->required()
                                    ->maxDate(now()->subYears(13))
                                    ->displayFormat('d/m/Y')
                                    ->columnSpan(1),
                                
                                Forms\Components\FileUpload::make('photo_url')
                                    ->label('Photo')
                                    ->image()
                                    ->directory('students/photos')
                                    ->visibility('public')
                                    ->columnSpan(1),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Academic Information')
                    ->description('Student academic details and enrollment')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Select::make('form')
                                    ->label('Form')
                    ->required()
                                    ->options([
                                        1 => 'Form 1',
                                        2 => 'Form 2',
                                        3 => 'Form 3',
                                    ])
                                    ->native(false)
                                    ->columnSpan(1),
                                
                                Forms\Components\Select::make('programme')
                                    ->label('Programme')
                                    ->options([
                                        'Science' => 'Science',
                                        'Business' => 'Business',
                                        'Arts' => 'Arts',
                                        'General Arts' => 'General Arts',
                                        'General Science' => 'General Science',
                                        'Home Economics' => 'Home Economics',
                                        'Visual Arts' => 'Visual Arts',
                                        'Agriculture' => 'Agriculture',
                                    ])
                                    ->searchable()
                                    ->native(false)
                                    ->columnSpan(1),
                                
                Forms\Components\TextInput::make('house')
                                    ->label('House')
                    ->maxLength(50)
                                    ->placeholder('e.g., Red House')
                                    ->columnSpan(1),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                Forms\Components\TextInput::make('index_number')
                                    ->label('Index Number')
                    ->maxLength(50)
                                    ->placeholder('BECE/WASSCE index')
                                    ->columnSpan(1),
                                
                                Forms\Components\TextInput::make('ghana_card_number')
                                    ->label('Ghana Card Number')
                                    ->maxLength(20)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('GHA-12345678-A')
                                    ->helperText('Format: GHA-XXXXXXXX-X')
                                    ->columnSpan(1),
                                
                Forms\Components\TextInput::make('year_of_admission')
                                    ->label('Year of Admission')
                                    ->required()
                                    ->numeric()
                                    ->default(date('Y'))
                                    ->minValue(2020)
                                    ->maxValue(date('Y') + 1)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                        if ($get('school_id') && !$get('kd_shs_id')) {
                                            $school = School::find($get('school_id'));
                                            if ($school) {
                                                $idService = new KdShsIdService();
                                                $set('kd_shs_id', $idService->generateBySchoolId($school->id));
                                            }
                                        }
                                        // Auto-calculate completion year
                                        $set('completion_year', $state + 3);
                                    })
                                    ->columnSpan(1),
                                
                                Forms\Components\TextInput::make('completion_year')
                                    ->label('Completion Year')
                                    ->numeric()
                                    ->default(fn ($get) => ($get('year_of_admission') ?? date('Y')) + 3)
                                    ->minValue(2020)
                                    ->columnSpan(1),
                            ]),
                        
                        Forms\Components\Select::make('admission_status')
                            ->label('Admission Status')
                            ->required()
                            ->options([
                                'Active' => 'Active',
                                'Transfer' => 'Transfer',
                                'Completed' => 'Completed',
                                'Dropped' => 'Dropped',
                            ])
                            ->default('Active')
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_url')
                    ->label('Photo')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->size(50),
                
                Tables\Columns\TextColumn::make('kd_shs_id')
                    ->label('GES-SHS ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Student ID copied!')
                    ->icon('heroicon-o-identification')
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('ghana_card_number')
                    ->label('Ghana Card')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Ghana Card Number copied!')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn ($record) => trim("{$record->first_name} {$record->other_names} {$record->last_name}"))
                    ->searchable(['first_name', 'last_name', 'other_names'])
                    ->sortable()
                    ->weight('medium')
                    ->icon('heroicon-o-user'),
                
                Tables\Columns\IconColumn::make('gender')
                    ->label('Gender')
                    ->icon(fn (string $state): string => match ($state) {
                        'M' => 'heroicon-o-user',
                        'F' => 'heroicon-o-user',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'M' => 'info',
                        'F' => 'pink',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->school?->name)
                    ->icon('heroicon-o-academic-cap')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\BadgeColumn::make('form')
                    ->label('Form')
                    ->formatStateUsing(fn ($state) => "Form {$state}")
                    ->colors([
                        'primary' => 1,
                        'success' => 2,
                        'warning' => 3,
                    ])
                    ->icons([
                        'heroicon-o-academic-cap' => 1,
                        'heroicon-o-academic-cap' => 2,
                        'heroicon-o-graduation-cap' => 3,
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('programme')
                    ->label('Programme')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-book-open')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('year_of_admission')
                    ->label('Admission Year')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('admission_status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Active',
                        'info' => 'Transfer',
                        'warning' => 'Completed',
                        'danger' => 'Dropped',
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Active',
                        'heroicon-o-arrow-right-circle' => 'Transfer',
                        'heroicon-o-check-badge' => 'Completed',
                        'heroicon-o-x-circle' => 'Dropped',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('latestVerification.status')
                    ->label('Verification')
                    ->badge()
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
                    ->default('Pending')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('form')
                    ->label('Form')
                    ->options([
                        1 => 'Form 1',
                        2 => 'Form 2',
                        3 => 'Form 3',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('programme')
                    ->label('Programme')
                    ->options([
                        'Science' => 'Science',
                        'Business' => 'Business',
                        'Arts' => 'Arts',
                        'General Arts' => 'General Arts',
                        'General Science' => 'General Science',
                        'Home Economics' => 'Home Economics',
                        'Visual Arts' => 'Visual Arts',
                        'Agriculture' => 'Agriculture',
                    ])
                    ->multiple()
                    ->searchable(),
                
                Tables\Filters\SelectFilter::make('admission_status')
                    ->label('Admission Status')
                    ->options([
                        'Active' => 'Active',
                        'Transfer' => 'Transfer',
                        'Completed' => 'Completed',
                        'Dropped' => 'Dropped',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('gender')
                    ->label('Gender')
                    ->options([
                        'M' => 'Male',
                        'F' => 'Female',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('year_of_admission')
                    ->form([
                        Forms\Components\Select::make('year')
                            ->label('Admission Year')
                            ->options(fn () => collect(range(2020, date('Y') + 1))
                                ->mapWithKeys(fn ($year) => [$year => $year])
                                ->toArray())
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['year'],
                                fn (Builder $query, $year): Builder => $query->where('year_of_admission', $year),
                            );
                    }),
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
            ->emptyStateHeading('No students yet')
            ->emptyStateDescription('Get started by creating your first student.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Create Student')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Student Overview')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\ImageEntry::make('photo_url')
                                    ->label('Photo')
                                    ->circular()
                                    ->defaultImageUrl(url('/images/default-avatar.png'))
                                    ->size(100)
                                    ->columnSpan(1),
                                
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('kd_shs_id')
                                            ->label('GES-SHS ID')
                                            ->icon('heroicon-o-identification')
                                            ->copyable()
                                            ->badge()
                                            ->color('primary')
                                            ->size('lg'),
                                        
                                        Infolists\Components\TextEntry::make('ghana_card_number')
                                            ->label('Ghana Card Number')
                                            ->icon('heroicon-o-credit-card')
                                            ->copyable()
                                            ->badge()
                                            ->color('success')
                                            ->placeholder('Not provided'),
                                        
                                        Infolists\Components\TextEntry::make('full_name')
                                            ->label('Full Name')
                                            ->getStateUsing(fn ($record) => trim("{$record->first_name} {$record->other_names} {$record->last_name}"))
                                            ->icon('heroicon-o-user')
                                            ->weight('bold')
                                            ->size('lg'),
                                        
                                        Infolists\Components\TextEntry::make('gender')
                                            ->label('Gender')
                                            ->badge()
                                            ->color(fn ($state) => $state === 'M' ? 'info' : 'pink')
                                            ->icon(fn ($state) => $state === 'M' ? 'heroicon-o-user' : 'heroicon-o-user'),
                                        
                                        Infolists\Components\TextEntry::make('date_of_birth')
                                            ->label('Date of Birth')
                                            ->date('F j, Y')
                                            ->icon('heroicon-o-calendar')
                                            ->badge()
                                            ->color('gray'),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('School Information')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('school.name')
                                    ->label('School')
                                    ->icon('heroicon-o-academic-cap')
                                    ->badge()
                                    ->color('primary')
                                    ->url(fn ($record) => $record->school ? route('filament.admin.resources.schools.view', $record->school) : null),
                                
                                Infolists\Components\TextEntry::make('school.school_code')
                                    ->label('School Code')
                                    ->icon('heroicon-o-hashtag')
                                    ->badge()
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('school.region')
                                    ->label('Region')
                                    ->icon('heroicon-o-map-pin')
                                    ->badge()
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('school.district')
                                    ->label('District')
                                    ->icon('heroicon-o-map-pin')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ]),
                
                Infolists\Components\Section::make('Academic Information')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('form')
                                    ->label('Form')
                                    ->formatStateUsing(fn ($state) => "Form {$state}")
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-academic-cap')
                                    ->size('lg'),
                                
                                Infolists\Components\TextEntry::make('programme')
                                    ->label('Programme')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-book-open'),
                                
                                Infolists\Components\TextEntry::make('house')
                                    ->label('House')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-home')
                                    ->placeholder('Not assigned'),
                                
                                Infolists\Components\TextEntry::make('index_number')
                                    ->label('Index Number')
                                    ->icon('heroicon-o-identification')
                                    ->badge()
                                    ->color('gray')
                                    ->placeholder('Not provided'),
                                
                                Infolists\Components\TextEntry::make('year_of_admission')
                                    ->label('Year of Admission')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-calendar'),
                                
                                Infolists\Components\TextEntry::make('completion_year')
                                    ->label('Completion Year')
                                    ->badge()
                                    ->color('gray')
                                    ->icon('heroicon-o-calendar')
                                    ->placeholder('Not completed'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('admission_status')
                            ->label('Admission Status')
                            ->badge()
                            ->color(fn ($state) => match ($state) {
                                'Active' => 'success',
                                'Transfer' => 'info',
                                'Completed' => 'warning',
                                'Dropped' => 'danger',
                                default => 'gray',
                            })
                            ->icon(fn ($state) => match ($state) {
                                'Active' => 'heroicon-o-check-circle',
                                'Transfer' => 'heroicon-o-arrow-right-circle',
                                'Completed' => 'heroicon-o-check-badge',
                                'Dropped' => 'heroicon-o-x-circle',
                                default => 'heroicon-o-question-mark-circle',
                            }),
                    ]),
                
                Infolists\Components\Section::make('Verification Status')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Infolists\Components\TextEntry::make('latestVerification.status')
                            ->label('Verification Status')
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
                            })
                            ->default('Pending'),
                        
                        Infolists\Components\TextEntry::make('latestVerification.method')
                            ->label('Verification Method')
                            ->badge()
                            ->color('info')
                            ->placeholder('Not verified'),
                        
                        Infolists\Components\TextEntry::make('latestVerification.verified_at')
                            ->label('Verified At')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-calendar')
                            ->placeholder('Not verified'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GuardiansRelationManager::class,
            RelationManagers\VerificationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'view' => Pages\ViewStudent::route('/{record}'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
