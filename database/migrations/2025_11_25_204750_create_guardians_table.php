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
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('name', 200);
            $table->string('relationship', 50)->comment('Father, Mother, Guardian, etc.');
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
