<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detection extends Model
{
    protected $fillable = [
        'session_id',
        'track_id',
        'frame_number',
        'detected_at',
        'timestamp',
        'confidence',
        'behavior',
        'speed_kmph',
        'aggression_score',
        'bbox_x1',
        'bbox_y1',
        'bbox_x2',
        'bbox_y2',
        'bbox_width',
        'bbox_height',
        'center_x',
        'center_y',
        'alert_triggered',
        'alert_type',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'timestamp' => 'decimal:3',
        'confidence' => 'decimal:4',
        'speed_kmph' => 'decimal:2',
        'aggression_score' => 'decimal:2',
        'alert_triggered' => 'boolean',
    ];

    /**
     * Get the session that owns this detection
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(DetectionSession::class, 'session_id');
    }

    /**
     * Get bounding box as array
     */
    public function getBoundingBoxAttribute(): array
    {
        return [
            'x1' => $this->bbox_x1,
            'y1' => $this->bbox_y1,
            'x2' => $this->bbox_x2,
            'y2' => $this->bbox_y2,
            'width' => $this->bbox_width,
            'height' => $this->bbox_height,
        ];
    }

    /**
     * Get center point as array
     */
    public function getCenterAttribute(): array
    {
        return [
            'x' => $this->center_x,
            'y' => $this->center_y,
        ];
    }

    /**
     * Scope for filtering by behavior
     */
    public function scopeByBehavior($query, string $behavior)
    {
        return $query->where('behavior', $behavior);
    }

    /**
     * Scope for filtering by track ID
     */
    public function scopeByTrack($query, int $trackId)
    {
        return $query->where('track_id', $trackId);
    }
}

