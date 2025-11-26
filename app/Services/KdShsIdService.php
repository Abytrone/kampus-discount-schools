<?php

namespace App\Services;

use App\Models\School;
use App\Models\Student;

class KdShsIdService
{
    /**
     * Generate a unique GES-SHS ID for a student
     * Pattern: GES-SHS-{school_code}-{index_number}
     * 
     * @param int $schoolId
     * @param string|null $indexNumber Optional index number from BECE/WASSCE
     * @return string
     */
    public function generateBySchoolId(int $schoolId, ?string $indexNumber = null): string
    {
        $school = School::findOrFail($schoolId);
        
        // If index number is provided, use it directly
        if ($indexNumber) {
            // Clean the index number (remove any existing prefix)
            $cleanIndex = preg_replace('/^GES-SHS-\d+-/', '', $indexNumber);
            $cleanIndex = preg_replace('/[^0-9]/', '', $cleanIndex);
            
            // If it's already a valid format, return it
            if (strlen($cleanIndex) >= 5) {
                return "GES-SHS-{$school->school_code}-{$cleanIndex}";
            }
        }
        
        // Generate a unique index number for this school
        // Get the last index number used for this school
        $lastStudent = Student::where('school_id', $schoolId)
            ->where('kd_shs_id', 'like', "GES-SHS-{$school->school_code}-%")
            ->orderByRaw('CAST(SUBSTRING_INDEX(kd_shs_id, "-", -1) AS UNSIGNED) DESC')
            ->value('kd_shs_id');

        // Extract the index number from the last ID
        $index = 1;
        if ($lastStudent) {
            $parts = explode('-', $lastStudent);
            $lastIndex = (int) end($parts);
            $index = $lastIndex + 1;
        }

        // Format index as 6-digit zero-padded number
        $formattedIndex = str_pad($index, 6, '0', STR_PAD_LEFT);

        return "GES-SHS-{$school->school_code}-{$formattedIndex}";
    }

    /**
     * Generate ID using school code directly
     */
    public function generate(int|string $schoolCode, ?string $indexNumber = null): string
    {
        $school = School::where('school_code', $schoolCode)->first();
        if (!$school) {
            throw new \Exception("School with code {$schoolCode} not found");
        }
        
        return $this->generateBySchoolId($school->id, $indexNumber);
    }
}
