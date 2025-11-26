<?php

namespace App\Filament\Widgets;

use App\Models\School;
use Filament\Widgets\ChartWidget;

class SchoolsByRegionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Schools Distribution by Region';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $regions = School::selectRaw('region, COUNT(*) as count')
            ->groupBy('region')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        $labels = $regions->pluck('region')->toArray();
        $data = $regions->pluck('count')->toArray();

        // Generate vibrant colors for each region
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // Blue
            'rgba(16, 185, 129, 0.8)',   // Green
            'rgba(245, 158, 11, 0.8)',   // Amber
            'rgba(239, 68, 68, 0.8)',    // Red
            'rgba(139, 92, 246, 0.8)',   // Purple
            'rgba(236, 72, 153, 0.8)',   // Pink
            'rgba(20, 184, 166, 0.8)',   // Teal
            'rgba(251, 146, 60, 0.8)',   // Orange
            'rgba(34, 197, 94, 0.8)',    // Emerald
            'rgba(168, 85, 247, 0.8)',   // Violet
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Number of Schools',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.8', '1', $color);
                    }, array_slice($colors, 0, count($data))),
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'callbacks' => [
                        'label' => "function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed + ' schools';
                            }
                            return label;
                        }",
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}

