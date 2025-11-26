<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('role', [
                'KD_Admin',
                'School_Admin',
                'Ambassador',
                'ReadOnly_Partner'
            ])->default('School_Admin')->after('phone');
            $table->foreignId('school_id')->nullable()->after('role')->constrained('schools')->onDelete('set null');
            $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('school_id');
            
            // Indexes
            $table->index('role');
            $table->index('school_id');
            $table->index('status');
        });
        
        // Rename name to full_name using DB facade
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE users CHANGE name full_name VARCHAR(255) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['school_id']);
            $table->dropIndex(['status']);
            
            $table->dropColumn(['phone', 'role', 'school_id', 'status']);
        });
        
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE users CHANGE full_name name VARCHAR(255) NOT NULL');
    }
};
