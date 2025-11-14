<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organ extends Model
{
    use HasFactory;

    // Define the fillable fields
    protected $fillable = [
        'organ_name',
        'blood_type',
        'donor_name',
        'donor_age',
        'donor_gender',
        'organ_type',
        'organ_condition',
    ];

    // Optionally, define any relationships or other methods here if needed

    public function requests()
    {
        return $this->hasMany(OrganRequest::class, 'organ_id');
    }

}
