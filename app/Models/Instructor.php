<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Instructor extends Model
{
    use HasFactory;
     protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'date_of_birth',
        'bio',
        'info',
    ];

    protected $dates = ['date_of_birth'];

    // Accessor: Get Full Name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    // Relationship: Instructor can have many Courses
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}