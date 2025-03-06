<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'departments_id',
        'name',
        'description',
        'video'
    ];

    // Relationship: A session belongs to a course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}