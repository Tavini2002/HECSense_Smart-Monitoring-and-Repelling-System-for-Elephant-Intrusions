<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'session_id',
        'track_id',
        'alert_type',
        'message',
        'triggered_at',
        'distance_meters',
        'zone_name',
        'metadata',
    ];

    protected $casts = [
        'triggered_at' => 'datetime',
        'distance_meters' => 'decimal:2',
        'metadata' => 'array',
    ];

    /**
     * Get the session that owns this alert
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(DetectionSession::class, 'session_id');
    }

    /**
     * Scope for filtering by alert type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }
}

