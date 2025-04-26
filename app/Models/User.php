<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
       protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'identity_id',
        'email',
        'phone_number',
        'password',
        'image',
        'color',
        'verify_code',
        'role',
        'date_of_birth',
        'school_name',
        'grade_name',
        'educational_stage',
        'neighborhood',
        'parent_id',
        'parent_type',
        'child_type',
        'mother_name',
        'mother_identity_id',
        'points',
        "status",
        "fcm_token",
        'balance'
    ];
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }


    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }
    public function enrolledCourses(){
        return $this->hasMany(EnrolledCourse::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}