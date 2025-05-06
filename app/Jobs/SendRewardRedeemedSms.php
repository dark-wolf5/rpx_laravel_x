<?php

namespace App\Jobs;

use App\Models\RpxUser;
use App\Models\SystemSms;
use App\Models\User;
use App\Services\Rewards;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRewardRedeemedSms implements ShouldQueue
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
        private RpxUser $rpxUser,
        private string $businessName,
        private string $rewardName,
        private bool $withLoginInstructions = false
    ){
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(Rewards::class)->redeemedSms(
            $this->rpxUser,
            $this->user,
            $this->systemSms,
            $this->rewardName,
            $this->businessName,
            $this->withLoginInstructions
        );
    }
}
