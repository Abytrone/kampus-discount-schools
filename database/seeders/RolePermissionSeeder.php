<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder assigns permissions to roles based on their responsibilities.
     */
    public function run(): void
    {
        // Get all roles
        $superAdmin = Role::where('name', 'super_admin')->first();
        $kdAdmin = Role::where('name', 'kd_admin')->first();
        $schoolAdmin = Role::where('name', 'school_admin')->first();
        $ambassador = Role::where('name', 'ambassador')->first();
        $readonlyPartner = Role::where('name', 'readonly_partner')->first();

        // Super Admin gets ALL permissions
        if ($superAdmin) {
            $superAdmin->givePermissionTo(Permission::all());
            $this->command->info('Super Admin: All permissions assigned');
        }

        // KD Admin: Full access to all resources except Shield management
        if ($kdAdmin) {
            $kdAdminPermissions = Permission::whereNotLike('name', '%shield%')
                ->whereNotLike('name', '%role%')
                ->get();
            $kdAdmin->givePermissionTo($kdAdminPermissions);
            $this->command->info('KD Admin: Permissions assigned');
        }

        // School Admin: Manage their own school and students
        if ($schoolAdmin) {
            $schoolAdminPermissions = Permission::where(function ($query) {
                $query->where('name', 'like', '%school%')
                    ->orWhere('name', 'like', '%student%')
                    ->orWhere('name', 'like', '%guardian%')
                    ->orWhere('name', 'like', '%verification%');
            })->get();
            $schoolAdmin->givePermissionTo($schoolAdminPermissions);
            $this->command->info('School Admin: Permissions assigned');
        }

        // Ambassador: Can view and verify students
        if ($ambassador) {
            $ambassadorPermissions = Permission::where(function ($query) {
                $query->where('name', 'like', '%student%view%')
                    ->orWhere('name', 'like', '%verification%create%')
                    ->orWhere('name', 'like', '%verification%update%')
                    ->orWhere('name', 'like', '%verification%view%')
                    ->orWhere('name', 'like', '%school%view%');
            })->get();
            $ambassador->givePermissionTo($ambassadorPermissions);
            $this->command->info('Ambassador: Permissions assigned');
        }

        // ReadOnly Partner: Only view students for eligibility checks
        if ($readonlyPartner) {
            $readonlyPermissions = Permission::where(function ($query) {
                $query->where('name', 'like', '%student%view%')
                    ->orWhere('name', 'like', '%student%view_any%');
            })->get();
            $readonlyPartner->givePermissionTo($readonlyPermissions);
            $this->command->info('ReadOnly Partner: Permissions assigned');
        }

        $this->command->info('Role permissions assigned successfully!');
    }
}
