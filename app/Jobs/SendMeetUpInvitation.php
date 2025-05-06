<?php

namespace App\Jobs;

use App\Models\MeetUp;
use App\Models\MeetUpInvitation;
use App\Models\SystemSms;
use App\Models\User;
use App\Services\MeetUps\MeetUpInvitationsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMeetUpInvitation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param User $meetUpOwner
     * @param MeetUp $meetUp
     * @param MeetUpInvitation $meetUpInvitation
     * @param string $invitationListNameList
     * @param string $businessName
     * @param string $guestName
     */
    public function __construct(
        private User $meetUpOwner,
        private MeetUp $meetUp,
        private MeetUpInvitation $meetUpInvitation,
        private string $invitationListNameList,
        private string $businessName,
        private string $guestName,
    ){
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // There are two users that we need info from, that's the user who owns the event "host"
        // and the person invited to the event.
        $rpxUseOwner = $this->meetUpOwner->rpxUser;

        $eventRecipient = User::find($this->meetUpInvitation->friend_id);
        if (! is_null($eventRecipient)) {
            if (is_null($eventRecipient->rpxUser->phone_number)) {
                Log::info("[SendMeetUpInvitation][handle] - User ID: " . $eventRecipient->rpxUser->id . " does not have a phone number.");
                return;
            }

            if ($eventRecipient->rpxUser->sms_opt_in === 0) {
                Log::info("[SendMeetUpInvitation][handle] - User ID: " . $eventRecipient->rpxUser->id . " is opted out of SMS.");
                return;
            }
        }

        $guestPhoneNumber = null;
        $guestUserId = null;
        if (! is_null($eventRecipient)) {
            $guestPhoneNumber = $eventRecipient->rpxUser->phone_number;
            $guestUserId =  $eventRecipient->rpxUser->id;
        } else {
            $guestPhoneNumber = $this->meetUpInvitation->friend_id;
            $guestUserId = 'mui-' . $this->meetUpInvitation->id;
        }

        $hostname = null;
        if (! is_null($rpxUseOwner->first_name)) {
            $hostname = $rpxUseOwner->first_name . " " . $rpxUseOwner->last_name;
        } else {
            $hostname = $this->meetUpOwner->username;
        }

        $sms = app(SystemSms::class)->createInviteMeetUpSms($eventRecipient, $guestPhoneNumber);

        app(MeetUpInvitationsService::class)->sendMeetUpInvitation(
            $this->meetUp,
            $this->businessName,
            $hostname,
            $guestUserId,
            $guestPhoneNumber,
            $sms,
            $this->guestName,
            $this->invitationListNameList,
            $this->meetUpInvitation
        );
    }
}
