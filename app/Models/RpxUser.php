<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RpxUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['sms_opt_in', 'phone_number'];

    protected $hidden = ['last_known_ip_address', 'confirm_attempts', 'confirmed', 'created_at', 'last_log_in', 'deleted_at'];

    /*
    * Rpx User belongs to User.
    */
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id');
    }

    public function userLocation()
    {
        return $this->hasOne('App\Models\UserLocation', 'id');
    }
}
