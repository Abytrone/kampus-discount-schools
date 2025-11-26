<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentsByFormChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Students Distribution by Form';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $forms = ['Form 1', 'Form 2', 'Form 3'];
        $data = [];
        
        foreach ($forms as $form) {
            $data[] = Student::where('form', $form)
                ->where('admission_status', 'Active')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Active Students',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',   // Blue for Form 1
                        'rgba(16, 185, 129, 0.8)',   // Green for Form 2
                        'rgba(245, 158, 11, 0.8)',  // Amber for Form 3
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $forms,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1000,
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}

