<?php

namespace App\Jobs;

use App\Models\SystemSms;
use App\Models\User;
use App\Services\Points;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBonusLpSms implements ShouldQueue
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
        private string $totalPoints,
        private string $range1,
        private string $range2,
        private string $range3,
        private string $dayOfWeek,
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

        app(Points::class)->sendBonusLp(
            $rpxUser,
            $this->user,
            $this->systemSms,
            $this->totalPoints,
            $this->businessName,
            $this->range1,
            $this->range2,
            $this->range3,
            $this->dayOfWeek
        );
    }
}
