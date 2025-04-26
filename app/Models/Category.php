<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'categories';
    protected $fillable = [
        'name_ar',
        'name_he',
        'image',
        'description',
    ];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        return  $this->image;
    }
    public function courses()
{
    return $this->hasMany(Course::class);
}

}