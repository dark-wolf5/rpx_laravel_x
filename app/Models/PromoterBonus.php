<?php

namespace App\Models;

use App\Jobs\SendBonusLpSms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Auth;

class PromoterBonus extends Model
{
    use HasFactory;

    protected $table = 'promoter_bonus';

    protected $fillable = ['business_id', 'lp_amount', 'redeemed', 'user_id', 'promoter_id', 'device_ip', 'device_id', 'expires_at', 'day', 'time_range_1', 'time_range_2', 'time_range_3'];

    public function redeemableItem()
    {
        return $this->hasOneThrough('App\Models\RedeemableItems', 'App\Models\LoyaltyPointLedger', 'id', 'ledger_record_id', 'ledger_record_id', 'id');
    }

    /**
     * Scope a query to only include popular users.
     */
    public function scopeIsNotExpired(Builder $query)
    {
        // Scope function to check if expiration_date has not transpired
        return $query->where('expires_at', '>', Carbon::now());
    }

    public function scopeIsNotRedeemed(Builder $query)
    {
        return $query->where('redeemed', '=', 0);
    }

    /**
     * Let's create something out of this.
     */
    public function scopeWithInTimeRange(Builder $query): void
    {
        $now = Carbon::now();
        $nowHour = intval($now->format('g'));
        $nowDay = intval($now->format('N'));
        $amOrPm = $now->format('A');

        Log::info('Day ' .  $nowDay . ' Hour ' . $nowHour . ' amorpm: ' . $amOrPm);

        $query->where('day', '=', $nowDay)
            ->where('time_range_1', '<=', $nowHour)
            ->where('time_range_2', '>=', $nowHour)
            ->where('time_range_3', '=', $amOrPm);
    }

    public function createForUser(Request $request)
    {
        $validatedData = $request->validate([
            'timeRangeOne' => 'nullable|string',
            'timeRangeTwo' => 'nullable|string',
            'timeRangeThree' => 'nullable|string',
            'day' => 'nullable|string',
            'businessId' => 'nullable|string',
            'userId' => 'nullable|string',
            'deviceId' => 'nullable|string',
        ]);

        $loggedInUser = Auth::user();

        if ($loggedInUser) {
            $deviceAlternatorRecord = PromoterDeviceAlternator::where('device_id', $validatedData['deviceId'])->first();
            $pB = new PromoterBonus();
            $pB->time_range_1 = $validatedData["timeRangeOne"];
            $pB->time_range_2 = $validatedData["timeRangeTwo"];
            $pB->time_range_3 = $validatedData["timeRangeThree"];
            $pB->day = $validatedData["day"];
            $pB->business_id = $validatedData["businessId"];
            $pB->promoter_id = $deviceAlternatorRecord->user_id;
            $pB->lp_amount = $deviceAlternatorRecord->lp_amount;
            $pB->redeemed = false;
            $pB->device_ip = $request->ip();
            $pB->device_id = $deviceAlternatorRecord->device_id;
            $pB->user_id = $validatedData["userId"];
            $pB->expires_at = Carbon::now()->addDays(30);
            $pB->ledger_record_id = '0';
            $pB->save();

            $pB->refresh();

            $user = User::find($pB->user_id);
            $rpxUser = RpxUser::find($pB->user_id);
            $business = Business::find($pB->business_id);
            Log::info('Business Name ' . $business->name . ' Business Id ' . $pB->business_id);
            $this->sendBonusLpSms(
                $user,
                $rpxUser,
                $business->name,
                $deviceAlternatorRecord->lp_amount,
                $pB->time_range_1,
                $pB->time_range_2,
                $pB->time_range_3,
                $pB->day
            );
        }

        return response('ok', 200);
    }

    private function sendBonusLpSms(
        $user = null,
        $rpxUser = null,
        $businessName = null,
        $lpAmount = null,
        $range1 = null,
        $range2 = null,
        $range3 = null,
        $day = null
    ) {
        if (env('APP_ENV') === 'staging') {
            return;
        }

        $sms = app(SystemSms::class)->createBonusLpSms($user, $rpxUser->phone_number);

        SendBonusLpSms::dispatch(
            $user,
            $sms,
            $rpxUser->phone_number,
            $businessName,
            $lpAmount,
            $range1,
            $range2,
            $range3,
            $day
        )
            ->onQueue(config('rpx.sms.queue'));
    }

}
