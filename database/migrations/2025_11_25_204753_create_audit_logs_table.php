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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 100)->comment('Student, School, Verification, etc.');
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['CREATE', 'UPDATE', 'DELETE', 'VIEW'])->default('VIEW');
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('timestamp')->useCurrent();
            $table->json('details')->nullable();
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['entity_type', 'entity_id']);
            $table->index('performed_by_user_id');
            $table->index('timestamp');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
