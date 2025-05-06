<?php

namespace App\Models;

use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusinessExposure extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'business_exposure';

    protected $fillable = ['total_exposure'];

    public function business(): BelongsTo
    {
        return $this->belongsTo('App\Models\Business', 'business_id', 'id');
    }

    public function updateTotalExposure($userId){

    }

    public function createTotalExposure(){
        Log::info('createTotalExposure ');
        return new BusinessExposure();
    }
}
