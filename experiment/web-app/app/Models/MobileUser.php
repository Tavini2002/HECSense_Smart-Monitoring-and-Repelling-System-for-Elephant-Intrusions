<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileUser extends Model
{
    use HasFactory;

    /**
     * Attributes allowed for mass assignment
     */
    protected $fillable = [
        'full_name',
        'username',
        'email',
        'password',
        'phone_number',
        'gender',
        'dob',
        'blood_type',
        'organ',
        'allergies',
        'status',
    ];

    /**
     * Fetch all organ requests created by the user
     */
    public function organRequestList()
    {
        return $this->hasMany(OrganRequest::class, 'user_id');
    }
}
