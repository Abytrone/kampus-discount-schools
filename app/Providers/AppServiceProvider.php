<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//         if (app()->environment('production')) {
//            URL::forceScheme('https');
//         }else{
//            URL::forceScheme('http');
//         }

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
            'pink' => Color::Pink,
            'purple' => Color::Purple,
            'orange' => Color::Orange,
            'teal' => Color::Teal,
            'cyan' => Color::Cyan,
            'indigo' => Color::Indigo,
            'violet' => Color::Violet,
            'rose' => Color::Rose,
        ]);
    }
}
