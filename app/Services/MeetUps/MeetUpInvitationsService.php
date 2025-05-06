<?php

namespace App\Services\MeetUps;

use App\Helpers\Sms\SmsAndCallTwimlHelper;
use App\Models\MeetUp;
use App\Models\MeetUpInvitation;
use App\Models\SystemSms;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class MeetUpInvitationsService
{
    public function sendMeetUpInvitation(
        MeetUp $meetUp,
        string $businessName,
        string $hostName,
        string $hostUserId,
        string $phoneNumber,
        SystemSms $sms,
        string $guestName,
        string $invitationListNameList,
        MeetUpInvitation $meetUpInvitation,
    ) {
        $meetUpName = $meetUp->name;

        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $meetUp->time, 'UTC');

        $dateTime = $dateTime->setTimezone('America/New_York');
        $meetUpDate = $dateTime->format('D, M d, Y h:i A');

        try
        {
            $lang = 'en';
            $sid = config('services.twilio.account_sid');
            $token = config('services.twilio.token');

            $client = new Client($sid, $token);
            $langHelper = new SmsAndCallTwimlHelper($lang);
            $body = $langHelper->getInvitationSmsText(
                $meetUpName,
                $businessName,
                $hostName,
                $meetUpDate,
                $guestName,
                $invitationListNameList,
                $meetUpInvitation
            );

            $client->messages->create(
                $phoneNumber,
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
                '[MeetUpInvitationsService]-[sendMeetUpInvitation]: Message Sent '.
                ', Sms ID: ' . $sms->id .
                ', User ID: ' . $hostUserId .
                ', Phone-Number: ' . $phoneNumber .
                ', Business: ' . $businessName .
                ', Date: ' . $meetUpDate.
                ', MeetUp Name: ' . $meetUpName
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
                "[MeetUpInvitationsService]-[sendMeetUpInvitation]: Message Failed" .
                ", Sms ID: " . $sms->id .
                ", User ID: " . $hostUserId .
                ", Phone-Number: " . $phoneNumber .
                ", Business: " . $businessName .
                ", Date: " . $meetUpDate.
                ", MeetUp Name: " . $meetUpName .
                ", Error Code: " . $errorCode .
                ", Error Message: " . $e->getMessage()
            );

            return $errorCode;
        }

        return 0;
    }
}
