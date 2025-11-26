<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentStudentsTableWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->with('school')
                    ->latest()
                    ->limit(10)
            )
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
                    ->copyable()
                    ->icon('heroicon-o-identification')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Name')
                    ->getStateUsing(fn (Student $record): string => 
                        trim("{$record->first_name} {$record->other_names} {$record->last_name}")
                    )
                    ->searchable(['first_name', 'last_name', 'other_names'])
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office-2')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->school?->name),
                Tables\Columns\TextColumn::make('form')
                    ->label('Form')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Form 1' => 'info',
                        'Form 2' => 'success',
                        'Form 3' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Form 1' => 'heroicon-o-academic-cap',
                        'Form 2' => 'heroicon-o-book-open',
                        'Form 3' => 'heroicon-o-graduation-cap',
                        default => 'heroicon-o-user',
                    }),
                Tables\Columns\TextColumn::make('programme')
                    ->label('Programme')
                    ->badge()
                    ->color('primary')
                    ->limit(20),
                Tables\Columns\TextColumn::make('admission_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Active' => 'success',
                        'Inactive' => 'danger',
                        'Graduated' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Active' => 'heroicon-o-check-circle',
                        'Inactive' => 'heroicon-o-x-circle',
                        'Graduated' => 'heroicon-o-academic-cap',
                        default => 'heroicon-o-question-mark-circle',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No students yet')
            ->emptyStateDescription('Students will appear here once they are registered.')
            ->emptyStateIcon('heroicon-o-user-group');
    }
}

