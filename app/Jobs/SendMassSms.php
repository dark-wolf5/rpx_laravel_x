<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Models\SmsGroup;
use App\Models\User;
use App\Services\CustomerManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendMassSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private User $user,
        private string $businessName,
        private Sms $sms,
        private SmsGroup $smsGroup,
        private string $fromNumber
    ){
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $rpxUser = $this->user->rpxUser()->first();
        $phoneNumber = $rpxUser->phone_number;

        app(CustomerManager::class)->sendSms(
            $phoneNumber,
            $this->user->id,
            $rpxUser->first_name,
            $this->businessName,
            $this->sms,
            $this->smsGroup,
            $this->fromNumber,
        );
    }
}
