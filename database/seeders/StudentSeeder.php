<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Services\KdShsIdService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates students for all schools with realistic data that matches
     * each school's programmes and characteristics.
     */
    public function run(): void
    {
        $idService = new KdShsIdService();

        // Expanded Ghanaian first names
        $maleFirstNames = [
            'Kwame', 'Kofi', 'Kojo', 'Yaw', 'Kwaku', 'Kwabena', 'Kweku', 'Nana', 'Emmanuel', 'Michael',
            'David', 'Samuel', 'Daniel', 'Joseph', 'John', 'Paul', 'Peter', 'James', 'Isaac', 'Benjamin',
            'Joshua', 'Nathaniel', 'Elijah', 'Caleb', 'Noah', 'Ethan', 'Aaron', 'Moses', 'Solomon', 'Jeremiah',
            'Timothy', 'Stephen', 'Mark', 'Luke', 'Andrew', 'Philip', 'Thomas', 'Matthew', 'Simon', 'Judas',
            'Agyeman', 'Amoah', 'Bonsu', 'Frimpong', 'Kumi', 'Manu', 'Ofori', 'Opoku', 'Prempeh', 'Sarpong'
        ];

        $femaleFirstNames = [
            'Ama', 'Akosua', 'Adwoa', 'Abena', 'Efua', 'Yaa', 'Afia', 'Aba', 'Esther', 'Grace',
            'Mary', 'Patience', 'Comfort', 'Gifty', 'Joyce', 'Priscilla', 'Ruth', 'Sarah', 'Abigail', 'Deborah',
            'Hannah', 'Rebecca', 'Rachel', 'Naomi', 'Elizabeth', 'Martha', 'Lydia', 'Dorcas', 'Phoebe', 'Susanna',
            'Mary', 'Anna', 'Salome', 'Joanna', 'Martha', 'Mary Magdalene', 'Tabitha', 'Lois', 'Eunice', 'Priscilla',
            'Agyeman', 'Amoah', 'Bonsu', 'Frimpong', 'Kumi', 'Manu', 'Ofori', 'Opoku', 'Prempeh', 'Sarpong'
        ];

        // Ghanaian last names
        $lastNames = [
            'Mensah', 'Osei', 'Asante', 'Boateng', 'Owusu', 'Amoah', 'Appiah', 'Darko', 'Gyasi', 'Kwarteng',
            'Agyeman', 'Bonsu', 'Frimpong', 'Adjei', 'Amoako', 'Asiedu', 'Baffour', 'Bekoe', 'Danso', 'Fosu',
            'Kumi', 'Manu', 'Nkrumah', 'Ofori', 'Opoku', 'Owusu', 'Prempeh', 'Sarpong', 'Tetteh', 'Yeboah',
            'Acheampong', 'Adu', 'Agyei', 'Anane', 'Antwi', 'Asamoah', 'Boadi', 'Dankwa', 'Gyamfi', 'Kyerematen',
            'Mensah', 'Okyere', 'Sarpong', 'Tettey', 'Yeboah', 'Agyapong', 'Boateng', 'Darko', 'Fosu', 'Gyasi'
        ];

        // Other names (optional middle names)
        $otherNames = [
            'Agyeman', 'Kofi', 'Nana', 'Junior', 'Senior', 'Ama', 'Yaw', 'Kojo', 'Kweku', 'Kwabena',
            null, null, null, null, null, null, null, null, null, null
        ];

        // House names
        $houses = [
            'Red House', 'Blue House', 'Green House', 'Yellow House', 'Gold House', 'Silver House',
            'Eagle House', 'Lion House', 'Panther House', 'Tiger House', 'Phoenix House', 'Dragon House',
            null, null, null, null
        ];

        $admissionStatuses = [
            'Active', 'Active', 'Active', 'Active', 'Active', 'Active', 'Active', 'Active',
            'Transfer', 'Completed', 'Dropped'
        ];

        $schools = School::all();
        $currentYear = date('Y');
        $totalStudents = 0;

        $this->command->info("🎓 Creating students for {$schools->count()} schools...");
        $this->command->newLine();

        $bar = $this->command->getOutput()->createProgressBar($schools->count());
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        foreach ($schools as $schoolIndex => $school) {
            // Determine student count based on school category and type
            $baseCount = match($school->category) {
                'A' => rand(150, 300),  // Category A schools have more students
                'B' => rand(100, 200),
                'C' => rand(80, 150),
                default => rand(50, 120),
            };

            // Boarding schools typically have more students
            if (str_contains($school->status_type ?? '', 'Boarding')) {
                $baseCount = (int) ($baseCount * 1.3);
            }

            // Create students for different admission years (3 years of data)
            $years = [$currentYear - 2, $currentYear - 1, $currentYear];
            $schoolStudents = 0;

            foreach ($years as $year) {
                // Distribute students across forms
                $form1Count = (int) ($baseCount * 0.4); // 40% in Form 1
                $form2Count = (int) ($baseCount * 0.35); // 35% in Form 2
                $form3Count = (int) ($baseCount * 0.25); // 25% in Form 3

                // Adjust counts based on year
                if ($year === $currentYear - 2) {
                    // These students are now in Form 3
                    $form = 3;
                    $count = $form3Count;
                } elseif ($year === $currentYear - 1) {
                    // These students are now in Form 2
                    $form = 2;
                    $count = $form2Count;
                } else {
                    // Current year students are in Form 1
                    $form = 1;
                    $count = $form1Count;
                }

                // Get school programmes to match students
                $schoolProgrammes = $school->programmes ?? [];
                if (empty($schoolProgrammes) || !is_array($schoolProgrammes)) {
                    // Fallback programmes if school doesn't have any
                    $schoolProgrammes = ['General Arts', 'General Science', 'Business'];
                }

                // Create students for this form
                for ($i = 0; $i < $count; $i++) {
                    // Match gender to school type
                    $gender = match($school->gender_type) {
                        'Boys' => 'M',
                        'Girls' => 'F',
                        default => rand(0, 1) ? 'M' : 'F',
                    };

                    // Select name based on gender
                    $firstName = $gender === 'M'
                        ? $maleFirstNames[array_rand($maleFirstNames)]
                        : $femaleFirstNames[array_rand($femaleFirstNames)];

                    $lastName = $lastNames[array_rand($lastNames)];
                    $otherName = $otherNames[array_rand($otherNames)];

                    // Select programme from school's available programmes
                    $programme = $schoolProgrammes[array_rand($schoolProgrammes)];

                    // Calculate age based on form (Form 1: 15-16, Form 2: 16-17, Form 3: 17-18)
                    $age = 14 + $form + rand(0, 1);
                    $dateOfBirth = Carbon::now()
                        ->subYears($age)
                        ->subMonths(rand(0, 11))
                        ->subDays(rand(0, 28));

                    // Generate GES-SHS ID
                    $gesShsId = $idService->generateBySchoolId($school->id);

                    // Generate BECE index number (format: 123456/2024)
                    $beceIndex = rand(100000, 999999) . '/' . $year;

                    // Generate Ghana Card Number (format: GHA-XXXXXXXX-X)
                    // Format: GHA-{8 digits}-{1 letter}
                    $ghanaCardNumber = 'GHA-' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) . '-' .
                                       strtoupper(chr(65 + rand(0, 25))); // Random letter A-Z

                    // Determine admission status
                    $admissionStatus = 'Active';
                    if ($form === 3 && rand(1, 10) <= 2) {
                        // 20% of Form 3 students have completed
                        $admissionStatus = 'Completed';
                    } elseif (rand(1, 100) <= 2) {
                        // 2% chance of other statuses
                        $admissionStatus = $admissionStatuses[array_rand($admissionStatuses)];
                    }

                    // Calculate completion year
                    $completionYear = null;
                    if ($admissionStatus === 'Completed') {
                        $completionYear = $year + 3;
                    }

                    try {
                        Student::create([
                            'kd_shs_id' => $gesShsId,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'other_names' => $otherName,
                            'gender' => $gender,
                            'date_of_birth' => $dateOfBirth,
                            'school_id' => $school->id,
                            'form' => $form,
                            'programme' => $programme,
                            'house' => $houses[array_rand($houses)],
                            'index_number' => $beceIndex,
                            'ghana_card_number' => $ghanaCardNumber,
                            'year_of_admission' => $year,
                            'completion_year' => $completionYear,
                            'admission_status' => $admissionStatus,
                        ]);

                        $schoolStudents++;
                        $totalStudents++;
                    } catch (\Exception $e) {
                        // Skip duplicate IDs or other errors
                        continue;
                    }
                }
            }

            // Update progress
            if (($schoolIndex + 1) % 100 === 0) {
                $bar->setMessage("Created {$totalStudents} students so far...");
            }
            $bar->advance();
        }

        $bar->setMessage('Complete!');
        $bar->finish();
        $this->command->newLine(2);

        $this->command->info("✅ Students seeding completed!");
        $this->command->info("📊 Total students created: {$totalStudents}");
        $this->command->info("📈 Average per school: " . round($totalStudents / $schools->count(), 2));
    }
}
