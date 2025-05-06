<?php

namespace App\Services;

use App\Helpers\Sms\SmsAndCallTwimlHelper;
use App\Models\Sms;
use App\Models\SmsGroup;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class CustomerManager
{
    public function sendSms(
        string $userPhoneNumber,
        string $userID,
        string $firstName,
        string $businessName,
        Sms $sms,
        SmsGroup $smsGroup,
        string $fromNumber,
    ) {
        try
        {
            $lang = 'en';
            $sid = config('services.twilio.account_sid');
            $token = config('services.twilio.token');

            $client = new Client($sid, $token);
            $langHelper = new SmsAndCallTwimlHelper($lang);
            $body = $langHelper->getPromotionalSmsText($firstName, $businessName, $smsGroup->body);

            $client->messages->create(
                $userPhoneNumber,
                [
                    'from' => $fromNumber,
                    'body' => $body,
                ]
            );

            // Update SMS message in DB;
            $sms->update([
                'sent' => true,
            ]);

            $smsGroup->update([
                'total_sent' => $smsGroup->total_sent + 1,
                'price' => $smsGroup->price + 0.0079
            ]);

            Log::info(
                '[CustomerManager]-[sendSms]: Message Sent' .
                ', User ID: '. $userID .
                ', Phone-Number: ' . $userPhoneNumber .
                ', Business: ' . $businessName
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
                '[CustomerManager]-[sendSms]: Message Failed' .
                ', Phone-Number: ' . $userPhoneNumber .
                ", Business: " . $businessName .
                ", Error Code: " . $errorCode .
                ", Error Message: " . $e->getMessage()
            );

            return $errorCode;
        }
    }
}
