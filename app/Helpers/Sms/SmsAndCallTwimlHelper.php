<?php

namespace App\Helpers\Sms;

use App\Models\MeetUpInvitation;
use Carbon\Carbon;

class SmsAndCallTwimlHelper
{
    public function __construct(public string $twimlLocale)
    {
    }

    public function getConfirmationCallSpeech($confirmationCode)
    {
        $confirmationCode = implode('. ', str_split($confirmationCode));

        $greeting = trans('phoneConfirmation.greeting');
        $codeOne = trans('phoneConfirmation.code_1', ['confirmationCode' => $confirmationCode]);
        $codeTwo = trans('phoneConfirmation.code_2', ['confirmationCode' => $confirmationCode]);

        $locale = $this->twimlLocale;

        $text = "<Response><Pause length='3'/>
        <Say language='$locale'>$greeting</Say>
        <Say language='$locale'>$codeOne</Say>
        <Say language='$locale'>$codeTwo</Say>
        </Response>";

        return $text;
    }

    public function getInviteContactSmsText(string $displayName, string $personInviting)
    {
        return trans('promotional_sms.inviteContact', ['displayName' => $displayName, 'personInviting' => $personInviting]);

    }

    public function getConfirmPhoneSmsText($confirmationCode)
    {
        $smsText = trans('phoneConfirmation.smsText', ['confirmationCode' => $confirmationCode]);

        return $smsText;
    }

    public function getPromotionalSmsText(string $firstName, string $businessName, string $body): string
    {
        return trans('promotional_sms.promotional_text', ['firstName' => $firstName, 'businessName' => $businessName, 'body' => $body]);
    }

    public function getSettingsSmsText(string $firstName)
    {
        return trans('promotional_sms.smsOptInText', ['firstName' => $firstName]);
    }

    public function getAccountCompletionReminderText(string $firstName, string $businessName, string $portalUrl)
    {
        return trans('promotional_sms.accountCompletionReminderText', ['firstName' => $firstName, 'businessName' => $businessName, 'portalUrl' => $portalUrl]);
    }

    public function getPasswordResetText(string $firstName, string $token)
    {
        return trans('promotional_sms.passwordResetText', ['firstName' => $firstName, 'token' => $token]);
    }

    public function getAccountCreatedSmsOptInText(string $firstName, string $businessName, string $userEmail)
    {
        return trans('promotional_sms.accountCreatedSmsOptInText', ['firstName' => $firstName, 'businessName' => $businessName, 'userEmail' => $userEmail]);
    }

    public function getRewardRedeemedSmsTxt(string $rewardName, string $businessName, bool $withLoginInstructions, string $userEmail, string $firstName)
    {
        if ($withLoginInstructions === true) {
            return trans('promotional_sms.rewardRedeemedTextWithEmail', ['rewardName' => $rewardName, 'businessName' => $businessName, 'userEmail' => $userEmail, 'firstName' => $firstName]);
        } else {
            return trans('promotional_sms.rewardRedeemedText', ['rewardName' => $rewardName, 'businessName' => $businessName, 'firstName' => $firstName]);
        }
    }

    public function getPointsRedeemedSmsTxt(int $businessPoints, string $businessName, string $userEmail, string $firstName, int $bonusPoints)
    {
        $totalPoints = $businessPoints + $bonusPoints;
        return trans('promotional_sms.pointsRedeemedTextWithEmail', ['businessPoints' => $businessPoints, 'totalPoints' => $totalPoints, 'businessName' => $businessName, 'userEmail' => $userEmail, 'firstName' => $firstName, 'bonusPoints' => $bonusPoints]);
    }

    public function getInvitationSmsText(
        string $meetUpName,
        string $businessName,
        string $hostName,
        string $date,
        string $guestName,
        string $invitationListNameList,
        MeetUpInvitation $meetUpInvitation,
    )
    {
        return trans(
            'promotional_sms.invitationSmsText',
            [
                'businessName' => $businessName,
                'hostName' => $hostName,
                'date' => $date,
                'meetUpName' => $meetUpName,
                'guestName' => $guestName,
                'invitationListNameList' => $invitationListNameList,
                'invitationId' => $meetUpInvitation->uuid,
            ]
        );
    }

    public function getBonusLpSmsTxt(string $totalPoints, string $businessName, string $firstName, string $range1, string $range2, string $range3, int $dayOfWeek)
    {
        $friendlyDayName = $this->getDayNameFromNumber($dayOfWeek);

        return trans(
            'promotional_sms.pointsBonusText',
            [
                'totalPoints' => $totalPoints,
                'businessName' => $businessName,
                'firstName' => $firstName,
                'range1' => $range1,
                'range2' => $range2,
                'range3' => $range3,
                'dayOfWeek' => $friendlyDayName
            ]
        );
    }

    function getDayNameFromNumber($dayNumber)
    {
        // Adjust Carbon's default start of week to ISO format (1 = Monday, 7 = Sunday)
        return Carbon::create()->startOfWeek(Carbon::MONDAY)->addDays($dayNumber - 1)->dayName;
    }
}
