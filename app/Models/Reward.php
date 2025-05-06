<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Jobs\SendRewardRedeemedSms;
use Auth;
use Illuminate\Validation\Rule;
use Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class Reward extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'rewards';

    public function business()
    {
        return $this->belongsTo('App\Models\Business', 'business_id', 'id');
    }

    public function loyaltyTier()
    {
        return $this->belongsTo('App\Models\LoyaltyTier', 'tier_id', 'id');
    }

    public function uploadMedia(Request $request)
    {
        $success = true;
        $message = null;

        $validatedData = $request->validate([
            'image' => 'required|image|max:25000',
        ]);

        $user = Auth::user();

        $hashedFileName = $validatedData['image']->hashName();

        $newFile = Image::make($request->file('image'))->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $newFile = $newFile->encode('jpg', 60);
        $newFile = (string) $newFile;

        $environment = App::environment();

        $imagePath = 'rewards-media/images/' . $user->id . '/' . $hashedFileName;
        Storage::disk('s3')->put($imagePath, $newFile, 'public');
        $imagePath = Storage::disk('s3')->url($imagePath);

        $response = [
            'success' => $success,
            'message' => $environment,
            'image'   => $imagePath,
        ];

        return response($response);
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;

        $validatedData = $request->validate([
            'name'        => 'required|string|max:75|min:1',
            'description' => 'required|string|max:350|min:1',
            'images'      => 'nullable|string|max:500|min:1',
            'type'        => 'required|numeric|max:6',
            'point_cost'  => 'required|numeric|min:1',
            'tier_id'     => [
                'nullable',
                Rule::exists('loyalty_tiers', 'id')->where(function($qry) use ($business){
                    $qry->where('business_id', $business->id);
                }),
            ],
            'is_global' => 'required|boolean'
        ]);

        $businessReward = new Reward();
        $businessReward->uuid = Str::uuid();
        $businessReward->business_id = $business->id;
        $businessReward->name = $validatedData['name'];
        $businessReward->description = $validatedData['description'];
        $businessReward->images = (!is_null($validatedData['images'])) ? $validatedData['images'] : '0';
        $businessReward->type = $validatedData['type'];
        $businessReward->point_cost = round($validatedData['point_cost']);
        $businessReward->tier_id = $validatedData['tier_id'];
        $businessReward->is_global = $validatedData['is_global'];

        DB::transaction(function () use ($businessReward) {
            $businessReward->save();
        }, 3);

        $response = [
            'success'     => true,
            'newBusiness' => $business,
        ];

        return response($response);
    }

    public function claim(Request $request)
    {
        $validatedData = $request->validate([
            'redeemableHash' => 'required|string|max:36',
            'user_id' => 'nullable|numeric',
        ]);


        $sendSmsWithLoginInstructions = false;
        if ($request->exists('user_id')) {
            $user = User::where('id', $validatedData['user_id'])->with('loyaltyPointBalance')->first();
            $sendSmsWithLoginInstructions = true;
        } else {
            $user = Auth::user();
        }

        $success = false;
        $reward = null;

        if ($user)
        {
            $reward = Reward::where('uuid', $validatedData['redeemableHash'])->get()->first();

            if ($reward)
            {
                // Check if the user has enough LP to claim this reward.
                // To do this we check the user balance in the corresponding business.
                $balanceInBusiness = $user->loyaltyPointBalance()
                    ->where('from_business', $reward->business_id)->first();

                // Check if the user has entered this tier.
                if($reward->tier_id){
                    $tier = LoyaltyTier::select('lp_entrance')->where('id', $reward->tier_id)->first();

                    if(is_null($balanceInBusiness) || $tier->lp_entrance > $balanceInBusiness->balance_aggregate) {
                        // Deny the transaction.
                        $response = response([
                            'success' => false,
                            'tier_name' => $tier->name,
                            'message' => "You need ".$tier->lp_entrance." loyalty points to enter this loyalty tier.",
                        ]);
                        return $response;
                    }
                }

                // Please beware: The point_cost column actually refers to the dollar value.
                $lpValue = round(($reward->point_cost / ($reward->business->loyaltyPointBalance->loyalty_point_dollar_percent_value/100)));

                if (is_null($user->loyaltyPointBalanceAggregator)) {
                    $balanceAfterRedeeming = 0 - $lpValue;
                } else {
                    $balanceAfterRedeeming = $user->loyaltyPointBalanceAggregator->balance - $lpValue;
                }

                if (! is_null($balanceInBusiness)) {
                    $balanceInBusinessAfterRedeeming = $balanceInBusiness->balance - $lpValue;
                } else {
                    $balanceInBusinessAfterRedeeming = 0 - $lpValue;
                }
                if ($balanceAfterRedeeming < 0)
                {
                    // Deny the transaction.
                    $response = response([
                        'success' => false,
                        'message' => 'Not enough loyalty points in this account.',
                    ]);
                    return $response;
                }

                // Create the transaction in the ledger table.
                $rewardLedgerRecord = $user->loyaltyPointLedger()->create([
                    'uuid'           => Str::uuid(),
                    'business_id'    => $reward->business_id,
                    'loyalty_amount' => -($lpValue),
                    'user_id'        => $user->id,
                    'type'           => 'reward_expense',
                ]);

                // Create a redeemable item.
                $redeemed = new RedeemableItems();
                $redeemed->uuid = Str::uuid();
                $redeemed->business_id = $reward->business_id;
                $redeemed->redeemer_id = $user->id;
                $redeemed->total_spent = $lpValue;
                $redeemed->redeemed = true;
                $redeemed->reward_id = $reward->id;
                $redeemed->dollar_value = floatval($reward->point_cost);
                $redeemed->ledger_record_id = $rewardLedgerRecord->id;

                // Charge the user the LP Cost.
                if (! is_null($user->loyaltyPointBalanceAggregator)) {
                    $user->loyaltyPointBalanceAggregator->balance = $balanceAfterRedeeming;
                }

                //  Reflect the expense in the users balance in the business.
                if (! is_null($balanceInBusiness)) {
                    $balanceInBusiness->balance = $balanceInBusinessAfterRedeeming;
                }

                $businessName = Business::select('name')->find($reward->business_id)->name;
                $rpxUser = $user->rpxUser;

                DB::transaction(function () use (
                    $user,
                    $redeemed,
                    $balanceInBusiness,
                ) {
                    $redeemed->save();
                    if (! is_null($user->loyaltyPointBalanceAggregator)) {
                        $user->loyaltyPointBalanceAggregator->save();
                    }

                    if (! is_null($balanceInBusiness)) {
                        $balanceInBusiness->save();
                    }
                });

                // Try to send the user a text message saying that they've redeemed the reward.
                $this->sendRewardRedeemedSms($user, $rpxUser, $businessName, $reward->name, $sendSmsWithLoginInstructions);
            }
            $success = true;
        }

        if (! is_null($user->loyaltyPointBalanceAggregator)) {
            $lp_balance = $user->loyaltyPointBalanceAggregator->balance;
        } else {
            $lp_balance = 0;
        }

        $reward->point_cost = $lpValue;
        $response = response([
            'success'        => $success,
            'reward'         => $reward,
            'loyalty_points' => $lp_balance,
        ]);

        return $response;
    }

    private function sendRewardRedeemedSms(User $user, RpxUser $rpxUser, string $businessName, string $rewardName, bool $sendSmsWithLoginInstructions) {
        if (! is_null($rpxUser->phone_number) && $rpxUser->sms_opt_in === 1) {
            $sms = app(SystemSms::class)->createSettingsSms($user, $rpxUser->phone_number);
            SendRewardRedeemedSms::dispatch($user, $sms, $rpxUser, $businessName, $rewardName, $sendSmsWithLoginInstructions)
                ->onQueue(config('rpx.sms.queue'));
        }
    }

    public function updateReward(Request $request)
    {
        $user = Auth::user();
        $business = $user->business;

        $validatedData = $request->validate([
            'id'          => 'required|numeric|min:1',
            'name'        => 'required|string|max:75|min:1',
            'description' => 'required|string|max:350|min:1',
            'images'      => 'nullable|string|max:500|min:1',
            'type'        => 'required|numeric|max:6',
            'point_cost'  => 'required|numeric|min:1',
            'tier_id'     => [
                'nullable',
                Rule::exists('loyalty_tiers', 'id')->where(function($qry) use ($business){
                    $qry->where('business_id', $business->id);
                }),
            ],
            'is_global' => 'nullable|boolean' // Leave this to nullable until we replace the business site
        ]);

        $businessReward = $business->rewards()->find($validatedData['id']);

        $businessReward->business_id = $business->id;
        $businessReward->name = $validatedData['name'];
        $businessReward->description = $validatedData['description'];
        $businessReward->images = (!is_null($validatedData['images'])) ? $validatedData['images'] : '0';
        $businessReward->type = $validatedData['type'];
        $businessReward->point_cost = $validatedData['point_cost'];
        $businessReward->tier_id = $validatedData['tier_id'];
        if ( $request->exists('is_global') ) {
            $businessReward->is_global = $validatedData['is_global'];
        }

        DB::transaction(function () use ($businessReward) {
            $businessReward->save();
        }, 3);

        $response = [
            'success'     => true,
            'newBusiness' => $business,
        ];

        return response($response);
    }

    public function index(Request $request)
    {
        $validatedData = $request->validate([
            'qrCodeLink' => ['nullable', 'string'],
        ]);

        $rewards = null;
        $loyalty_point_dollar_percent_value = null;

        if (isset($validatedData['qrCodeLink'])) {
            $business = Business::select('id', 'name', 'description', 'address', 'qr_code_link', 'loc_x', 'loc_y', 'is_verified', 'categories', 'updated_at')
            ->where('qr_code_link', $validatedData['qrCodeLink'])
            ->get()[0];

            $businessMenu = Reward::select('*')
            ->where('business_id', $business->id)
            ->get();

            $loyalty_point_dollar_percent_value = LoyaltyPointBalance::where('business_id', $business->id)
            ->get()[0]->loyalty_point_dollar_percent_value;

            if (!is_null($businessMenu)) {
                $rewards = $businessMenu;
            }
        } else {
            $user = Auth::user();

            $business = $user->business;

            if ($business) {
                $rewards = $business->rewards()->select('*')->get();
            } else {
                $rewards = [];
            }
        }

        $response = [
            'success'                            => true,
            'rewards'                            => $rewards,
            'business'                           => $business,
            'loyalty_point_dollar_percent_value' => $loyalty_point_dollar_percent_value,
        ];

        return response($response);
    }

    public function deleteMe(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|numeric',
        ]);

        $user = Auth::user();
        $reward_id = $validatedData['id'];

        if ($user)
        {
            DB::transaction(function () use ($user, $reward_id) {
                Reward::where('id', $reward_id)->delete();
            }, 3);
        }

        $response = [
            'success' => true,
        ];

        return response($response);
    }
}
