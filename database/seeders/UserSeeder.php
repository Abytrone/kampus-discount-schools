<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles (they should already exist from RoleSeeder)
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $kdAdminRole = Role::firstOrCreate(['name' => 'kd_admin', 'guard_name' => 'web']);
        $schoolAdminRole = Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
        $ambassadorRole = Role::firstOrCreate(['name' => 'ambassador', 'guard_name' => 'web']);
        $readonlyPartnerRole = Role::firstOrCreate(['name' => 'readonly_partner', 'guard_name' => 'web']);

        // Create Super Admin (first KD Admin gets super_admin role)
        $superAdmin = User::create([
            'full_name' => 'KampusDiscount Admin',
            'email' => 'admin@kampusdiscount.com',
            'password' => Hash::make('password'),
            'phone' => '+233 24 000 0001',
            'role' => 'KD_Admin',
            'school_id' => null,
            'status' => 'Active',
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole($superAdminRole);

        // Create KD Admin
        $kdAdmin = User::create([
            'full_name' => 'John Mensah',
            'email' => 'john.mensah@kampusdiscount.com',
            'password' => Hash::make('password'),
            'phone' => '+233 24 000 0002',
            'role' => 'KD_Admin',
            'school_id' => null,
            'status' => 'Active',
            'email_verified_at' => now(),
        ]);
        $kdAdmin->assignRole($kdAdminRole);

        // Create School Admins for each school (optimized)
        $schools = School::all();
        $totalSchools = $schools->count();
        $this->command->info("Creating users for {$totalSchools} schools...");

        $passwordHash = Hash::make('password'); // Hash once, reuse
        $bar = $this->command->getOutput()->createProgressBar($totalSchools);
        $bar->start();

        // Use transaction for better performance
        DB::beginTransaction();
        try {
            foreach ($schools as $index => $school) {
                // Generate unique email based on school code
                $schoolCodeStr = (string) $school->school_code;
                $emailDomain = 'school' . $schoolCodeStr . '.edu.gh';

                // Principal/Headmaster
                $principal = User::create([
                    'full_name' => $school->name . ' Principal',
                    'email' => 'principal' . $schoolCodeStr . '@' . $emailDomain,
                    'password' => $passwordHash,
                    'phone' => '+233 24 ' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . ' ' . rand(1000, 9999),
                    'role' => 'School_Admin',
                    'school_id' => $school->id,
                    'status' => 'Active',
                    'email_verified_at' => now(),
                ]);
                $principal->assignRole($schoolAdminRole);

                // Admin staff
                $admin = User::create([
                    'full_name' => $school->name . ' Admin',
                    'email' => 'admin' . $schoolCodeStr . '@' . $emailDomain,
                    'password' => $passwordHash,
                    'phone' => '+233 24 ' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . ' ' . rand(1000, 9999),
                    'role' => 'School_Admin',
                    'school_id' => $school->id,
                    'status' => 'Active',
                    'email_verified_at' => now(),
                ]);
                $admin->assignRole($schoolAdminRole);

                $bar->advance();

                // Clear cache every 50 schools to prevent memory issues
                if (($index + 1) % 50 === 0) {
                    DB::commit();
                    DB::beginTransaction();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $bar->finish();
        $this->command->newLine();

        // Create some Ambassadors (randomly assigned to schools)
        $ambassadorNames = [
            ['Kwame Asante', 'kwame.asante@kampusdiscount.com'],
            ['Ama Osei', 'ama.osei@kampusdiscount.com'],
            ['Kofi Boateng', 'kofi.boateng@kampusdiscount.com'],
            ['Akosua Mensah', 'akosua.mensah@kampusdiscount.com'],
            ['Yaw Darko', 'yaw.darko@kampusdiscount.com'],
            ['Adwoa Frimpong', 'adwoa.frimpong@kampusdiscount.com'],
        ];

        foreach ($ambassadorNames as $ambassador) {
            $ambassadorUser = User::create([
                'full_name' => $ambassador[0],
                'email' => $ambassador[1],
                'password' => $passwordHash,
                'phone' => '+233 24 ' . rand(200, 299) . ' ' . rand(1000, 9999),
                'role' => 'Ambassador',
                'school_id' => $schools->random()->id,
                'status' => 'Active',
                'email_verified_at' => now(),
            ]);
            $ambassadorUser->assignRole($ambassadorRole);
        }

        // Create ReadOnly Partners (banks, etc.)
        $partners = [
            ['Access Bank Ghana', 'api@accessbank.com'],
            ['Ghana Education Service', 'api@ges.gov.gh'],
        ];

        foreach ($partners as $partner) {
            $partnerUser = User::create([
                'full_name' => $partner[0] . ' API User',
                'email' => $partner[1],
                'password' => $passwordHash,
                'phone' => '+233 24 ' . rand(300, 399) . ' ' . rand(1000, 9999),
                'role' => 'ReadOnly_Partner',
                'school_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ]);
            $partnerUser->assignRole($readonlyPartnerRole);
        }

        $this->command->info('✅ Users created and roles assigned successfully!');
    }
}
