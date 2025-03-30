<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InstructorRating extends Model
{
    use HasFactory;

    protected $fillable = ['instructor_id', 'user_id', 'rating', 'review','is_accept'];

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}