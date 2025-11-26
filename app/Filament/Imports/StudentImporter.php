<?php

namespace App\Filament\Imports;

use App\Models\Student;
use App\Models\School;
use App\Services\KdShsIdService;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getOptionsFormComponents(): array
    {
        return [
            // You can add import options here if needed
        ];
    }

    public static function getColumns(): array
    {
        return [
            'kd_shs_id',
            'school_id',
            'first_name',
            'last_name',
            'other_names',
            'gender',
            'date_of_birth',
            'photo_url',
            'form',
            'programme',
            'house',
            'index_number',
            'ghana_card_number',
            'year_of_admission',
            'completion_year',
            'admission_status',
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has been completed and ' . number_format($import->successful_rows) . ' ';
        $body .= $import->successful_rows === 1 ? 'student has' : 'students have';
        $body .= ' been imported.';

        if ($import->failed_rows > 0) {
            $body .= ' ' . number_format($import->failed_rows) . ' ';
            $body .= $import->failed_rows === 1 ? 'row has' : 'rows have';
            $body .= ' failed to import.';
        }

        return $body;
    }

    public function resolveRecord(): ?Student
    {
        // Try to find existing student by GES-SHS ID or Ghana Card Number
        $kdShsId = $this->data['kd_shs_id'] ?? null;
        $ghanaCardNumber = $this->data['ghana_card_number'] ?? null;
        
        if ($kdShsId && !empty(trim($kdShsId))) {
            $student = Student::where('kd_shs_id', trim($kdShsId))->first();
            if ($student) {
                return $student;
            }
        }
        
        if ($ghanaCardNumber && !empty(trim($ghanaCardNumber))) {
            $student = Student::where('ghana_card_number', trim($ghanaCardNumber))->first();
            if ($student) {
                return $student;
            }
        }
        
        return new Student();
    }

    public function beforeCreate(): void
    {
        // Auto-generate GES-SHS ID if not provided
        if (empty($this->data['kd_shs_id']) && !empty($this->data['school_id'])) {
            $idService = new KdShsIdService();
            $this->data['kd_shs_id'] = $idService->generateBySchoolId($this->data['school_id']);
        }
        
        // Auto-calculate completion year if not provided
        if (empty($this->data['completion_year']) && !empty($this->data['year_of_admission'])) {
            $this->data['completion_year'] = (int)$this->data['year_of_admission'] + 3;
        }
    }

    public function afterCreate(): void
    {
        // Any post-creation logic can go here
    }

    public function getValidationRules(): array
    {
        return [
            'kd_shs_id' => ['nullable', 'string', 'max:50', 'unique:students,kd_shs_id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'other_names' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'in:M,F'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1990-01-01'],
            'photo_url' => ['nullable', 'string', 'max:500'],
            'school_id' => ['required', 'exists:schools,id'],
            'form' => ['required', 'in:1,2,3,Form 1,Form 2,Form 3'],
            'programme' => ['nullable', 'string', 'max:50'],
            'house' => ['nullable', 'string', 'max:50'],
            'index_number' => ['nullable', 'string', 'max:50'],
            'ghana_card_number' => ['nullable', 'string', 'max:20', 'unique:students,ghana_card_number', 'regex:/^GHA-\d{8}-[A-Z]$/'],
            'year_of_admission' => ['required', 'integer', 'min:2020', 'max:' . (date('Y') + 1)],
            'completion_year' => ['nullable', 'integer', 'min:2020'],
            'admission_status' => ['required', 'in:Active,Transfer,Completed,Dropped'],
        ];
    }

    public function mutateBeforeCreate(array $data): array
    {
        // Convert form string to integer if needed
        if (isset($data['form'])) {
            $form = $data['form'];
            if (is_string($form)) {
                $form = match(strtolower(trim($form))) {
                    'form 1', '1' => 1,
                    'form 2', '2' => 2,
                    'form 3', '3' => 3,
                    default => 1,
                };
            }
            $data['form'] = $form;
        }
        
        // Convert school name/code to ID if needed
        if (isset($data['school_id']) && !is_numeric($data['school_id'])) {
            $schoolIdentifier = trim($data['school_id']);
            
            // Try to find by school code first (exact match)
            $school = School::where('school_code', $schoolIdentifier)->first();
            
            // If not found, try by code as integer
            if (!$school && is_numeric($schoolIdentifier)) {
                $school = School::where('school_code', (int)$schoolIdentifier)->first();
            }
            
            // If not found, try by name (partial match)
            if (!$school) {
                $school = School::where('name', 'like', "%{$schoolIdentifier}%")->first();
            }
            
            if ($school) {
                $data['school_id'] = $school->id;
            } else {
                throw new \Exception("School not found: {$schoolIdentifier}. Please use a valid school code or name.");
            }
        }
        
        // Format date of birth - handle multiple formats
        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            try {
                // Try different date formats
                $dateFormats = ['Y-m-d', 'Y/m/d', 'd-m-Y', 'd/m/Y', 'm-d-Y', 'm/d/Y'];
                $parsed = false;
                
                foreach ($dateFormats as $format) {
                    try {
                        $date = Carbon::createFromFormat($format, trim($data['date_of_birth']));
                        $data['date_of_birth'] = $date->format('Y-m-d');
                        $parsed = true;
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                // If none of the formats worked, try Carbon's parse
                if (!$parsed) {
                    $data['date_of_birth'] = Carbon::parse($data['date_of_birth'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                throw new \Exception("Invalid date format for date_of_birth: {$data['date_of_birth']}. Use YYYY-MM-DD format.");
            }
        }
        
        // Format Ghana Card Number (uppercase and validate format)
        if (isset($data['ghana_card_number']) && !empty(trim($data['ghana_card_number']))) {
            $ghanaCard = strtoupper(trim($data['ghana_card_number']));
            // Remove spaces
            $ghanaCard = str_replace(' ', '', $ghanaCard);
            $data['ghana_card_number'] = $ghanaCard;
        } else {
            // Set to null if empty
            $data['ghana_card_number'] = null;
        }
        
        // Handle photo_url - support both URLs and file paths
        if (isset($data['photo_url']) && !empty(trim($data['photo_url']))) {
            $photoUrl = trim($data['photo_url']);
            
            // Check if it's a URL (http:// or https://)
            if (filter_var($photoUrl, FILTER_VALIDATE_URL) && (str_starts_with($photoUrl, 'http://') || str_starts_with($photoUrl, 'https://'))) {
                // It's a URL, use it directly
                $data['photo_url'] = $photoUrl;
            } else {
                // It's a file path - try to handle it
                // Check if it's already a storage path (starts with storage/)
                if (str_starts_with($photoUrl, 'storage/')) {
                    // Already a storage path, use it as is
                    $data['photo_url'] = $photoUrl;
                } else {
                    // Try to download from URL if it's a valid URL without protocol
                    // Or check if file exists in the expected directory
                    $photoPath = $this->handlePhotoFile($photoUrl, $data);
                    $data['photo_url'] = $photoPath;
                }
            }
        } else {
            // Set to null if empty
            $data['photo_url'] = null;
        }
        
        // Clean up empty strings to null for optional fields
        $optionalFields = ['other_names', 'house', 'index_number', 'programme', 'completion_year', 'kd_shs_id'];
        foreach ($optionalFields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }
        
        // Ensure year_of_admission is integer
        if (isset($data['year_of_admission'])) {
            $data['year_of_admission'] = (int)$data['year_of_admission'];
        }
        
        // Ensure completion_year is integer if provided
        if (isset($data['completion_year']) && !empty($data['completion_year'])) {
            $data['completion_year'] = (int)$data['completion_year'];
        }
        
        return $data;
    }

    /**
     * Handle photo file - download from URL or use existing file
     */
    protected function handlePhotoFile(string $photoUrl, array $data): ?string
    {
        $storageDirectory = 'students/photos';
        $publicDisk = Storage::disk('public');
        
        // Ensure directory exists
        if (!$publicDisk->exists($storageDirectory)) {
            $publicDisk->makeDirectory($storageDirectory, 0755, true);
        }
        
        // Try to determine if it's a URL without protocol or a file path
        // If it looks like a URL (contains domain), try to download
        if (preg_match('/^[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,}/', $photoUrl)) {
            // Looks like a domain, try to download with https://
            try {
                $fullUrl = (str_starts_with($photoUrl, '//') ? 'https:' : 'https://') . $photoUrl;
                $response = Http::timeout(10)->get($fullUrl);
                
                if ($response->successful()) {
                    $extension = $this->getFileExtensionFromUrl($fullUrl) ?: 'jpg';
                    $filename = Str::slug(($data['first_name'] ?? 'student') . '-' . ($data['last_name'] ?? 'photo')) . '-' . time() . '.' . $extension;
                    $filePath = $storageDirectory . '/' . $filename;
                    
                    $publicDisk->put($filePath, $response->body());
                    return 'storage/' . $filePath;
                }
            } catch (\Exception $e) {
                // If download fails, return null or original value
                return null;
            }
        }
        
        // Check if file exists in public storage
        $filePath = $storageDirectory . '/' . basename($photoUrl);
        if ($publicDisk->exists($filePath)) {
            return 'storage/' . $filePath;
        }
        
        // Check if it's a relative path from public directory
        $relativePath = ltrim($photoUrl, '/');
        if ($publicDisk->exists($relativePath)) {
            return 'storage/' . $relativePath;
        }
        
        // If nothing works, return null
        return null;
    }

    /**
     * Get file extension from URL or content type
     */
    protected function getFileExtensionFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            if ($extension) {
                return strtolower($extension);
            }
        }
        
        // Default to jpg if no extension found
        return 'jpg';
    }

    public function getImportJobOptions(): array
    {
        return [
            'timeout' => 3600, // 1 hour timeout for large imports
        ];
    }

    public function getFailedImportRow(Import $import, array $data, array $errors): array
    {
        return [
            'row' => $import->getProcessedRows() + 1,
            'data' => $data,
            'errors' => $errors,
        ];
    }
}

