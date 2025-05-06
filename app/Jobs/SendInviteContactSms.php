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

class SendInviteContactSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private string $displayName,
        private SystemSms $systemSms,
        private string $phoneNumber,
        private User $user,
    ){
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(UserService::class)->sendInviteContactSms(
            $this->phoneNumber,
            $this->displayName,
            $this->systemSms,
            $this->user->rpxUser->first_name
        );
    }
}
