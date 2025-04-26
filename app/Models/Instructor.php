<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Instructor extends Model implements JWTSubject
{
    use HasFactory, SoftDeletes;
     protected $fillable = [
        'first_name',
        'last_name',
        'image',
        'date_of_birth',
        'bio',
        'info',
        'email',
        'password' ,
        'phone_number'
    ];
    protected $hidden = [
        'password',
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
    /**
     * JWT Identifier.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}