<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
      protected $fillable = [
        'device_id',
        'model',
        'brand',
        'manufacturer',
        'os_version',
        'sdk_version',
        'system_name',
        'device_name',
        'ip_address',
        'latitude',
        'longitude',
        'contact_info',
        'status',
    ];
}