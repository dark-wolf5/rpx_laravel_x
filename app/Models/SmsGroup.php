<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsGroup extends Model
{
    use HasFactory;

    public $table = 'sms_group';

    protected $fillable = ['total_sent', 'total', 'price'];
}
