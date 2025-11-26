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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->integer('school_code')->unique()->index()->comment('Official GES School Code');
            $table->string('name');
            $table->string('category', 10)->nullable()->comment('Category A, B, C, etc.');
            $table->string('school_type', 50)->default('SHS')->comment('SHS, Technical, Vocational, etc.');
            $table->enum('gender_type', ['Boys', 'Girls', 'Mixed'])->default('Mixed')->comment('Gender classification');
            $table->string('region', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('location', 200)->nullable()->comment('Specific location/town');
            $table->text('address')->nullable();
            $table->json('programmes')->nullable()->comment('Array of programmes offered');
            $table->integer('num_programmes')->default(0)->comment('Number of programmes offered');
            $table->string('status_type', 50)->nullable()->comment('Day, Boarding, Day/Boarding');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->integer('serial_number')->nullable()->comment('Original serial number from GES data');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('status');
            $table->index('region');
            $table->index('district');
            $table->index('school_type');
            $table->index('gender_type');
            $table->index('category');
            $table->index(['region', 'district']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
