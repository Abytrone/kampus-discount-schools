<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder generates permissions for all Filament resources, pages, and widgets
     * using Filament Shield's built-in permission generator.
     */
    public function run(): void
    {
        // Get all resources and generate permissions for each
        $resources = FilamentShield::getResources();
        if ($resources) {
            foreach ($resources as $resource) {
                FilamentShield::generateForResource($resource);
            }
            $this->command->info('Resource permissions generated!');
        }

        // Get all pages and generate permissions for each
        $pages = FilamentShield::getPages();
        if ($pages) {
            foreach ($pages as $page => $pageData) {
                FilamentShield::generateForPage($pageData['permission']);
            }
            $this->command->info('Page permissions generated!');
        }

        // Get all widgets and generate permissions for each
        $widgets = FilamentShield::getWidgets();
        if ($widgets) {
            foreach ($widgets as $widget => $widgetData) {
                FilamentShield::generateForWidget($widgetData['permission']);
            }
            $this->command->info('Widget permissions generated!');
        }

        $this->command->info('All permissions generated successfully!');
    }
}
