<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles based on the system requirements
        $roles = [
            [
                'name' => 'super_admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'kd_admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'school_admin',
                'guard_name' => 'web',
            ],
            [
                'name' => 'ambassador',
                'guard_name' => 'web',
            ],
            [
                'name' => 'readonly_partner',
                'guard_name' => 'web',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                $role
            );
        }

        $this->command->info('Roles created successfully!');
    }
}
