<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionMessage extends Model
{
    use HasFactory;

    protected $table = 'promotion_messages';
    protected $fillable = ['business_id', 'message'];
}
