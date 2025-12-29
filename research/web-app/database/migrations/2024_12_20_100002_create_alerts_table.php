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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('detection_sessions')->onDelete('cascade');
            $table->integer('track_id')->nullable(); // Elephant track ID (if applicable)
            $table->enum('alert_type', ['warning_tts', 'alarm_sound', 'danger_tts', 'stop_alarm', 'zone_transition'])->default('alarm_sound');
            $table->string('message')->nullable(); // Alert message/content
            $table->timestamp('triggered_at'); // When alert was triggered
            $table->decimal('distance_meters', 8, 2)->nullable(); // Distance in meters (if applicable)
            $table->string('zone_name')->nullable(); // Zone name (SAFE, WARNING, DANGER)
            $table->json('metadata')->nullable(); // Additional metadata (JSON format)
            $table->timestamps();
            
            // Indexes
            $table->index('session_id');
            $table->index('alert_type');
            $table->index('triggered_at');
            $table->index('track_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};

