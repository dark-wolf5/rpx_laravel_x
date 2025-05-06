<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Sms extends Model
{
    use HasFactory;

    public $table = 'sms';

    protected $fillable = ['sent'];

    public function fromUser()
    {
        return $this->belongsTo('App\Models\User', 'from_id');
    }

    public function toUser()
    {
        return $this->belongsTo('App\Models\User', 'to_id');
    }

    /**
     * Create a new sms for business promotions.
     * @param User $user
     * @param Business $business
     * @param SmsGroup $smsGroup
     * @return Sms
     */
    public function createNewSms(User $user, Business $business, SmsGroup $smsGroup)
    {
        $rpxUser = $user->rpxUser()->first();
        $phoneNumber = $rpxUser->phone_number;

        $sms = new Sms;

        if (is_null($phoneNumber)) {
            return $sms;
        }

        $businessUserId = $business->id;

        $sms->price = 0.0079;
        $sms->to_id = $user->id;
        $sms->from_id = $businessUserId;
        $sms->to_phone = $phoneNumber;
        $sms->group_id = $smsGroup->id;
        $sms->save();
        $sms->refresh();

        return $sms;
    }
}
