<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name','course_id'];

    /**
     * Get the courses that belong to this department.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}