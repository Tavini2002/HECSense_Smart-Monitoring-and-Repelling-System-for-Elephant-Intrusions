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
        Schema::create('elephant_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('detection_sessions')->onDelete('cascade');
            $table->integer('track_id'); // Elephant track ID
            $table->integer('total_detections')->default(0); // Total number of detections for this track
            $table->integer('calm_count')->default(0); // Number of calm detections
            $table->integer('warning_count')->default(0); // Number of warning detections
            $table->integer('aggressive_count')->default(0); // Number of aggressive detections
            $table->decimal('avg_speed_kmph', 6, 2)->default(0); // Average speed
            $table->decimal('max_speed_kmph', 6, 2)->default(0); // Maximum speed
            $table->decimal('min_speed_kmph', 6, 2)->default(0); // Minimum speed
            $table->timestamp('first_detected_at')->nullable(); // First detection timestamp
            $table->timestamp('last_detected_at')->nullable(); // Last detection timestamp
            $table->integer('duration_seconds')->default(0); // Duration tracked (seconds)
            $table->timestamps();
            
            // Indexes
            $table->index('session_id');
            $table->index('track_id');
            $table->index(['session_id', 'track_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elephant_statistics');
    }
};

