<?php

namespace App\Services;

use App\Helpers\Sms\SmsAndCallTwimlHelper;
use App\Models\RpxUser;
use App\Models\SystemSms;
use App\Models\User;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class Points
{
    public function redeemedPoints(
        RpxUser $rpxUser,
        User $user,
        SystemSms $sms,
        string $businessPoints,
        string $businessName,
        string $bonusPoints
    ) {
        try
        {
            $lang = 'en';
            $sid = config('services.twilio.account_sid');
            $token = config('services.twilio.token');

            $client = new Client($sid, $token);
            $langHelper = new SmsAndCallTwimlHelper($lang);
            $body = $langHelper->getPointsRedeemedSmsTxt(
                $businessPoints,
                $businessName,
                $user->email,
                $rpxUser->first_name,
                $bonusPoints
            );

            $client->messages->create(
                $rpxUser->phone_number,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $body,
                ]
            );

            // Update SMS message in DB;
            $sms->update([
                'sent' => true,
            ]);

            $totalPoints = intval($bonusPoints) + intval($businessPoints);

            Log::info(
                '[Points]-[redeemedPoints]: Message Sent' .
                ', User ID: '. $user->id .
                ', Phone-Number: ' . $rpxUser->phone_number .
                ', Business: ' . $businessName .
                ', Total Points: ' . $totalPoints .
                ', Bonus Points: ' . $bonusPoints
            );
        }
        catch(TwilioException $e)
        {
            $errorCode = '';
            switch($e->getCode())
            {
                case '21211':
                    $errorCode = 'phoneNumber.invalid';
                    break;
                case '21612':
                case '21408':
                case '21610':
                case '21614':
                    $errorCode = 'phoneNumber.unavailable';
                    break;
                default:
                    $errorCode = $e->getCode();
            }

            Log::error(
                '[Points]-[redeemedPoints]: Message Failed' .
                ', Phone-Number: ' . $rpxUser->phone_number .
                ", Business: " . $businessName .
                ", Error Code: " . $errorCode .
                ", Error Message: " . $e->getMessage()
            );

            return $errorCode;
        }
    }

    public function sendBonusLp(
        RpxUser $rpxUser,
        User $user,
        SystemSms $sms,
        string $totalPoints,
        string $businessName,
        string $range1,
        string $range2,
        string $range3,
        string $dayOfWeek
    ) {
        try
        {
            $lang = 'en';
            $sid = config('services.twilio.account_sid');
            $token = config('services.twilio.token');

            $client = new Client($sid, $token);
            $langHelper = new SmsAndCallTwimlHelper($lang);
            $body = $langHelper->getBonusLpSmsTxt(
                $totalPoints,
                $businessName,
                $rpxUser->first_name,
                $range1,
                $range2,
                $range3,
                $dayOfWeek
            );

            $client->messages->create(
                $rpxUser->phone_number,
                [
                    'from' => config('services.twilio.from'),
                    'body' => $body,
                ]
            );

            // Update SMS message in DB;
            $sms->update([
                'sent' => true,
            ]);

            Log::info(
                '[Points]-[Bonus Points Awarded]: Message Sent' .
                ', User ID: '. $user->id .
                ', Phone-Number: ' . $rpxUser->phone_number .
                ', Business: ' . $businessName .
                ', Total Points: ' . $totalPoints
            );
        }
        catch(TwilioException $e)
        {
            $errorCode = '';
            switch($e->getCode())
            {
                case '21211':
                    $errorCode = 'phoneNumber.invalid';
                    break;
                case '21612':
                case '21408':
                case '21610':
                case '21614':
                    $errorCode = 'phoneNumber.unavailable';
                    break;
                default:
                    $errorCode = $e->getCode();
            }

            Log::error(
                '[Points]-[redeemedPoints]: Message Failed' .
                ', Phone-Number: ' . $rpxUser->phone_number .
                ", Business: " . $businessName .
                ", Error Code: " . $errorCode .
                ", Error Message: " . $e->getMessage()
            );

            return $errorCode;
        }
    }
}
