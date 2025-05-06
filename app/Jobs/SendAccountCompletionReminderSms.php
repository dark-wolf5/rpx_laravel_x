<?php

namespace App\Jobs;

use App\Models\SystemSms;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAccountCompletionReminderSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private User $user,
        private SystemSms $systemSms,
        private string $phoneNumber,
        private string $businessName,
        private string $portalUrl
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

        app(UserService::class)->sendAccountCompletionReminderSms(
            $this->phoneNumber,
            $this->user->id,
            $rpxUser,
            $this->systemSms,
            $this->businessName,
            $this->portalUrl
        );
    }
}
