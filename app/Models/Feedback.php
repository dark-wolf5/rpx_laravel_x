<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'feedbacks';

    protected $hidden = ['id'];

    protected $fillable = ['user_id', 'feedback_text', 'ledger_record_id'];


    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
