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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('kd_shs_id', 50)->unique()->index();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('other_names', 100)->nullable();
            $table->enum('gender', ['M', 'F'])->default('M');
            $table->date('date_of_birth');
            $table->string('photo_url')->nullable();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->tinyInteger('form')->default(1)->comment('1, 2, or 3');
            $table->string('programme', 50)->nullable()->comment('Science, Business, Arts, etc.');
            $table->string('house', 50)->nullable();
            $table->string('index_number', 50)->nullable()->comment('BECE/WASSCE/school index');
            $table->string('ghana_card_number', 20)->nullable()->unique()->comment('Ghana Card Number (GHA-XXXXXXXX-X)');
            $table->year('year_of_admission');
            $table->year('completion_year')->nullable();
            $table->enum('admission_status', ['Active', 'Transfer', 'Completed', 'Dropped'])->default('Active');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['school_id', 'form', 'admission_status']);
            $table->index(['last_name', 'first_name', 'school_id']);
            $table->index('year_of_admission');
            $table->index('programme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
