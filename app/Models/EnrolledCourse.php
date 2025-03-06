<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrolledCourse extends Model
{
    use HasFactory;

    protected $table = 'enrolled_courses';

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the user that owns the enrollment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that belongs to this enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}