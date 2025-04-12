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
        'assigned_by',
        'status',
        'amount_paid',
        'remaining_amount',
        'payment_status',
        'is_event'
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
     public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    /**
     * Get the course that belongs to this enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function isFullyPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function calculateRemainingAmount()
    {
        return $this->remaining_amount;
    }
}