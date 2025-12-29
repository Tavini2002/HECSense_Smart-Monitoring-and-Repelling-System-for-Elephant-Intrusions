<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZoneTransition extends Model
{
    protected $fillable = [
        'session_id',
        'track_id',
        'from_zone',
        'to_zone',
        'distance_meters',
        'transitioned_at',
    ];

    protected $casts = [
        'transitioned_at' => 'datetime',
        'distance_meters' => 'decimal:2',
    ];

    /**
     * Get the session that owns this zone transition
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(DetectionSession::class, 'session_id');
    }

    /**
     * Scope for filtering by zone
     */
    public function scopeByZone($query, string $zone)
    {
        return $query->where('to_zone', $zone);
    }
}

