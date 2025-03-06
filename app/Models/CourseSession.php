<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'department_id',
        'title',
        'title_he',
        'description',
        'description_he',
        'video'
    ];

    // Relationship: A session belongs to a course
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    // A CourseSession belongs to a Department
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}