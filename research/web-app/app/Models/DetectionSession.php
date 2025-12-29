<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetectionSession extends Model
{
    protected $fillable = [
        'session_name',
        'source_type',
        'source_path',
        'started_at',
        'ended_at',
        'status',
        'total_frames',
        'confidence_threshold',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'confidence_threshold' => 'decimal:2',
    ];

    /**
     * Get all detections for this session
     */
    public function detections(): HasMany
    {
        return $this->hasMany(Detection::class, 'session_id');
    }

    /**
     * Get all alerts for this session
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'session_id');
    }

    /**
     * Get all zone transitions for this session
     */
    public function zoneTransitions(): HasMany
    {
        return $this->hasMany(ZoneTransition::class, 'session_id');
    }

    /**
     * Get all statistics for this session
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(ElephantStatistic::class, 'session_id');
    }

    /**
     * Get duration in seconds
     */
    public function getDurationAttribute(): int
    {
        if ($this->started_at && $this->ended_at) {
            return $this->ended_at->diffInSeconds($this->started_at);
        }
        return 0;
    }

    /**
     * Get statistics summary
     */
    public function getStatsSummaryAttribute(): array
    {
        $detections = $this->detections;
        
        return [
            'total_detections' => $detections->count(),
            'calm_count' => $detections->where('behavior', 'calm')->count(),
            'warning_count' => $detections->where('behavior', 'warning')->count(),
            'aggressive_count' => $detections->where('behavior', 'aggressive')->count(),
            'total_alerts' => $this->alerts->count(),
            'unique_elephants' => $detections->unique('track_id')->count(),
        ];
    }
}

