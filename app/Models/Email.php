<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Email extends Model
{
    use HasFactory;

    public $table = 'emails';

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
     * @param EmailGroup $emailGroup
     * @return Email
     */
    public static function createNewEmail(User $user, Business $business, EmailGroup $emailGroup): Email
    {
        $userEmail = $user->email;

        $email = new Email();

        if (is_null($userEmail)) {
            Log::info(
                "[CustomerManager]-[createNewEmail]: No Email For User" .
                ", User ID: " . $user->id .
                ", Business: " . $business->name .
                ", Group ID: " . $emailGroup->id .
                ", User Email: ". $userEmail
            );

            return $email;
        }

        $businessUserId = $business->id;

        $email->price = 0.001;
        $email->to_id = $user->id;
        $email->from_id = $businessUserId;
        $email->to_email = $userEmail;
        $email->group_id = $emailGroup->id;

        $email->save();
        $email->refresh();

        Log::info(
            "[CustomerManager]-[createNewEmail]: Email Was Created" .
            ", User ID: " . $user->id .
            ", Business: " . $business->name .
            ", Group ID: " . $emailGroup->id .
            ", User Email: ". $userEmail
        );

        return $email;
    }
}
