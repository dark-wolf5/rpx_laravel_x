<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailGroup extends Model
{
    use HasFactory;

    public $table = 'emails_group';

    protected $fillable = ['total_sent', 'total', 'price'];
}
