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
        Schema::create('zone_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('detection_sessions')->onDelete('cascade');
            $table->integer('track_id')->nullable(); // Elephant track ID
            $table->string('from_zone'); // Zone transitioned from (SAFE, WARNING, DANGER)
            $table->string('to_zone'); // Zone transitioned to (SAFE, WARNING, DANGER)
            $table->decimal('distance_meters', 8, 2); // Distance when transition occurred
            $table->timestamp('transitioned_at'); // When transition occurred
            $table->timestamps();
            
            // Indexes
            $table->index('session_id');
            $table->index('transitioned_at');
            $table->index(['from_zone', 'to_zone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_transitions');
    }
};

