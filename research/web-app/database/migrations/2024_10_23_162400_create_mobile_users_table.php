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
        Schema::create('mobile_users', function (Blueprint $table) {
            $table->id(); // Primary Key: auto-incrementing ID
            $table->string('full_name'); // Full Name
            $table->string('email')->unique(); // Email (Unique)
            $table->string('password'); // Password (hashed)
            $table->string('phone_number')->nullable(); // Phone Number (Optional)
            $table->enum('gender', ['male', 'female', 'other'])->nullable(); // Gender (Optional)
            $table->date('dob'); // Date of Birth
            $table->enum('status', ['pending', 'approved'])->default('pending'); // Status: 'pending' or 'approved'
            $table->timestamps(); // Created at and Updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_users');
    }
};
