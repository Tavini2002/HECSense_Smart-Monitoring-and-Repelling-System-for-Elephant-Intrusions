<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    /**
     * Database table associated with the model
     */
    protected $table = 'messages';

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'user_id',
        'organ_name',
        'blood_type',
        'message',
    ];

    /**
     * Get the mobile user related to this message
     */
    public function mobileUser()
    {
        return $this->belongsTo(MobileUser::class, 'user_id');
    }
}

