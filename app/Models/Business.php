<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Image;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'business';

    protected $fillable = ['photo'];

    public function rewards()
    {
        return $this->hasMany('App\Models\Reward', 'business_id', 'id');
    }

    public function loyaltyTiers()
    {
        return $this->hasMany('App\Models\LoyaltyTier', 'business_id', 'id');
    }

    public function loyaltyPointBalance()
    {
        return $this->hasOne('App\Models\LoyaltyPointBalance', 'user_id', 'id');
    }

    public function loyaltyPointLedger()
    {
        return $this->hasOne('App\Models\LoyaltyPointLedger', 'business_id', 'id');
    }

    public function redeemables()
    {
        return $this->hasMany('App\Models\RedeemableItems', 'business_id');
    }

    public function recentGuests(): HasMany
    {
        return $this->hasMany('App\Models\LoyaltyPointBalance', 'from_business', 'id');
    }

    public function businessExposure()
    {
        return $this->hasOne('App\Models\BusinessExposure', 'business_id')->latestOfMany();
    }

    public function ads()
    {
        return $this->hasMany('App\Models\Ads', 'business_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo('App\Models\User', 'id');
    }

    public function rpxUser(): BelongsTo
    {
        return $this->belongsTo('App\Models\RpxUser', 'id');
    }

    public function feedback(): HasManyThrough
    {
        return $this->hasManyThrough('App\Models\Feedback', 'App\Models\LoyaltyPointLedger', 'business_id', 'ledger_record_id', 'id', 'id');
    }

    public function verify(Request $request)
    {
        $validatedData = $request->validate([
            'name'        => 'required|string|max:75|min:1',
            'description' => 'required|string|max:350|min:1',
            'address'     => 'required|string|max:350|min:1',
            'city'        => 'required|string',
            'country'     => 'required|string',
            'line1'       => 'nullable|string',
            'line2'       => 'nullable|string',
            'postal_code' => 'nullable|string',
            'state'       => 'nullable|string',
            'photo'       => 'required|string|max:650|min:1',
            'loc_x'       => 'required|max:90|min:-90|numeric',
            'loc_y'       => 'required|max:180|min:-180|numeric',
            'categories'  => 'required|string',
            'passkey'     => 'required|string|max:20|min:4',
            'accountType' => 'required|numeric',
            'is_food_truck' => 'boolean',
            'lp_rate' => 'required|numeric',
        ]);

        $user = Auth::user();
        $confirmKey = 'K23' . $user->id;

        $rpxBusinessPassKey = $confirmKey;

        if ($rpxBusinessPassKey !== $validatedData['passkey']) {
            $response = [
                'message' => 'passkey_mismatch',
            ];
            return response($response);
        }

        $user->rpxUser->user_type = $validatedData['accountType'];

        // Check if the business already exists.
        $existingBusiness = $user->business;

        if (!is_null($existingBusiness))
        {
            $business = $user->business;
        }
        else
        {
            $business = new Business();
        }

        $business->id = $user->id;
        $business->name = $validatedData['name'];
        $business->description = $validatedData['description'];
        $business->slug = Str::slug($business->name);
        $business->address = $validatedData['address'];
        $business->city = $validatedData['city'];
        $business->country = $validatedData['country'];
        $business->line1 = $validatedData['line1'];
        $business->line2 = $validatedData['line2'];
        $business->postal_code = $validatedData['postal_code'];
        $business->state = $validatedData['state'];
        $business->photo = $validatedData['photo'];
        $business->loc_x = $validatedData['loc_x'];
        $business->loc_y = $validatedData['loc_y'];
        $business->categories = $validatedData['categories'];
        $business->is_verified = 1;
        $business->qr_code_link = Str::uuid();
        $business->is_food_truck = $validatedData['is_food_truck'];

        $lpRate = $validatedData['lp_rate'];

        DB::transaction(function () use ($business, $user, $lpRate) {
            $business->save();
            $user->rpxUser->save();
            $currentLpRate = $user->loyaltyPointBalance()->where('business_id', $business->id)->first();
            $currentLpRate->loyalty_point_dollar_percent_value = $lpRate;
            $currentLpRate->save();
            $user->save();
        }, 3);

        $response = [
            'message'   => 'success',
            'business'  => $business,
        ];

        return response($response);
    }

    public function saveBusiness(Request $request)
    {
        $validatedData = $request->validate([
            'name'        => 'required|string|max:75|min:1',
            'description' => 'required|string|max:350|min:1',
            'address'     => 'required|string|max:350|min:1',
            'city'        => 'required|string',
            'country'     => 'required|string',
            'line1'       => 'nullable|string',
            'line2'       => 'nullable|string',
            'postal_code' => 'nullable|string',
            'state'       => 'nullable|string',
            'photo'       => 'required|string|max:650|min:1',
            'loc_x'       => 'required|max:90|min:-90|numeric',
            'loc_y'       => 'required|max:180|min:-180|numeric',
            'categories'  => 'required|string',
            'accountType' => 'required|numeric',
            'is_food_truck' => 'boolean',
        ]);

        $user = Auth::user();

        $user->rpxUser->user_type = $validatedData['accountType'];

        // check if the business already exists.
        $existingBusiness = $user->business;

        if (!is_null($existingBusiness))
        {
            $business = $user->business;
        }
        else
        {
            $business = new Business();
        }

        $business->id = $user->id;
        $business->name = $validatedData['name'];
        $business->description = $validatedData['description'];
        $business->slug = Str::slug($business->name);

        $business->address = $validatedData['address'];

        $business->city = $validatedData['city'];
        $business->country = $validatedData['country'];
        $business->line1 = $validatedData['line1'];
        $business->line2 = $validatedData['line2'];
        $business->postal_code = $validatedData['postal_code'];
        $business->state = $validatedData['state'];

        $business->photo = $validatedData['photo'];
        $business->loc_x = $validatedData['loc_x'];
        $business->loc_y = $validatedData['loc_y'];
        $business->categories = $validatedData['categories'];

        $business->qr_code_link = Str::uuid();

        DB::transaction(function () use ($business, $user) {
            $business->save();
            $user->rpxUser->save();

            $business->refresh();

            if ($business->loyaltyPointBalance === null) {
                $lpBalance = new LoyaltyPointBalance();
                $lpBalance->user_id = $user->id;
                $lpBalance->from_business = 0;
                $lpBalance->business_id = $business->id;
                $lpBalance->balance = 0;
                $lpBalance->reset_balance = 0;
                $lpBalance->loyalty_point_dollar_percent_value = 2; // Default start value
                $lpBalance->end_of_month = Carbon::now()->addMonth();
                $lpBalance->save();
            }

            if(is_null( $business->businessExposure()->get()[0] ?? null)) {
                $businessExposure = new BusinessExposure();
                $businessExposure->total_exposure = 0;
                $businessExposure->business_id = $business->id;
                $businessExposure->save();
            }
        }, 3);

        $response = [
            'message'  => 'success',
            'business' => $business,
        ];

        return response($response);
    }

    public function getGooglePlacesToEat(Request $request)
    {
        $request->validate([
            'url'    => 'required|string|max:250|min:1',
            'bearer' => 'required|string|max:250|min:1',
        ]);

        $url = $request->url;
        $gToken = $request->bearer;

        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, $url);

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Access-Control-Allow-Credentials: true',
            'Content-Type: application/json',
            "Authorization: Bearer $gToken",
        ]);

        // $output contains the output string
        $output = curl_exec($ch);

        // close curl resource to free up system resources
        curl_close($ch);

        $response = [
            'message'    => 'success',
            'g_response' => $output,
        ];

        return response($response);
    }

    public function saveLocation(Request $request) {
        $validatedData = $request->validate([
            'address'     => 'required|string|max:350|min:1',
            'city'        => 'required|string',
            'country'     => 'required|string',
            'line1'       => 'nullable|string',
            'line2'       => 'nullable|string',
            'postal_code' => 'nullable|string',
            'state'       => 'nullable|string',
            'photo'       => 'required|string|max:650|min:1',
            'loc_x'       => 'required|max:90|min:-90|numeric',
            'loc_y'       => 'required|max:180|min:-180|numeric',
        ]);

        $user = Auth::user();
        $business = $user->business();

        $business->update([
            'loc_x' => $validatedData['loc_x'],
            'loc_y' => $validatedData['loc_y'],
            'address' => $validatedData['address'],
            'city' => $validatedData['city'],
            'country' => $validatedData['country'],
            'line1' => $validatedData['line1'],
            'line2' => $validatedData['line2'],
            'postal_code' => $validatedData['postal_code'],
            'state' => $validatedData['state'],
            'photo' => $validatedData['photo'],
        ]);

        $response = [
            'success'  => true,
        ];

        return response($response);
    }

    public function show(Request $request)
    {
        $validatedData = $request->validate([
            'qrCodeLink' => ['required', 'string'],
        ]);

        $business = Business::select(
            'business.qr_code_link',
            'business.name',
            'business.categories',
            'business.description',
            'business.address',
            'business.photo',
            'business.qr_code_link',
            'business.loc_x',
            'business.loc_y',
            'business.id',
            'rpx_users.user_type',
        )
        ->join('rpx_users', 'business.id', '=', 'rpx_users.id')
        ->with('loyaltyPointBalance')
        ->where('qr_code_link', $validatedData['qrCodeLink'])
        ->orWhere('slug', $validatedData['qrCodeLink'])
        ->get();

        $businessTierList = null;
        if (count($business) > 0) {
            $businessTierList = LoyaltyTier::where('business_id', $business[0]->id)->get();
            $success = true;
            $business = $business[0];
        } else {
            $success = false;
        }

        $response = [
            'success'  => $success,
            'business' => $business,
            'business_tier_list'  => $businessTierList,
        ];

        return response($response);
    }

    public function uploadPhoto(Request $request)
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

        $imagePath = 'defaults/images/' . $user->id . '/' . $hashedFileName;
        Storage::disk('s3')->put($imagePath, $newFile, 'public');
        $imagePath = Storage::disk('s3')->url($imagePath);

        $response = [
            'success' => $success,
            'message' => $environment,
            'image'   => $imagePath,
        ];

        return response($response);
    }
}
