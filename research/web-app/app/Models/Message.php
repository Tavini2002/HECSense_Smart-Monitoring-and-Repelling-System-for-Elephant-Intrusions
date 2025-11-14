<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural form of the model name
    protected $table = 'messages';

    // Specify the fillable fields
    protected $fillable = [
        'user_id',
        'organ_name',
        'blood_type',
        'message',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(MobileUser::class, 'user_id');
    }
}
