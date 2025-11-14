<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganRequest extends Model
{
    use HasFactory;

    // Define the table name if it's not pluralized by Laravel
    protected $table = 'organ_requests';

    // Specify which attributes are mass assignable
    protected $fillable = [
        'organ_id',
        'user_id',
        'date',
        'status'
    ];

    // Relationships (optional if you want to define them)


    public function organ()
    {
        return $this->belongsTo(Organ::class, 'organ_id');
    }

    public function user()
    {
        return $this->belongsTo(MobileUser::class, 'user_id'); // Assuming MobileUser is the user model
    }

}
