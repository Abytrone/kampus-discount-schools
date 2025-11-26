<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds schools from the official Ghana Education Service (GES) database.
     */
    public function run(): void
    {
        $jsonPath = base_path('ghana_high_schools.json');

        if (!File::exists($jsonPath)) {
            $this->command->error('ghana_high_schools.json file not found at: ' . $jsonPath);
            $this->command->info('Please ensure the JSON file is in the project root directory.');
            return;
        }

        $jsonContent = File::get($jsonPath);
        $jsonData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON format: ' . json_last_error_msg());
            return;
        }

        if (!isset($jsonData['schools']) || !is_array($jsonData['schools'])) {
            $this->command->error('Invalid JSON structure! Expected "schools" array.');
            return;
        }

        $schools = $jsonData['schools'];
        $total = count($schools);

        if ($total === 0) {
            $this->command->warn('No schools found in JSON file!');
            return;
        }

        $this->command->info("📚 Found {$total} schools in GES database. Seeding...");
        $this->command->newLine();

        $bar = $this->command->getOutput()->createProgressBar($total);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $regions = [];
        $districts = [];

        foreach ($schools as $index => $schoolData) {
            try {
                // Validate required fields
                if (empty($schoolData['SchoolCode']) || empty($schoolData['SchoolName'])) {
                    $skipped++;
                    $bar->setMessage("Skipping invalid entry #{$index}");
                    $bar->advance();
                    continue;
                }

                $schoolCode = (int) $schoolData['SchoolCode'];
                $schoolName = trim($schoolData['SchoolName']);

                // Track regions and districts for statistics
                if (!empty($schoolData['Region'])) {
                    $regions[$schoolData['Region']] = ($regions[$schoolData['Region']] ?? 0) + 1;
                }
                if (!empty($schoolData['District'])) {
                    $districts[$schoolData['District']] = ($districts[$schoolData['District']] ?? 0) + 1;
                }

                // Generate contact info based on school name
                $nameSlug = strtolower(
                    preg_replace('/[^A-Za-z0-9]+/', '',
                        substr($schoolName, 0, 15)
                    )
                );
                $contactPhone = '+233 24 ' . rand(100, 999) . ' ' . rand(1000, 9999);
                $contactEmail = 'info@' . $nameSlug . '.edu.gh';

                // Build address from location components
                $addressParts = array_filter([
                    $schoolData['Location'] ?? null,
                    $schoolData['District'] ?? null,
                    $schoolData['Region'] ?? null,
                ]);
                $address = !empty($addressParts) ? implode(', ', $addressParts) : null;

                // Handle programmes - can be array or string
                $programmesArray = [];
                if (!empty($schoolData['Programmes'])) {
                    if (is_array($schoolData['Programmes'])) {
                        // Already an array, just clean it
                        $programmesArray = array_values(array_filter(
                            array_map('trim', $schoolData['Programmes']),
                            fn($p) => !empty($p)
                        ));
                    } else {
                        // Legacy: comma-separated string
                        $programmesArray = array_values(array_filter(
                            array_map('trim', explode(',', $schoolData['Programmes'])),
                            fn($p) => !empty($p)
                        ));
                    }
                }

                // Determine school type
                $schoolType = $schoolData['SchoolType'] ?? 'SHS';

                // Map status type
                $statusType = $schoolData['Status'] ?? 'Day/Boarding';

                $school = School::updateOrCreate(
                    ['school_code' => $schoolCode],
                    [
                        'name' => $schoolName,
                        'category' => $schoolData['Category'] ?? null,
                        'school_type' => $schoolType,
                        'gender_type' => $schoolData['Gender'] ?? 'Mixed',
                        'region' => $schoolData['Region'] ?? null,
                        'district' => $schoolData['District'] ?? null,
                        'location' => $schoolData['Location'] ?? null,
                        'address' => $address,
                        'programmes' => !empty($programmesArray) ? $programmesArray : null,
                        'num_programmes' => count($programmesArray),
                        'status_type' => $statusType,
                        'status' => 'Active',
                        'contact_phone' => $contactPhone,
                        'contact_email' => $contactEmail,
                        'serial_number' => isset($schoolData['SN']) ? (int) $schoolData['SN'] : null,
                    ]
                );

                if ($school->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }

                // Update progress message every 100 schools
                if (($index + 1) % 100 === 0) {
                    $bar->setMessage("Processed " . ($index + 1) . " schools...");
                }
            } catch (\Exception $e) {
                $skipped++;
                if ($skipped <= 5) { // Only show first 5 errors to avoid spam
                    $this->command->newLine();
                    $this->command->warn("⚠️  Skipped: {$schoolData['SchoolName']} - {$e->getMessage()}");
                }
            }

            $bar->advance();
        }

        $bar->setMessage('Complete!');
        $bar->finish();
        $this->command->newLine(2);

        // Display statistics
        $this->command->info("✅ Schools seeding completed successfully!");
        $this->command->table(
            ['Metric', 'Count'],
            [
                ['Created', $created],
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total Processed', $created + $updated],
            ]
        );

        $this->command->info("📊 Coverage Statistics:");
        $this->command->table(
            ['Region', 'Schools'],
            collect($regions)
                ->sortDesc()
                ->take(10)
                ->map(fn ($count, $region) => [$region, $count])
                ->toArray()
        );

        $this->command->info("🎓 Total regions: " . count($regions));
        $this->command->info("📍 Total districts: " . count($districts));
    }
}
