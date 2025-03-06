<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_session_id',
    ];

    // Relationship: A record belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship: A record belongs to a course session
    public function session()
    {
        return $this->belongsTo(CourseSession::class, 'course_session_id');
    }
}