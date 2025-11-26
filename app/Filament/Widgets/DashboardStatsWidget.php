<?php

namespace App\Filament\Widgets;

use App\Models\School;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class DashboardStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total Schools
        $totalSchools = School::count();
        $activeSchools = School::where('status', 'Active')->count();
        $growthRate = $totalSchools > 0 ? round(($activeSchools / $totalSchools) * 100, 1) : 0;
        
        $schoolsChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $schoolsChartData[] = School::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Total Students
        $totalStudents = Student::count();
        $activeStudents = Student::where('admission_status', 'Active')->count();
        $activePercentage = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0;
        
        $studentsChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $studentsChartData[] = Student::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Active Students This Month
        $thisMonth = Student::where('admission_status', 'Active')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $activeChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $activeChartData[] = Student::where('admission_status', 'Active')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // Regions Covered
        $totalRegions = School::distinct('region')->count('region');
        $topRegion = School::selectRaw('region, COUNT(*) as count')
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->first();

        $topRegionName = $topRegion?->region ?? 'N/A';
        $topRegionCount = $topRegion?->count ?? 0;
        
        $regionsChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $regionsChartData[] = School::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        return [
            Stat::make('Total Schools', number_format($totalSchools))
                ->description("{$activeSchools} active ({$growthRate}%)")
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('primary')
                ->chart($schoolsChartData)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
            
            Stat::make('Total Students', number_format($totalStudents))
                ->description("{$activeStudents} active ({$activePercentage}%)")
                ->descriptionIcon('heroicon-o-academic-cap')
                ->color('success')
                ->chart($studentsChartData)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
            
            Stat::make('Active Students', number_format($activeStudents))
                ->description("{$thisMonth} registered this month")
                ->descriptionIcon('heroicon-o-user-group')
                ->color('info')
                ->chart($activeChartData)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
            
            Stat::make('Regions Covered', number_format($totalRegions))
                ->description("Top: {$topRegionName} ({$topRegionCount} schools)")
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('warning')
                ->chart($regionsChartData)
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition-shadow',
                ]),
        ];
    }
}

