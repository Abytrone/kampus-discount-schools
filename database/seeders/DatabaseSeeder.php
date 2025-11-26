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
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            RolePermissionSeeder::class,
            SchoolSeeder::class,
            UserSeeder::class,
        ]);

        if(app()->isLocal()){
            $this->call([
                StudentSeeder::class,
                GuardianSeeder::class,
                VerificationSeeder::class,
            ]);
        }
    }
}
