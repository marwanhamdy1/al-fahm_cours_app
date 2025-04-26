<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;
     use HasFactory;
    protected $table = 'courses';

    protected $fillable = [
        'title',
        'title_he',
        'price',
        'image',
        'earnings_point',
        'address',
        'address_he',
        'description',
        'description_he',
        'start_date',
        'end_date',
        'max_people',
        'signed_people',
        'age_range',
        'session_count',
        'category_id',
        'instructor_id',
        'active',
        'type',
        'rating_count',
        'rating_sum',
        'item_type'
    ];

    protected $dates = ['start_date', 'end_date'];

    // Relationship: A course belongs to a category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship: A course belongs to an instructor
    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
    // Relationship: A course has many course sessions
    public function department()
    {
        return $this->hasMany(Department::class);
    }

    // Relationship: A course has many course sessions
    public function sessions()
    {
        return $this->hasMany(CourseSession::class);
    }

    // Get all users signed up for the course (through sessions)
    public function usersSessions()
    {
        return $this->belongsToMany(User::class, 'user_course_sessions', 'course_id', 'user_id');
    }
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

}