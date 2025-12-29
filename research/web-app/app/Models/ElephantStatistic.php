<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElephantStatistic extends Model
{
    protected $fillable = [
        'session_id',
        'track_id',
        'total_detections',
        'calm_count',
        'warning_count',
        'aggressive_count',
        'avg_speed_kmph',
        'max_speed_kmph',
        'min_speed_kmph',
        'first_detected_at',
        'last_detected_at',
        'duration_seconds',
    ];

    protected $casts = [
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
        'avg_speed_kmph' => 'decimal:2',
        'max_speed_kmph' => 'decimal:2',
        'min_speed_kmph' => 'decimal:2',
    ];

    /**
     * Get the session that owns this statistic
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(DetectionSession::class, 'session_id');
    }

    /**
     * Get primary behavior (most common)
     */
    public function getPrimaryBehaviorAttribute(): string
    {
        $behaviors = [
            'aggressive' => $this->aggressive_count,
            'warning' => $this->warning_count,
            'calm' => $this->calm_count,
        ];
        
        arsort($behaviors);
        return array_key_first($behaviors);
    }
}

