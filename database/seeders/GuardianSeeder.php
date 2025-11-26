<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\Student;
use Illuminate\Database\Seeder;

class GuardianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $relationships = ['Father', 'Mother', 'Guardian', 'Uncle', 'Aunt', 'Grandfather', 'Grandmother'];
        
        // Ghanaian names for guardians
        $firstNames = [
            'Kwame', 'Kofi', 'Kojo', 'Yaw', 'Kwaku', 'Kwabena', 'Ama', 'Akosua', 'Adwoa', 'Abena',
            'Efua', 'Yaa', 'Afia', 'Emmanuel', 'Michael', 'David', 'Samuel', 'Daniel', 'Joseph', 'John',
            'Esther', 'Grace', 'Mary', 'Patience', 'Comfort', 'Gifty', 'Joyce', 'Priscilla', 'Ruth', 'Sarah'
        ];

        $lastNames = [
            'Mensah', 'Osei', 'Asante', 'Boateng', 'Owusu', 'Amoah', 'Appiah', 'Darko', 'Gyasi', 'Kwarteng',
            'Agyeman', 'Bonsu', 'Frimpong', 'Adjei', 'Amoako', 'Asiedu', 'Baffour', 'Bekoe', 'Danso', 'Fosu'
        ];

        $students = Student::all();
        
        foreach ($students as $student) {
            // Each student has 1-2 guardians
            $guardianCount = rand(1, 2);
            
            for ($i = 0; $i < $guardianCount; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $name = $firstName . ' ' . $lastName;
                $relationship = $relationships[array_rand($relationships)];
                
                // Generate phone number (Ghanaian format)
                $phone = '+233' . rand(20, 29) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
                
                // Generate email
                $email = strtolower($firstName . '.' . $lastName . '@gmail.com');
                
                Guardian::create([
                    'student_id' => $student->id,
                    'name' => $name,
                    'relationship' => $relationship,
                    'phone' => $phone,
                    'address' => rand(1, 100) . ' Main Street, Accra',
                    'email' => rand(0, 1) ? $email : null, // 50% chance of having email
                ]);
            }
        }
    }
}
