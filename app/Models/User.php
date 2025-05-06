<?php

namespace App\Models;

use App\Jobs\SendBonusLpSms;
use App\Jobs\SendResetPasswordSms;
use App\Jobs\SendSystemSms;
use App\Jobs\SendAccountCreatedThroughBusinessSms;
use Auth;
use Illuminate\Support\Facades\Log;
use Mail;
use App\Mail\User\AccountCreated;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as IlluminatePassword;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Rules\FirstName;
use App\Rules\LastName;
use App\Rules\Password;
use App\Rules\Username;
use Carbon\Carbon;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Cashier;

/**
 * @property mixed $business
 * @property mixed $trial_ends_at
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasFactory, SoftDeletes, Billable;

    protected $casts = [
        'trial_ends_at' => 'date',
    ];

    protected $fillable = ['trial_ends_at'];

    protected $hidden = ['password', 'stripe_id', 'pm_last_four', 'pm_type', 'created_at', 'delete_at', 'end_of_month', 'remember_token'];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshWToken()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::factory()->getTTL() * 60,
        ]);
    }

    public function userLocation()
    {
        return $this->hasOne('App\Models\UserLocation', 'id');
    }

    public function business()
    {
        return $this->hasOne('App\Models\Business', 'id');
    }

    public function rpxUser()
    {
        return $this->hasOne('App\Models\RpxUser', 'id');
    }

    public function defaultImages()
    {
        return $this->hasMany('App\Models\DefaultImages', 'id');
    }

    public function myFavorites()
    {
        return $this->hasMany('App\Models\MyFavorites', 'user_id');
    }

    public function loyaltyPointBalance()
    {
        return $this->hasMany('App\Models\LoyaltyPointBalance', 'user_id');
    }

    public function loyaltyPointBalanceAggregator()
    {
        return $this->hasOne('App\Models\LoyaltyPointBalanceAggregator', 'id');
    }

    public function loyaltyPointLedger()
    {
        return $this->hasMany('App\Models\LoyaltyPointLedger', 'user_id');
    }

    public function redeemed()
    {
        return $this->hasMany('App\Models\RedeemableItems', 'redeemer_id');
    }

    public function signUp(Request $request)
    {
        $validatedData = $request->validate([
            'username' => ['required', 'unique:users', 'max:35', 'min:1', new Username],
            'email'    => ['required', 'unique:users', 'email'],
            'password' => ['required', new Password],
            'route'    => ['required', 'string'],
        ]);

        if ($validatedData['route'] == '/business')
        {
            $accountType = 0;
        }
        else
        {
            $accountType = 4;
        }

        $user = new User();
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->uuid = Str::uuid();

        $newRpxUser = new RpxUser();
        $newRpxUser->first_name = '';
        $newRpxUser->last_name = '';
        $newRpxUser->user_type = $accountType;

        $description = 'Welcome to my Rpx profile.';

        $newRpxUser->description = $description;
        $newRpxUser->last_known_ip_address = $request->ip;

        DB::transaction(function () use ($user, $newRpxUser) {
            $user->createAsStripeCustomer();
            $user->save();

            $newRpxUser->id = $user->id;
            $newRpxUser->save();

            // Only make the aggregated Balance if the user is not a business
            if ($newRpxUser->user_type != '0')
            {
                $lpAggregator = new LoyaltyPointBalanceAggregator();
                $lpAggregator->id = $user->id;
                $lpAggregator->balance = 0;
                $lpAggregator->save();
            }
        }, 3);

        $newRpxUser = $user->rpxUser()->select('default_picture', 'user_type')->first();

        // Start the session
        Auth::login($user);

        if (config('env') === 'production') {
            $this->sendConfirmationEmail();
        }

        $user = Auth::user();
        $user = $user
        ->select('id', 'username', 'email')
        ->where('id', $user->id)
        ->first();

        $signUpResponse = [
            'token_info'   => $this->respondWithToken(Auth::refresh()),
            'message'      => 'success',
            'user'         => $user,
            'rpx_user' => $newRpxUser,
        ];

        return response($signUpResponse);
    }

    public function logIn(Request $request)
    {
        $validatedData = $request->validate([
            'login'           => ['required', 'string'],
            'password'        => ['required', new Password],
            'timezone'        => ['required', 'string'],
            'remember_me_opt' => ['required', 'string'],
            'route'           => ['required', 'string'],
        ]);

        $login = $validatedData['login'];
        $password = $validatedData['password'];
        $remember_me = $validatedData['remember_me_opt'];
        $route = $validatedData['route'];

        if ($route == '/business')
        {
            //Set account to not set and let the user pick their business account type later on.
            $accountType = 0;
        }
        else
        {
            //Set the account type to personal.
            $accountType = 4;
        }

        $searchUser = User::onlyTrashed()
            ->join('rpx_users', 'rpx_users.id', '=', 'users.id')
            ->where(function ($query) use ($login) {
                $query->where('users.username', $login)
                    ->orWhere('users.email', $login)
                    ->orWhere('rpx_users.phone_number', '+1'.$login);
            })->first();

        if ($searchUser !== null && Hash::check($password, $searchUser->password))
        {
            $searchUser->restore();
        }

        if (!Auth::attempt(['email' => $login, 'password' => $password]) &&
            !Auth::attempt(['username' => $login, 'password' => $password])
        ) {
            $userWPh = RpxUser::where('phone_number', '+1'.$login)->first();
            if (! is_null($userWPh)) {
                $user = User::find($userWPh->id);
                if (! Hash::check($password, $user->password)) {
                    $login_failed = true;
                } else {
                    $login_failed = false;
                }
            } else {
                $login_failed = true;
            }
        }
        else
        {
            $login_failed = false;
        }

        if ($login_failed)
        {
            $loginResponse = [
                'message' => 'invalid_cred',
                'user' => $searchUser
            ];
        }
        else
        {
            $user = User::select('users.id', 'users.username', 'users.stripe_id')
                ->join('rpx_users', 'rpx_users.id', '=', 'users.id')
                ->where(function ($query) use ($login) {
                    $query->where('users.username', $login)
                        ->orWhere('users.email', $login)
                        ->orWhere('rpx_users.phone_number', '+1'.$login);
                })->first();

            $accountTypeCheck = $this->checkAccountType($accountType, $user);

            if ($accountTypeCheck !== true)
            {
                return $accountTypeCheck;
            }

            if ($user->stripe_id == null)
            {
                $user->createAsStripeCustomer();
            }

            $rpxUser = $user->rpxUser()->select('default_picture', 'user_type')->first();

            // Start the session
            Auth::login($user, $remember_me);
            $token = Auth::refresh();

            if ($remember_me == '1')
            {
                Auth::user()->remember_token = $token;
            }
            else
            {
                Auth::user()->remember_token = null;
            }

            Auth::user()->save();

            $user = Auth::user();

            $loginResponse = [
                'token_info'   => $this->respondWithToken($token),
                'message'      => 'success',
                'user'         => $user,
                'rpx_user' => $rpxUser,
            ];
        }
        return response($loginResponse);
    }

    public function checkAccountType(int $accountType, User $user)
    {
        if ($accountType === 0 &&
            ($user->rpxUser->user_type == 1 ||
             $user->rpxUser->user_type == 2 ||
             $user->rpxUser->user_type == 3)
        ) {
            return true;
        }
        elseif ($user->rpxUser->user_type !== $accountType)
        {
            return response([
                'message'      => 'wrong_account_type',
                'account_type' => $accountType,
                'sb_acc_type'  => $user->rpxUser->user_type,
            ]);
        }
        else
        {
            return true;
        }
    }

    public function logOut(Request $request)
    {
        Auth::logout();

        $logoutResponse = [
            'success' => true,
        ];

        return response($logoutResponse);
    }

    public function closeBrowser(Request $request)
    {
        if (Auth::user()->remember_me !== null)
        {
            Auth::logout();
        }

        $logoutResponse = [
            'success' => true,
        ];

        return response($logoutResponse);
    }

    public function checkIfLoggedIn()
    {
        if (Auth::check())
        {
            $msg = '1';
            $user = Auth::user();

            $userId = $user->id;

            if ($user->stripe_id !== null)
            {
                $userBillable = Cashier::findBillable($user->stripe_id);
                $businessMembership = $userBillable->subscribed($user->id);
            }
            else
            {
                $businessMembership = null;
            }
        }
        else
        {
            $msg = 'not_logged_in';
            $businessMembership = null;
            $userId = null;
        }

        $response = [
            'message'            => $msg,
            'user_id'            => $userId,
            'businessMembership' => $businessMembership,
        ];

        return response($response);
    }

    private function sendConfirmationEmail(User $user = null, RpxUser $rpxUser = null, bool $withLink = false)
    {
        if (env('APP_ENV') === 'staging') {
            return;
        }

        if (is_null($user)) {
            $user = Auth::user();
            $rpxUser = $user->rpxUser()->first();
        }

        $credentials = array(
            "email" => $user->email
        );

        if ($withLink) {
           IlluminatePassword::sendResetLink($credentials);
        }

        Mail::to($user->email, $user->username)
            ->send(new AccountCreated($user, $rpxUser, $withLink));
    }

    private function sendConfirmationSms($user = null, $rpxUser = null, $businessName = null) {
        if (env('APP_ENV') === 'staging') {
            return;
        }

        $sms = app(SystemSms::class)->createSettingsSms($user, $rpxUser->phone_number);

        SendAccountCreatedThroughBusinessSms::dispatch($user, $sms, $rpxUser->phone_number, $businessName)
            ->onQueue(config('rpx.sms.queue'));
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

    public function getSettings()
    {
        $user = Auth::user();

        $userSettings = [
            'hash'     => $user->uuid,
            'username' => $user->username,
            'email'    => $user->email,
        ];

        $rpxUserSettings = $user
            ->rpxUser()
            ->select('user_type', 'first_name', 'last_name', 'phone_number', 'sms_opt_in')
            ->get()[0];

        $business = $user
            ->business()
            ->select(
                'id',
                'name',
                'description',
                'address',
                'city',
                'country',
                'line1',
                'line2',
                'postal_code',
                'state',
                'categories',
                'photo',
                'is_verified',
                'qr_code_link',
                'loc_x',
                'loc_y',
                'created_at',
                'updated_at',
                'is_food_truck'
            )->get();

        $nextPayment = null;
        $endsAt = null;
        $trialEndsAt = null;

        if (count($business) > 0)
        {
            $business = $business[0];

            $userBillable = Cashier::findBillable($user->stripe_id);
            if (count($userBillable->subscriptions) > 0) {
                $userSubscriptionPlan = $userBillable->subscriptions[0]->stripe_price;
            } else {
                $userSubscriptionPlan = null;
            }

            switch($userSubscriptionPlan)
            {
                case config('rpx.business_subscription_price_1_2'):
                    $userSubscriptionPlan = 'rpx.business_subscription_price_1_2';
                    break;
                case config('rpx.business_subscription_price_2_2'):
                    $userSubscriptionPlan = 'rpx.business_subscription_price_2_2';
                    break;
                case config('rpx.business_subscription_price1'):
                    $userSubscriptionPlan = 'rpx.business_subscription_price1';
                    break;
                case config('rpx.business_subscription_price'):
                    $userSubscriptionPlan = 'rpx.business_subscription_price';
                    break;
                default:
                    $userSubscriptionPlan = null;
            }

            $isSubscribed = $userBillable->subscribed($user->id);

            if ($isSubscribed)
            {
                try {
                    $nextPayment = Carbon::createFromTimestamp($user->subscription($user->id)->asStripeSubscription()->current_period_end);
                    if($user->subscription($user->id)->asStripeSubscription()->cancel_at) {
                        $endsAt = Carbon::createFromTimestamp($user->subscription($user->id)->asStripeSubscription()->cancel_at);
                        $trialEndsAt =  Carbon::createFromTimestamp($user->trial_ends_at);
                    }
                } catch (Exception $e) {
                    $user->subscription($user->id)->cancel();

                    $nextPayment = null;
                    $endsAt = null;
                    $trialEndsAt = null;
                    $isSubscribed = false;
                    $userSubscriptionPlan = null;
                }
            }

            $loyaltyPointBalance = $user->business->loyaltyPointBalance()->first();
        }
        else
        {
            $business = null;
            $isSubscribed = false;
            $userSubscriptionPlan = null;
            $loyaltyPointBalance = null;
        }

        $settingsResponse = [
            'success'               => true,
            'user'                  => $userSettings,
            'rpx_user'          => $rpxUserSettings,
            'business'              => $business,
            'is_subscribed'         => $isSubscribed,
            'userSubscriptionPlan'  => $userSubscriptionPlan,
            'loyalty_point_balance' => $loyaltyPointBalance,
            'next_payment'          => $nextPayment,
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
        ];

        return response($settingsResponse);
    }

    public function saveSettings(Request $request)
    {
        $user = Auth::user();

        if ($user->username === $request->username)
        {
            $usernameValidators = 'required|string|max:35|min:1';
        }
        else
        {
            $usernameValidators = 'required|string|max:35|min:1';
        }

        if ($user->email === $request->email)
        {
            $emailValidators = 'required|email';
        }
        else
        {
            $emailValidators = 'required|email';
        }

        $validatedData = $request->validate([
            'username'     => $usernameValidators,
            'email'        => $emailValidators,
            'first_name'   => ['required', new FirstName],
            'last_name'    => ['required', new LastName],
            'account_type' => 'required|numeric',
            'phone_number' => 'sometimes|string|max:35|nullable',
            'sms_opt_in' => 'required|boolean',
        ]);

        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->rpxUser->first_name = $validatedData['first_name'];
        $user->rpxUser->last_name = $validatedData['last_name'];
        $user->rpxUser->user_type = $validatedData['account_type'];
        $user->rpxUser->sms_opt_in = $validatedData['sms_opt_in'];

        if (array_key_exists('phone_number', $validatedData)) {
            $s = RpxUser::where('phone_number', '+1'.$validatedData['phone_number'])
                ->orWhere('phone_number', $validatedData['phone_number'])
                ->where('id', '!=', $user->id)
                ->count();

            if ($s > 0) {
                return response([
                    'error' => 'The phone number is already in use.'
                ], 422);
            }
        }

        DB::transaction(function () use ($user, $validatedData) {
            if (! array_key_exists('phone_number', $validatedData)) {
                $user->rpxUser->phone_number = null;
            }

            $user->save();
            $user->rpxUser->save();
            $user->refresh();

            if (array_key_exists('phone_number', $validatedData) && $user->rpxUser->sms_opt_in === 0) {
                $user->rpxUser->sms_opt_in = 1;
                $sms = app(SystemSms::class)->createSettingsSms($user, $validatedData['phone_number']);
                SendSystemSms::dispatch($user, $sms, $validatedData['phone_number'])
                    ->onQueue(config('rpx.sms.queue'));
            } else {
                // User already opted-in, no need to send opt-in confirmation message.
                if(array_key_exists('phone_number', $validatedData) && $validatedData['phone_number'] !== '+1'){
                    $user->rpxUser->phone_number = $validatedData['phone_number'];
                } else {
                    $user->rpxUser->phone_number = null;
                }
            }

            $user->rpxUser->save();
        }, 3);

        $response = [
            'success' => true,
            'user'    => $user,
        ];

        return response($response);
    }

    public function confirmAccount(Request $request)
    {
    }

    public function savePassword(Request $request)
    {
        $validatedData = $request->validate([
            'password' => ['required', new Password, 'confirmed'],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($validatedData['password']);
        $user->save();

        $response = [
            'message' => 'success',
        ];

        return $response;
    }

    public function getUser(Request $request)
    {
        $business = Auth::user();

        $validatedData = $request->validate([
            'phone_number' => 'string|max:35|required',
        ]);

        $rpxUser = RpxUser::
            select('id', 'first_name', 'last_name', 'phone_number')
            ->where('phone_number', $validatedData['phone_number'])
            ->first();

        if (! is_null($rpxUser)) {
            $user = $this->find($rpxUser->id)->only('email', 'username');

            $lpBalanceInBusiness = LoyaltyPointBalance::select('balance', 'balance_aggregate')
                ->where('from_business', $business->id)
                ->where('user_id', $rpxUser->id)
                ->first();

            $lpBalance = LoyaltyPointBalanceAggregator::where('id', $rpxUser->id)->first();
            $message = "success";
        } else {
            $user = null;
            $lpBalanceInBusiness = null;
            $lpBalance = null;
            $message = "User not found.";
        }

        $response = [
            'message' => $message,
            'user' => $user,
            'rpx_user' => $rpxUser,
            'lp_balance' => $lpBalance,
            'lp_in_business' => $lpBalanceInBusiness,
        ];

        return response($response);
    }

    public function privateProfile()
    {
        $rpxUser = $this->rpxUser()->select('first_name', 'last_name', 'description', 'default_picture')->first();
        $defaultImages = $this->defaultImages()->select('default_image_url')->get();
        $business = $this->business;

        $response = [
            'user'           => $this->only('id', 'username', 'email'),
            'rpx_user'   => $rpxUser,
            'default_images' => $defaultImages,
            'business'       => $business,
        ];

        return response($response);
    }

    public function setPassResetPin(Request $request)
    {
        $success = false;

        $validatedData = $request->validate([
            'email' => 'required|string',
            'using_phone_number' => 'required|boolean'
        ]);

        if ($validatedData['using_phone_number'] === true) {
            $su = RpxUser::select('id', 'phone_number')
                ->where('phone_number', '+1'.$validatedData['email'])
                ->first();
            $user = User::find($su->id);
        } else {
            $user = User::select('id', 'email')
                ->where('email', $validatedData['email'])
                ->first();
        }

        if ($user !== null)
        {
            $userId = $user->id;

            if ($validatedData['using_phone_number']) {
                if(!is_null($su)) {
                    $myToken = IlluminatePassword::createToken($user);

                    $sms = app(SystemSms::class)->createResetPasswordSms($user, $su->phone_number);
                    SendResetPasswordSms::dispatch($user, $sms, $myToken)
                        ->onQueue(config('rpx.sms.queue'));

                    $status = 'passwords.sent';
                }
            } else {
                $status = IlluminatePassword::sendResetLink(
                    $request->only('email')
                );
            }
        }
        else
        {
            $status = 'invalid_email';
        }

        $success = true;

        $response = [
            'success' => $success,
            'user'    => $user,
            'status'  => $status,
        ];

        return response($response);
    }

    public function completePassReset(Request $request)
    {
        $success = true;

        $validatedData = $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => ['required', new Password, 'confirmed'],
        ]);

        $status = IlluminatePassword::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new IlluminatePassword($user));
            }
        );

        $response = [
            'success' => $success,
            'status'  => $status,
        ];

        return response($response);
    }

    public function changePassword(Request $request)
    {
        $success = false;

        $user = Auth::user();

        $validatedData = $request->validate([
            'password'         => ['required', new Password, 'confirmed'],
            'current_password' => ['required', new Password],
        ]);

        if (Hash::check($validatedData['current_password'], $user->password))
        {
            $user->password = Hash::make($validatedData['password']);
            $user->save();
            $success = true;
            $message = 'saved';
        }
        else
        {
            $message = 'SB-E-000';
        }

        $response = [
            'success' => $success,
            'message' => $message,
        ];

        return response($response);
    }

    public function deactivate(Request $request)
    {
        $success = false;

        $user = Auth::user();

        $validatedData = $request->validate([
            'password'          => ['nullable', new Password],
            'is_social_account' => ['required', 'boolean'],
            'deactivation_type'  => ['required', 'boolean']
        ]);

        $passwordCheck = false;

        if ($validatedData['is_social_account'] === true)
        {
            $passwordCheck = true;
        }
        else
        {
            if (Hash::check($validatedData['password'], $user->password))
            {
                $passwordCheck = true;
            }
            else
            {
                $success = false;
            }
        }

        if ($passwordCheck)
        {
            //Deactivate all Stripe Memberships
            $deleteStripeMembership = $this->cancelMembership();

            if ($deleteStripeMembership)
            {
                if ($validatedData['deactivation_type'] === true) {
                    $success = $user->forceDelete();
                } else {
                    $success = $user->delete();
                }
            }
            else
            {
                $success = false;
            }
        }

        $response = [
            'success' => $success,
        ];

        return response($response);
    }

    public function activate()
    {
    }

    public function uniqueEmail(Request $request)
    {
        $emailConfirmed = EmailConfirmation::select(
            'email',
            'email_is_verified'
        )
        ->where('email', $request->email)
        ->where('email_is_verified', true)
        ->first();

        if ($emailConfirmed !== null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function checkConfirm(Request $request)
    {
    }

    public function checkIfEmailIsConfirmed($request)
    {
        $emailConfirmed = EmailConfirmation::select(
            'email',
            'email_is_verified'
        )
        ->where('email', $request->email)
        ->where('email_is_verified', true)
        ->first();

        if ($emailConfirmed !== null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function sendCode(Request $request): bool
    {
        $request->validated();

        $propertyInfo = '';
        $lang = 'en_US';

        $user = ['email' => $request->email, 'first_name' => $request->first_name];

        $pin = mt_rand(100000, 999999);

        EmailConfirmation::updateOrCreate([
            'email'             => $request->email,
            'email_is_verified' => false,
        ], [
            'confirmation_token' => $pin,
        ]);

        Mail::queue(new EmailConfirmationEmail($user, $propertyInfo, $pin, $lang));

        return true;
    }

    public function validateEmailConfirmCode(ValidateEmailConfirmCode $request): bool
    {
        $emailToConfirm = EmailConfirmation::select(
            'email',
            'email_is_verified'
        )
        ->where('email', $request->email)
        ->where('email_is_verified', false)
        ->where('confirmation_token', $request->confirm_code)
        ->first();

        if ($emailToConfirm !== null)
        {
            EmailConfirmation::where('email', $request->email)
            ->where('email_is_verified', false)
            ->where('confirmation_token', $request->confirm_code)
            ->update(
                ['email_is_verified' => true]
            );
        }
        else
        {
            return false;
        }

        return true;
    }

    public function checkConfirmCode(CheckEmailConfirmCode $request)
    {
        $now = Carbon::now();

        $emailConfirmed = EmailConfirmation::select(
            'email',
            'email_is_verified'
        )
        ->where('email', $request->email)
        ->where('expires_at', '>', $now->toDateTimeString())
        ->where('email_is_verified', true)
        ->first();

        if ($emailConfirmed !== null)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function businessMembership(Request $request)
    {
        $validatedData = $request->validate([
            'uuid'           => ['required', 'string', 'max:36'],
            'payment_method' => [
                'id' => ['required', 'string'],
            ],
            'payment_type' => ['required', 'string'],
        ]);

        $uuid = $validatedData['uuid'];
        $paymentMethodId = $validatedData['payment_method']['id'];

        $totalLp = 0;
        $rate = 2;
        switch($validatedData['payment_type'])
        {
            case 'business-membership':
                $priceKey = 'rpx.business_subscription_price1';
                $totalLp = 2000;
                break;
            case 'business-membership-1':
                $priceKey = 'rpx.business_subscription_price_1_2';
                $totalLp = 3500;
                break;
            case 'business-membership-2':
                $priceKey = 'rpx.business_subscription_price_2_2';
                $totalLp = 4750;
                break;
        }

        $price_name = config($priceKey);

        $user = User::where('uuid', $uuid)->get();

        if ($user->first())
        {
            $userStripeId = $user[0]->stripe_id;

            $user = Cashier::findBillable($userStripeId);

            $user->updateDefaultPaymentMethod($paymentMethodId);

            // Create the subscription with the payment method provided by the user.
            $user->newSubscription($user->id, [$price_name])->create($paymentMethodId);

            // Set existing trial_ends_at to now
            $user->trial_ends_at = Carbon::now();

            $loyaltyPointBalance = $user->business->loyaltyPointBalance;
            $loyaltyPointBalance->balance = $totalLp;
            $loyaltyPointBalance->reset_balance = $totalLp;
            $loyaltyPointBalance->loyalty_point_dollar_percent_value = $rate;

            DB::transaction(function () use ($user, $loyaltyPointBalance) {
                $user->save();
                $loyaltyPointBalance->save();
            }, 3);

            $user = Cashier::findBillable($userStripeId);
        }

        $response = [
            'success' => true,
            'user'    => $user,
        ];

        return response($response);
    }

    public function membershipStatus(Request $request)
    {
        $validatedData = $request->validate([
            'uuid'        => ['required', 'string', 'max:36'],
            'paymentType' => ['required', 'string', 'max:56'],
        ]);

        $user = User::where('uuid', $validatedData['uuid'])->first();
        $membershipInfo = null;

        if ($user->first())
        {
            $membershipInfo = Cashier::findBillable($user->stripe_id);

            if ($membershipInfo !== null)
            {
                $membershipInfo = $membershipInfo->subscribed($user->id);
            }
        }

        $response = [
            'success'        => true,
            'membershipInfo' => $membershipInfo,
        ];

        return response($response);
    }

    public function cancelMembership()
    {
        $user = Auth::user();

        $userBillable = Cashier::findBillable($user->stripe_id);

        if (!is_null($userBillable))
        {
            if ($userBillable->subscribed($user->id))
            {
                $userBillable->subscription($user->id)->cancel();
            }
        }

        //We also need to cancel all of the user's ads if they have any.
        $userAdList = Ads::withTrashed()
        ->where('business_id', '=', $user->id)
        ->get();

        if ($userAdList->first())
        {
            foreach ($userAdList as $userAd)
            {
                if ($userBillable->subscribed($userAd->id))
                {
                    $userBillable->subscription($userAd->id)->cancel();
                }
                $userAd->delete();
            }
        }

        $response = [
            'success' => true,
        ];

        return response($response);
    }

    public function createUser(Request $request) {

        $loggedInUser = Auth::user();

        $validatedData = $request->validate([
            'email' => ['required', 'unique:users', 'email'],
            'phone_number' => 'sometimes|string|unique:rpx_users|max:35|nullable',
            'firstName' => ['required', new FirstName],
            'promotion.timeRangeOne' => 'nullable|string',
            'promotion.timeRangeTwo' => 'nullable|string',
            'promotion.timeRangeThree' => 'nullable|string',
            'promotion.day' => 'nullable|string',
            'promotion.businessId' => 'nullable|string'
        ]);

        $user = new User();
        $user->username =  $validatedData['firstName'].mt_rand(0, 1000);
        while(User::where('username',  $user->username)->count() > 0) {
            $user->username = $validatedData['firstName'].mt_rand(0, 1000);
        }
        $user->email = $validatedData['email'];
        $user->password = Hash::make('');
        $user->uuid = Str::uuid();

        $newRpxUser = new RpxUser();
        $newRpxUser->first_name = $validatedData['firstName'];
        $newRpxUser->last_name = '';
        $newRpxUser->user_type = 4;
        $newRpxUser->phone_number = $validatedData['phone_number'];

        $message = "success";
        $e = null;

        try {
            $user->save();

            $user = User::where('email', $user->email)->first();

            $newRpxUser->id = $user->id;
            $newRpxUser->save();

            $newRpxUser = RpxUser::where('id', $user->id)->first();

            $loggedInUser = Auth::user();
            $businessName = $loggedInUser->business->name;

            DB::transaction(function() use ($user, $newRpxUser, $businessName) {
                $lpAggregator = new LoyaltyPointBalanceAggregator();
                $lpAggregator->id = $user->id;
                $lpAggregator->balance = 0;
                $lpAggregator->save();

                $this->sendConfirmationEmail($user, $newRpxUser, true);
                $this->sendConfirmationSms($user, $newRpxUser, $businessName);
            });

            if (array_key_exists('promotion', $validatedData)) {
                $deviceAlternatorRecord =  PromoterDeviceAlternator::where('user_id', $loggedInUser->id)->first();

                DB::transaction(function () use (
                    $deviceAlternatorRecord,
                    $validatedData,
                    $user,
                    $newRpxUser,
                    $request,
                    $businessName
                ) {
                    $pB = new PromoterBonus();
                    $pB->time_range_1 = $validatedData["promotion"]["timeRangeOne"];
                    $pB->time_range_2 = $validatedData["promotion"]["timeRangeTwo"];
                    $pB->time_range_3 = $validatedData["promotion"]["timeRangeThree"];
                    $pB->day = $validatedData["promotion"]["day"];
                    $pB->business_id = $validatedData["promotion"]["businessId"];
                    $pB->promoter_id = $deviceAlternatorRecord->user_id;
                    $pB->lp_amount = $deviceAlternatorRecord->lp_amount;
                    $pB->redeemed = false;
                    $pB->device_ip = $request->ip();
                    $pB->device_id = $deviceAlternatorRecord->device_id;
                    $pB->user_id = $user->id;
                    $pB->expires_at = Carbon::now()->addDays(30);
                    $pB->ledger_record_id = '0';
                    $pB->save();

                    $this->sendBonusLpSms(
                        $user,
                        $newRpxUser,
                        $businessName,
                        $pB->lp_amount,
                        $pB->time_range_1,
                        $pB->time_range_2,
                        $pB->time_range_3,
                        $pB->day
                    );
                });
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $message = 'Could not create user account.';
        }

        $signUpResponse = [
            'message'      => $message,
            'user'         => $user,
            'rpx_user' => $newRpxUser,
            'error' => $e
        ];

        return response($signUpResponse);
    }
}
