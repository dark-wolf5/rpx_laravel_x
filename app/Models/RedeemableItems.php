<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Jobs\SendPointsRedeemedSms;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * @property mixed       $business_id
 * @property false|mixed $redeemed
 * @property mixed       $loyalty_point_dollar_percent_value
 * @property mixed       $dollar_value
 * @property mixed       $total_spent
 * @property mixed redeemer_id
 * @property mixed reward_id
 * @property mixed ledger_record_id
 * @property mixed                            $amount
 * @property mixed|\Ramsey\Uuid\UuidInterface $uuid
 */
class RedeemableItems extends Model
{
    use HasFactory;

    public function loyaltyPointLedger()
    {
        return $this->hasOne('App\Models\LoyaltyPointLedger', 'id', 'ledger_record_id');
    }

    public function reward()
    {
        return $this->hasOne('App\Models\Reward', 'id', 'reward_id');
    }

    public function receiptData()
    {
        return $this->hasOne('App\Models\ReceiptData', 'redeemable_id', 'id');
    }

    public function feedback()
    {
        return $this->hasOne('App\Models\Feedback', 'ledger_record_id', 'ledger_record_id');
    }

    public function business()
    {
        return $this->hasOne('App\Models\Business', 'id', 'business_id');
    }

    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'amount'       => ['required', 'numeric'],
            'total_spent'  => ['required', 'numeric'],
            'dollar_value' => ['required', 'numeric'],
        ]);

        $user = Auth::user();

        if ($user)
        {
            $redeemable = new RedeemableItems();
            $redeemable->business_id = $user->business->id;
            $redeemable->uuid = Str::uuid();
            $redeemable->amount = $validatedData['amount'];
            $redeemable->total_spent = $validatedData['amount'];
            $redeemable->dollar_value = $validatedData['dollar_value'];
            $redeemable->loyalty_point_dollar_percent_value = $user->business->loyaltyPointBalance->loyalty_point_dollar_percent_value;
            $redeemable->redeemed = false;

            DB::transaction(function () use ($redeemable) {
                $redeemable->save();
            }, 3);

            $redeemable->refresh();
        }
        else
        {
            $redeemable = null;
        }

        $response = [
            'success'    => true,
            'redeemable' => $redeemable,
        ];

        return response($response);
    }

    public function scanReceipt(Request $request)
    {
        $success = true;
        $message = null;

        $validatedData = $request->validate([
            'file' => 'required|image|max:25000',
        ]);

        $user = Auth::user();

        $hashedFileName = $validatedData['file']->hashName();
        // $receiptData = $validatedData['receiptData'];
        $environment = App::environment();

        $imagePath = 'receipts/images/' . $user->id . '/';
        Storage::disk('s3')->put($imagePath, $request->file('file'), 'public');
        $imagePath = Storage::disk('s3')->url($imagePath . $hashedFileName);

        $redeemable = new RedeemableItems();
        $redeemable->business_id = 0;
        $redeemable->uuid = Str::uuid();
        $redeemable->amount = 25;
        $redeemable->total_spent = 0;
        $redeemable->dollar_value = 0;
        $redeemable->loyalty_point_dollar_percent_value = 1;
        $redeemable->redeemed = true;

        $insertLp = new LoyaltyPointLedger();
        $insertLp->user_id = $user->id;
        $insertLp->uuid = Str::uuid();
        $insertLp->business_id = 0;
        $insertLp->loyalty_amount = 25;
        $insertLp->type = 'points';

        $user->loyaltyPointBalanceAggregator->balance += $insertLp->loyalty_amount;
        $user->loyaltyPointBalanceAggregator->save();
        $user->loyaltyPointBalanceAggregator->refresh();

        DB::transaction(function () use ($redeemable, $insertLp, $user, $imagePath) {
            $redeemable->save();
            $insertLp->save();

            $insertLp->refresh();
            $redeemable->refresh();

            $redeemable->ledger_record_id = $insertLp->id;
            $redeemable->save();

            ReceiptData::create([
                'user_id' => $user->id,
                'image_path' => $imagePath,
                'redeemable_id' => $redeemable->id,
                'status' => 0,
                'data' => null,
            ]);
        }, 3);

        $response = [
            'success' => $success,
            'message' => $environment,
            'loyalty_points' => $user->loyaltyPointBalanceAggregator->balance,
            'award_points' => 10
        ];

        return response($response);
    }


    public function redeem(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'nullable|numeric',
            'dollars_spent' => 'required_with:user_id',
            'redeemableHash' => ['required_without:user_id', 'string'],
        ]);

        $sendSmsWithLoginInstructions = false;
        /**
         * We are creating the redeemable items and skipping the business creation part of it
         * What this means is that the user didn't have to scan the reward.
         */
        if ($request->exists('user_id')) {
            $user = User::where('id', $validatedData['user_id'])->first();
            $loggedInUser = Auth::user();
            $business = $loggedInUser->business;
            $sendSmsWithLoginInstructions = true;

            $redeemable = new RedeemableItems();
            $redeemable->business_id = $business->id;
            $redeemable->uuid = Str::uuid();
            $redeemable->amount = $validatedData['dollars_spent'];
            $redeemable->total_spent = $validatedData['dollars_spent'];
            $redeemable->dollar_value = $validatedData['dollars_spent'];
            $redeemable->loyalty_point_dollar_percent_value = $business->loyaltyPointBalance->loyalty_point_dollar_percent_value;
            $redeemable->redeemed = false;
        } else {
            $user = Auth::user();
            $redeemable = RedeemableItems::where('uuid', $validatedData['redeemableHash'])
                ->first();
        }

        if ($redeemable)
        {
            if ($redeemable->redeemed === 1)
            {
                $response = [
                    'success' => false,
                    'message' => 'Points already redeemed.',
                ];
                return response($response);
            }

            $redeemable->redeemed = 1;
            $redeemable->redeemer_id = $user->id;

            // Add to ledger and to LP Balance
            // Insert reward into ledger
            $insertLp = new LoyaltyPointLedger();
            $insertLp->user_id = $user->id;
            $insertLp->uuid = Str::uuid();
            $insertLp->business_id = $redeemable->business->id;
            $insertLp->loyalty_amount = abs(floatval($redeemable->amount));
            $insertLp->type = 'points';

            $reward = $insertLp->loyalty_amount;

            // Reflect reward into personal user business balance.
            $userCurrentBalance = $user->loyaltyPointBalance()->where('from_business', $redeemable->business->id)->first();
            if (is_null($userCurrentBalance))
            {
                $lp = new LoyaltyPointBalance();
                $lp->user_id = $user->id;
                $lp->balance = 0;
                $lp->from_business = $redeemable->business_id;
                $lp->business_id = 0;
                DB::transaction(function () use ($lp) {
                    $lp->save();
                }, 3);

                // Now that we have inserted a balance, let's rewrite this variable.
                $userCurrentBalance = $user->loyaltyPointBalance()->where('from_business', $redeemable->business->id)->first();
            }

            // Check if there are any VALID LP Promoter Bonus records available for this user and business.
            $qry = PromoterBonus::where('business_id', $redeemable->business->id)
                    ->where('user_id', $user->id)
                    ->isNotRedeemed()
                    ->isNotExpired();
            $lpPromoterBonusList = $qry->get();

            $totalBonusPoints = 0;
            $totalBonusDollars = 0;
            if (count($lpPromoterBonusList) > 0) {
                DB::transaction(function () use ($lpPromoterBonusList, $redeemable) {
                    $lpPromoterBonusList->each(function ($lpPromoterBonus) use ($redeemable){
                        // Add to ledger and to LP Balance
                        // Insert reward into ledger
                        $insertBonusLp = new LoyaltyPointLedger();
                        $insertBonusLp->user_id = $lpPromoterBonus->user_id;
                        $insertBonusLp->uuid = Str::uuid();
                        $insertBonusLp->business_id = $lpPromoterBonus->business_id;
                        $insertBonusLp->loyalty_amount = abs(floatval($lpPromoterBonus->lp_amount));
                        $insertBonusLp->type = 'points';
                        $insertBonusLp->save();
                        $insertBonusLp->refresh();

                        $lpPromoterBonus->ledger_record_id = $insertBonusLp->id;

                        $bonusRedeemable = new RedeemableItems();
                        $bonusRedeemable->business_id = $redeemable->business->id;
                        $bonusRedeemable->uuid = Str::uuid();
                        $bonusRedeemable->amount = abs(floatval($lpPromoterBonus->lp_amount));
                        $bonusRedeemable->total_spent = 0;
                        $bonusRedeemable->dollar_value = abs(floatval($lpPromoterBonus->lp_amount));
                        $bonusRedeemable->loyalty_point_dollar_percent_value = $redeemable->business->loyaltyPointBalance->loyalty_point_dollar_percent_value;
                        $bonusRedeemable->redeemed = true;
                        $bonusRedeemable->ledger_record_id = $insertBonusLp->id;
                        $bonusRedeemable->redeemer_id = $lpPromoterBonus->user_id;
                        $bonusRedeemable->save();

                        $lpPromoterBonus->redeemed = 1;
                        $lpPromoterBonus->save();
                    });
                });

                $totalBonusDollars = $lpPromoterBonusList->sum(function ($lpPromoterBonus) {
                    return $lpPromoterBonus->redeemableItem->dollar_value;
                });

                Log::info('$totalBonusDollars: ' . $totalBonusDollars);
                $totalBonusPoints = $lpPromoterBonusList->sum('lp_amount');
            }

            // Reflect the reward into the user's balance in the business.
            $newUserBalanceInBusiness = $userCurrentBalance->balance + $reward + $totalBonusPoints;
            $newUserBalanceAggregateInBusiness = $userCurrentBalance->balance_aggregate + $reward + $totalBonusPoints;

            DB::transaction(function () use (
                $insertLp,
                $user,
                $redeemable,
                $newUserBalanceInBusiness,
                $newUserBalanceAggregateInBusiness,
                $totalBonusPoints
            ) {
                $insertLp->save();
                $insertLp->refresh();

                $redeemable->ledger_record_id = $insertLp->id;
                $redeemable->save();

                if (is_null($user->loyaltyPointBalanceAggregator)) {
                    $lpAgg = new LoyaltyPointBalanceAggregator();
                    $lpAgg->id = $user->id;
                    $lpAgg->balance = $insertLp->loyalty_amount + $totalBonusPoints;
                    $lpAgg->save();
                } else {
                    $user->loyaltyPointBalanceAggregator->balance += ($insertLp->loyalty_amount + $totalBonusPoints);
                    $user->loyaltyPointBalanceAggregator->save();
                }

                $user->loyaltyPointBalance()->where('from_business', $redeemable->business_id)->update([
                    'balance' => $newUserBalanceInBusiness,
                    'balance_aggregate' => $newUserBalanceAggregateInBusiness
                ]);
            }, 3);

            $redeemable->refresh();

            $rpxUser = $user->rpxUser;
            $businessName = Business::select('name')->find($redeemable->business->id)->name;

            $this->sendPointsRedeemedSms($user, $rpxUser, $businessName, $insertLp->loyalty_amount, $sendSmsWithLoginInstructions, $totalBonusPoints);

            if(is_null($user->loyaltyPointBalanceAggregator)) {
                $agg = $insertLp->loyalty_amount;
            } else {
                $user->loyaltyPointBalanceAggregator->refresh();
                $agg = $user->loyaltyPointBalanceAggregator->balance;
            }

            // Let's temporarirly attach the TOTAL POINTS to the redeemable for the purpose of displaying the correct
            // lp amount (with the bonus added). In the future this will be undone because the front-end will have the
            // capability of distinguishing between a bonus lp and redeemable lp from a non-bonus transaction.
            $redeemable->amount += floatval($totalBonusPoints);
            $redeemable->dollar_value += floatval($totalBonusDollars);

            $response = [
                'success'        => true,
                'redeemable'     => $redeemable,
                'loyalty_points' => $agg,
            ];

            return response($response);
        }
    }

    /**
     * @param User $user
     * @param RpxUser $rpxUser
     * @param string $businessName
     * @param string $businessPoints How many points were redeemed in the current transaction.
     * @param bool $sendSmsWithLoginInstructions
     * @param string $bonusPoints
     * @return void
     */
    private function sendPointsRedeemedSms(
        User $user,
        RpxUser $rpxUser,
        string $businessName,
        string $businessPoints,
        bool $sendSmsWithLoginInstructions,
        string $bonusPoints = '0'
    ) {
        if (! is_null($rpxUser->phone_number) && $rpxUser->sms_opt_in === 1) {
            $sms = app(SystemSms::class)->createSettingsSms($user, $rpxUser->phone_number);

            SendPointsRedeemedSms::dispatch($user, $sms, $rpxUser, $businessName, $businessPoints, $sendSmsWithLoginInstructions, $bonusPoints)
                ->onQueue(config('rpx.sms.queue'));
        }
    }


    public function lpRedeemed(Request $request)
    {
        $user = Auth::user();
        /*
         * Personal account.
         */
        if ($user->rpxUser->user_type === 4)
        {
            $redeemedList = $user
                ->redeemed()
                ->with('loyaltyPointLedger')
                ->with('feedback')
                ->with('business', function ($query) {
                    $query->with('rpxUser');
                })
                ->where('reward_id', '=', null)
                ->orderBy('redeemable_items.created_at', 'desc')
                ->paginate(10);
        }

        $response = [
            'success'      => true,
            'redeemedList' => $redeemedList,
        ];

        return response($response);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->rpxUser->user_type === 4)
        {
            $rewardList = $user
                ->redeemed()
                ->with('loyaltyPointLedger')
                ->with('business', function ($query) {
                    $query->with('rpxUser');
                })
                ->with('reward')
                ->where('reward_id', '!=', null)
                ->orderBy('redeemable_items.created_at', 'desc')
                ->paginate(10);
        }
        else
        {
            $rewardList = DB::table('redeemable_items')
                ->join('business', 'redeemable_items.business_id', '=', 'business.id')
                ->join('users', 'redeemable_items.business_id', '=', 'users.id')
                ->join('rewards', 'redeemable_items.reward_id', '=', 'rewards.id')
                ->select(
                    'redeemable_items.uuid',
                    'redeemable_items.redeemer_id',
                    'redeemable_items.amount',
                    'redeemable_items.total_spent',
                    'redeemable_items.dollar_value',
                    'redeemable_items.loyalty_point_dollar_percent_value',
                    'redeemable_items.redeemed',
                    'redeemable_items.updated_at',
                    'users.username',
                    'rpx_users.default_picture',
                    'rpx_users.user_type',
                    'rewards.name AS reward_name',
                    'rewards.images AS reward_image',
                    'rewards.point_cost AS point_cost'
                )
                ->where('redeemable_items.business_id', $user->business->id)
                ->orderBy('redeemable_items.id', 'desc')
                ->paginate(5);
        }

        $response = [
            'success'    => true,
            'rewardList' => $rewardList,
        ];

        return response($response);
    }
}
