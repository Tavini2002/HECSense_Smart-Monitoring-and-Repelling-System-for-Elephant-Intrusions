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
        Schema::create('detection_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_name')->nullable(); // Name/description of the session
            $table->enum('source_type', ['video_upload', 'camera', 'stream'])->default('camera');
            $table->string('source_path')->nullable(); // Path to video file or camera index
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['running', 'completed', 'stopped', 'error'])->default('running');
            $table->integer('total_frames')->default(0);
            $table->decimal('confidence_threshold', 3, 2)->default(0.10); // Detection confidence threshold used
            $table->text('notes')->nullable(); // Additional notes about the session
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('status');
            $table->index('started_at');
            $table->index('source_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detection_sessions');
    }
};

