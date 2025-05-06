<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedExperience extends Model
{
    use HasFactory;

    protected $table = 'shared_experiences';
    protected $fillable = ['business_id', 'image', 'comment'];

}
