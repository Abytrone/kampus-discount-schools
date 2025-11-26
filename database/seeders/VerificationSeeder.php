<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VerificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = Student::all();
        $schoolAdmins = User::where('role', 'School_Admin')->get();
        $ambassadors = User::where('role', 'Ambassador')->get();
        $kdAdmins = User::where('role', 'KD_Admin')->get();

        $methods = ['SchoolUpload', 'SchoolAdminApproval', 'AmbassadorVerification', 'SelfRegistration'];
        $statuses = ['Pending', 'Approved', 'Rejected'];
        $remarks = [
            'Verified by school admin from upload list',
            'Student verified through bulk upload',
            'Verified by ambassador during field visit',
            'Self-registered and approved by school admin',
            'Verified and approved',
            'Pending review',
            null,
        ];

        foreach ($students as $student) {
            // Most students are verified (80% approved, 15% pending, 5% rejected)
            $rand = rand(1, 100);
            
            if ($rand <= 80) {
                $status = 'Approved';
                $method = rand(0, 1) ? 'SchoolUpload' : 'SchoolAdminApproval';
                
                // Assign verifier based on method
                if ($method === 'SchoolUpload' || $method === 'SchoolAdminApproval') {
                    $verifier = $schoolAdmins->where('school_id', $student->school_id)->random();
                } else {
                    $verifier = $ambassadors->random();
                }
                
                $verifiedAt = Carbon::now()->subDays(rand(1, 180));
            } elseif ($rand <= 95) {
                $status = 'Pending';
                $method = 'SchoolUpload';
                $verifier = null;
                $verifiedAt = null;
            } else {
                $status = 'Rejected';
                $method = 'SelfRegistration';
                $verifier = $schoolAdmins->where('school_id', $student->school_id)->random();
                $verifiedAt = Carbon::now()->subDays(rand(1, 30));
            }

            Verification::create([
                'student_id' => $student->id,
                'method' => $method,
                'verified_by_user_id' => $verifier?->id,
                'status' => $status,
                'remarks' => $remarks[array_rand($remarks)],
                'verified_at' => $verifiedAt,
            ]);
        }
    }
}
