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
        Schema::create('detections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('detection_sessions')->onDelete('cascade');
            $table->integer('track_id'); // Elephant track ID from YOLO tracker
            $table->integer('frame_number'); // Frame number in the video/session
            $table->timestamp('detected_at'); // Timestamp when detection occurred
            $table->decimal('timestamp', 10, 3)->nullable(); // Video timestamp in seconds
            
            // Detection data
            $table->decimal('confidence', 5, 4); // Detection confidence (0.0000 to 1.0000)
            $table->enum('behavior', ['calm', 'warning', 'aggressive']); // Detected behavior/mood
            $table->decimal('speed_kmph', 6, 2)->default(0); // Speed in km/h
            $table->decimal('aggression_score', 3, 2)->default(0); // Aggression score (0.0 to 1.0)
            
            // Bounding box coordinates
            $table->integer('bbox_x1'); // Top-left X
            $table->integer('bbox_y1'); // Top-left Y
            $table->integer('bbox_x2'); // Bottom-right X
            $table->integer('bbox_y2'); // Bottom-right Y
            $table->integer('bbox_width'); // Bounding box width
            $table->integer('bbox_height'); // Bounding box height
            $table->integer('center_x'); // Center X coordinate
            $table->integer('center_y'); // Center Y coordinate
            
            $table->boolean('alert_triggered')->default(false); // Whether alert was triggered
            $table->string('alert_type')->nullable(); // Type of alert (warning_tts, alarm_sound, etc.)
            $table->timestamps();
            
            // Indexes
            $table->index('session_id');
            $table->index('track_id');
            $table->index('detected_at');
            $table->index('behavior');
            $table->index('frame_number');
            $table->index(['session_id', 'track_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detections');
    }
};

