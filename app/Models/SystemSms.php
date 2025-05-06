<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSms extends Model
{
    use HasFactory;

    public $table = 'system_sms';

    protected $fillable = ['sent'];

    public function toUser()
    {
        return $this->belongsTo('App\Models\User', 'to_id');
    }

    /**
     * Create a system SMS to let the user know that they've opted in to
     * SMS messaging.
     * @param User $user
     * @return void
     */
    public function createSettingsSms(User $user, string $phoneNumber)
    {
        $sms = new SystemSms;

        if (is_null($phoneNumber)) {
            return $sms;
        }

        $sms->price = 0.0079;
        $sms->to_id = $user->id;
        $sms->to_phone = $phoneNumber;
        $sms->save();
        $sms->refresh();

        return $sms;
    }

    /**
     * Create a system SMS to let the user know that they haven't finished
     * setting up their account yet.
     * @param User $user
     * @return void
     */
    public function createAccountCompletionReminderSms(User $user, string $phoneNumber)
    {
        $sms = new SystemSms;

        if (is_null($phoneNumber)) {
            return $sms;
        }

        $sms->price = 0.0079;
        $sms->to_id = $user->id;
        $sms->to_phone = $phoneNumber;
        $sms->save();
        $sms->refresh();

        return $sms;
    }



    public function createInviteContactSms(string $phoneNumber)
    {
        $sms = new SystemSms;
        $sms->price = 0.0079;
        $sms->to_phone = $phoneNumber;
        $sms->save();
        $sms->refresh();

        return $sms;
    }

    /**
     * Create a system SMS to let the user know that they've opted in to
     * SMS messaging.
     * @param User $user
     * @return void
     */
    public function createBonusLpSms(User $user, string $phoneNumber)
    {
        $sms = new SystemSms;

        if (is_null($phoneNumber)) {
            return $sms;
        }

        $sms->price = 0.0079;
        $sms->to_id = $user->id;
        $sms->to_phone = $phoneNumber;
        $sms->save();
        $sms->refresh();

        return $sms;
    }

    public function createResetPasswordSms(User $user, string $phoneNumber)
    {
        $sms = new SystemSms;

        if (is_null($phoneNumber)) {
            return $sms;
        }

        $sms->price = 0.0079;
        $sms->to_id = $user->id;
        $sms->to_phone = $phoneNumber;
        $sms->save();
        $sms->refresh();

        return $sms;
    }

    /**
     * Create a system SMS to let the user know that they've opted in to
     * SMS messaging.
     * @param User $user
     * @return void
     */
    public function createInviteMeetUpSms(null | User $user, string $phoneNumber)
    {
        $sms = new SystemSms;
        if (is_null($phoneNumber)) {
            return $sms;
        }

        $sms->price = 0.0079;
        if (! is_null($user)) {
            $sms->to_id = $user->id;
        } else {
            $sms->to_id = null;
        }

        $sms->to_phone = $phoneNumber;
        $sms->save();
        $sms->refresh();

        return $sms;
    }

}
