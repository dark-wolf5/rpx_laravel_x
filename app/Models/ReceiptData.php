<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceiptData extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'redeemable_id', 'image_path', 'status'];
}
