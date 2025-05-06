<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailConfirmation extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'email_is_verified', 'confirmation_token', 'expires_at'];
}
