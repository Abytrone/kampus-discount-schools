<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, create roles
        $this->call([
            RoleSeeder::class,
        ]);

        // Then, generate permissions for all Filament resources
        $this->call([
            PermissionSeeder::class,
        ]);

        // Assign permissions to roles
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Seed application data
        $this->call([
            SchoolSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            GuardianSeeder::class,
            VerificationSeeder::class,
        ]);
    }
}
