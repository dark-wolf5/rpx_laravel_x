<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/** @property int $business_id */
/** @property int $clicks */
/** @property string $created_at */
/** @property string $deleted_at */
/** @property float $dollar_cost */
/** @property string $ends_at */
/** @property string $images */
/** @property string $images_mobile */
/** @property bool $is_live */
/** @property string $name */
/** @property int $subscription_id */
/** @property int $type */
/** @property string $updated_at */
/** @property string $uuid */
/** @property int $views */
/** @property int $views_from_start */
class Ads extends Model
{
    use HasFactory, SoftDeletes;

    public $table = 'ads';

    protected $fillable = ['business_id'];

    /**
     * Every ad belongs to a business. Not every business has an ad. A business can have multiple ads.
     */
    public function business(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Business', 'business_id', 'id');
    }

    /**
     * Before users pull up an ad we first need to make sure we pull up a business. The way this works is that
     * a random nearby business is pulled up based on the user's location and user type.
     */
    public function nearbyBusinessNoCategory(
        string $loc_x,
        string $loc_y,
        int $businessType,
        string $categories
    ): Business | null
    {
        $a = Business::select(
                'business.id',
                'business.qr_code_link',
                'business.name',
                'business.address',
                'business.categories',
                'business.description',
                'business.photo',
                'business.qr_code_link',
                'business.loc_x',
                'business.loc_y',
                'rpx_users.user_type',
                'loyalty_point_balances.balance',
                'loyalty_point_balances.loyalty_point_dollar_percent_value',
                'business_exposure.total_exposure'
            )
            ->where('business.is_verified', 1)
            ->join('business_exposure', 'business_exposure.business_id', '=', 'business.id')
            ->join('rpx_users', 'business.id', '=', 'rpx_users.id')
            ->join('loyalty_point_balances', function ($join) {
                $join->on('business.id', '=', 'loyalty_point_balances.business_id');
            })
            ->where('rpx_users.user_type', '=', $businessType)
            ->where('business.categories', '=', $categories)
            ->whereRaw("(
                (business.loc_x = {$loc_x} AND business.loc_y = {$loc_y})
                OR (
                        ABS (
                                SQRT    (
                                            (POWER ( (business.loc_x - {$loc_x}), 2) ) +
                                            (POWER ( (business.loc_y - {$loc_y}), 2) )
                                        )
                            )
                        <= 0.1
                    )
            )")
            ->orderBy('business_exposure.total_exposure', 'asc')->get()[0] ?? null;

        return $a;
    }

    /**
     * Before users pull up an ad we first need to make sure we pull up a business. The way this works is that
     * a nearby business in the chosen $category is pulled up based on the user's location and businessType.
     */
    public function nearbyBusiness($loc_x, $loc_y, $category, $businessType, $adType): Business | null
    {
        $a = Business::select(
            'business.id',
            'business.qr_code_link',
            'business.name',
            'business.address',
            'business.categories',
            'business.description',
            'business.photo',
            'business.qr_code_link',
            'business.loc_x',
            'business.loc_y',
            'rpx_users.user_type',
            'loyalty_point_balances.balance',
            'loyalty_point_balances.loyalty_point_dollar_percent_value',
            'business_exposure.business_id',
            'business_exposure.total_exposure',
        )
        ->whereHas('ads', function (Builder $qry) use ($adType) {
            $qry->where('type',  '=', $adType);
        })
        ->join('business_exposure', 'business_exposure.business_id', '=', 'business.id')
        ->join('rpx_users', 'business.id', '=', 'rpx_users.id')
        ->join('loyalty_point_balances', function ($join) {
            $join->on('business.id', '=', 'loyalty_point_balances.business_id');
        })
        ->where('rpx_users.user_type', '=', $businessType)
        ->where('business.categories', $category)
        ->whereRaw("(
            (business.loc_x = {$loc_x} AND business.loc_y = {$loc_y})
            OR (
                    ABS (
                            SQRT    (
                                        (POWER ( (business.loc_x - {$loc_x}), 2) ) +
                                        (POWER ( (business.loc_y - {$loc_y}), 2) )
                                    )
                        )
                    <= 0.1
                )
        )")
        ->orderBy('business_exposure.total_exposure', 'asc')->get();

        Log::info('Nearby Business: '. ($a[0] ?? null) );

        return ($a[0] ?? null);
    }

    /**
     * A header banner is displayed at the bottom of the interactive map we have on the front end of the app. This method
     * basically takes in a Request with the user's location, picked categories, account type, and account id. Additionally
     * this method adds a view to the ad.
     */
    public function headerBanner(Request $request)
    {
        $validatedData = $request->validate([
            'loc_x'        => 'max:90|min:-90|numeric',
            'loc_y'        => 'max:180|min:-180|numeric',
            'categories'   => 'nullable|numeric',
            'id'           => 'nullable|numeric',
            'account_type' => 'nullable|numeric',
        ]);

        $accountType = $validatedData['account_type'];

        if (!is_null($validatedData['id'])) {
            $ad = Ads::find($validatedData['id']);

            $this->addClickToAd($ad);

            $business = Business::find($ad->business_id);

            $totalRewards = count(Reward::select('business_id')
                ->where('business_id', '=', $business->id)
                ->get());

            $response = [
                'success'      => true,
                'business'     => $business,
                'ad'           => $ad,
                'totalRewards' => $totalRewards,
            ];

            return response($response);
        }

        $loc_x = $validatedData['loc_x'];
        $loc_y = $validatedData['loc_y'];

        $categories = $validatedData['categories'];
        $categories = $this->returnCategory($categories, $accountType);

        // Get a nearby business.
        $nearbyBusiness = $this->nearbyBusiness($loc_x, $loc_y, $categories, $accountType, 0);

        if (is_null($nearbyBusiness)) {
            $nearbyBusiness = $this->nearbyBusinessNoCategory($loc_x, $loc_y, $accountType, $categories);
        }

        if (is_null($nearbyBusiness)) {
            $ad = $this->getRpxAd(0);

            $nearbyBusiness = null;
            $totalRewards = 0;
        } else {
            $adInfo = $this->getAd($nearbyBusiness, $loc_x, $loc_y, $accountType, 0, $categories);

            $ad = $adInfo["ad"];
            $nearbyBusiness = $adInfo["nearbyBusiness"];
            $totalRewards = $adInfo["totalRewards"];
        }

        return [
            'success'      => true,
            'business'     => $nearbyBusiness,
            'ad'           => $ad,
            'totalRewards' => $totalRewards,
        ];
    }

    public function getRpxAd($adType)
    {
        return RpxAds::getRpxAd($adType);
    }

    public function getRpxAdList()
    {
        return RpxAds::getRpxAd(1);
    }

    public function addClickToAd(Ads $ad)
    {
        // Add click to ad.
        DB::transaction(function () use ($ad) {
            ++$ad->clicks;
            $ad->save();
        }, 3);
    }

    public function addViewToAd(Ads $ad)
    {
        Log::info("[Ads][addViewToAd] Adding - ID: ".$ad->id);
        $ad = Ads::find($ad->id);

        // Add click to ad.
        DB::transaction(function () use ($ad) {
            $ad->views = $ad->views + 1;
            $ad->views_from_start = $ad->views_from_start + 1;
            $ad->save();
            Log::info("[Ads][addViewToAd] Added - ID: ".$ad->id. " - Views: ". $ad->views);

            $this->updateTotalExposure($ad->business_id);
        }, 3);
    }

    public function updateTotalExposure($businessId) {
        /**
         * Move this into a service
         */
        $business = Business::find($businessId);
        $businessExposure = BusinessExposure::where('business_id', $businessId)->first();

        if ( is_null($businessExposure) ) {
            $businessExposure = new BusinessExposure();
            $businessExposure->business_id = $business->id;
            $viewCalculation = 0;
            $businessExposure->total_exposure = $viewCalculation;
            Log::info('[BusinessExposure][createTotalExposure] Business ID: '.$businessId.' Record Id: '.$businessExposure->id . ' Business Exposure: ' . $viewCalculation);
            $businessExposure->save();
        } else {
            $viewCalculationList = Ads::select('id', 'views', 'clicks', 'views_from_start', DB::raw('(views * .2) + (clicks * .4) + (views_from_start * .4) as business_exposure'))
                ->where('is_live', 1)
                ->where('business_id', $business->id)->get();
            $vcSum = 0;
            foreach ($viewCalculationList as $vc)
            {
                $vcSum = $vcSum + $vc->business_exposure / count($viewCalculationList);
            }

            Log::info('[BusinessExposure][updateTotalExposure] Business ID: '.$businessId.' Record Id: '.$businessExposure->id . ' Business Exposure: ' . $vcSum);
            $businessExposure->business_id = $business->id;
            $businessExposure->total_exposure = $vcSum;
            $businessExposure->save();
        }
    }

    public function getByUuid(Request $request)
    {
        $validatedData = $request->validate([
            'uuid' => 'required|string',
        ]);

        $ad = Ads::select('*')
            ->where('uuid', '=', $validatedData['uuid'])
            ->first();

        $business = Business::where('id', '=', $ad->business_id)
            ->first();

        $response = [
            'success'  => true,
            'business' => $business,
            'ad'       => $ad,
        ];

        return response($response);
    }

    public function footerBanner(Request $request)
    {
        $validatedData = $request->validate([
            'loc_x'        => 'max:90|min:-90|numeric',
            'loc_y'        => 'max:180|min:-180|numeric',
            'categories'   => 'nullable|numeric',
            'id'           => 'nullable|numeric',
            'account_type' => 'nullable|numeric',
        ]);

        $accountType = $validatedData['account_type'];

        if (isset($validatedData['id'])) {
            $ad = Ads::find($validatedData['id']);

            $this->addClickToAd($ad);

            $business = Business::find($ad->business_id);

            $totalRewards = count(Reward::select('business_id')
                ->where('business_id', '=', $business->id)
                ->get());

            $response = [
                'success'      => true,
                'business'     => $business,
                'ad'           => $ad,
                'totalRewards' => $totalRewards,
            ];

            return response($response);
        }

        $loc_x = $validatedData['loc_x'];
        $loc_y = $validatedData['loc_y'];

        $categories = $validatedData['categories'] ?? null;
        $categories = $this->returnCategory($categories, $accountType);

        // Get a nearby business.
        $nearbyBusiness = $this->nearbyBusiness($loc_x, $loc_y, $categories, $accountType, 2);

        if (is_null($nearbyBusiness)) {
            $nearbyBusiness = $this->nearbyBusinessNoCategory($loc_x, $loc_y, $accountType, $categories);
        }

        if (is_null($nearbyBusiness)) {
            $ad = $this->getRpxAd(2);

            $nearbyBusiness = null;
            $totalRewards = 0;
        } else {
            $adInfo = $this->getAd($nearbyBusiness, $loc_x, $loc_y, $accountType,2, $categories);
            $ad = $adInfo["ad"];
            $nearbyBusiness = $adInfo["nearbyBusiness"];
            $totalRewards = $adInfo["totalRewards"];
        }

        $response = [
            'success'      => true,
            'business'     => $nearbyBusiness,
            'ad'           => $ad,
            'totalRewards' => $totalRewards,
        ];

        return response($response);
    }

    public function getAd(
        $nearbyBusiness,
        string $loc_x,
        string $loc_y,
        string $accountType,
        int $adType,
        string $categories
    ) {
        //  If there is a nearby business, then try getting one of its ads.
        $ad = $this->nearbyAd($nearbyBusiness->id, $adType);

        if (! is_null($ad)) {
            $this->addViewToAd($ad);
        }

        // If there is no Ad, then return either one from a nearbyBusiness with an unrelated category, or
        // if there is no nearby business with an unrelated category, then return an ad from the SopotBie as list
        while( is_null($ad) ) {
            $nearbyBusiness = $this->nearbyBusiness($loc_x, $loc_y, $accountType, $categories, $adType);

            if (! is_null($nearbyBusiness)) {
                // If there is a nearby Business with an unrelated category, try getting an AD from it.
                $ad = $this->nearbyAd($nearbyBusiness->id, $adType);
                if(is_null($ad)) {
                    $ad = $this->getRpxAd($adType);
                    $nearbyBusiness = null;
                } else {
                    $ad = $ad[0];
                    $this->addViewToAd($ad);
                }

                break;
            } else {
                // else, let's return a Rpx Ad.
                $nearbyBusiness = null;
                $ad = $this->getRpxAd($adType);
                break;
            }
        }

        if (! is_null($nearbyBusiness)) {
            $totalRewards = count(Reward::select('business_id')
                ->where('business_id', '=', $nearbyBusiness->id)
                ->get());

        } else {
            $ad = $this->getRpxAd($adType);
            $totalRewards = 0;
        }

        return [
            "totalRewards" => $totalRewards,
            "nearbyBusiness" => $nearbyBusiness,
            "ad" => $ad
        ];
    }

    public function nearbyAd($businessId, $type)
    {
        $a = Ads::select(
            'id', 'uuid', 'business_id', 'type', 'name', 'images', 'images_mobile', 'views_from_start',
            'views', 'clicks', DB::raw('(views_from_start * .4) + (views * .2) + (clicks * .4) as total_exposure')
        )
        ->where('type', $type)
        ->where('business_id', '=', $businessId)
        ->where('is_live', '=', 1)
        ->orderBy('total_exposure', 'desc')
        ->get()[0] ?? null;

        Log::info("NearbyAD: " . $a . " Business ID: " . $businessId);

        return $a;
    }

    public function featuredAdList(Request $request)
    {
        $validatedData = $request->validate([
            'loc_x'        => 'max:90|min:-90|numeric',
            'loc_y'        => 'max:180|min:-180|numeric',
            'categories'   => 'numeric',
            'id'           => 'nullable|numeric',
            'account_type' => 'nullable|numeric',
        ]);

        $accountType = $validatedData['account_type'];

        if (isset($validatedData['id'])) {
            $ad = Ads::find($validatedData['id']);

            $this->addClickToAd($ad);

            $business = Business::find($ad->business_id);

            $totalRewards = count(Reward::select('business_id')
                ->where('business_id', '=', $business->id)
                ->get());

            $response = [
                'success'      => true,
                'business'     => $business,
                'ad'           => $ad,
                'totalRewards' => $totalRewards,
            ];

            return response($response);
        }

        $loc_x = $validatedData['loc_x'];
        $loc_y = $validatedData['loc_y'];

        $categories = $validatedData['categories'];
        $categories = $this->returnCategory($categories, $accountType);

        // Get a nearby business.
        $nearbyBusiness = $this->nearbyBusiness($loc_x, $loc_y, $categories, $accountType, 1);

        if (is_null($nearbyBusiness)) {
            $nearbyBusiness = $this->nearbyBusinessNoCategory($loc_x, $loc_y, $accountType, $categories);
        }

        if (is_null($nearbyBusiness)) {
            $ad = $this->getRpxAdList();

            $nearbyBusiness = null;
            $totalRewards = 0;
        } else {
            $adInfo = $this->getAd($nearbyBusiness, $loc_x, $loc_y, $accountType, 1, $categories);
            $ad = $adInfo["ad"];
            $nearbyBusiness = $adInfo["nearbyBusiness"];
            $totalRewards = $adInfo["totalRewards"];
        }

        Log::info("FeaturedAdList business: " . $nearbyBusiness);

        $response = [
            'success'      => true,
            'business'     => $nearbyBusiness,
            'ad'           => $ad,
            'totalRewards' => $totalRewards,
        ];

        return response($response);
    }

    public function uploadMedia(Request $request)
    {
        $success = true;
        $message = null;

        $validatedData = $request->validate([
            'image' => 'required|image|max:25000',
        ]);

        $user = \Auth::user();

        $hashedFileName = $validatedData['image']->hashName();

        $newFile = \Image::make($request->file('image'))->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $newFile = $newFile->encode('jpg', 60);
        $newFile = (string) $newFile;

        $imagePath = 'ad-media/images/' . $user->id . '/' . $hashedFileName;
        Storage::disk('s3')->put($imagePath, $newFile, 'public');
        $imagePath = Storage::disk('s3')->url($imagePath);

        $response = [
            'success' => $success,
            'message' => $message,
            'image'   => $imagePath,
        ];

        return response($response);
    }

    public function index()
    {
        $user = \Auth::user();

        if (!$user)
        {
            $response = [
                'success' => false,
                'message' => 'You are not authorized to view this content.',
            ];

            return response($response);
        }

        $adList = $user->business->ads()->get();

        $response = [
            'success' => true,
            'adList'  => $adList,
        ];

        return response($response);
    }

    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name'          => 'required|string|max:75|min:1',
            'images'        => 'required|string|max:500|min:1',
            'images_mobile' => 'required|string|max:500|min:1',
            'type'          => 'required|numeric|max:6',
        ]);

        $user = \Auth::user();

        if ($user)
        {
            $business = $user->business;
        }

        $businessAd = new Ads();

        $businessAd->uuid = Str::uuid();

        $greatestAdViews = Ads::select('is_live', 'type', 'views')
            ->where('is_live', true)
            ->where('type', $validatedData['type'])
            ->orderByDesc('views')
            ->get()->pluck('views')[0] ?? 0;

        $businessAd->business_id = $business->id;

        $businessAd->name = $validatedData['name'];
        $businessAd->images = $validatedData['images'];
        $businessAd->images_mobile = $validatedData['images_mobile'];
        $businessAd->type = $validatedData['type'];
        $businessAd->is_live = true;
        $businessAd->views = $greatestAdViews;
        $businessAd->views_from_start = 0;

        switch ($businessAd->type)
        {
            case 0:
                $businessAd->dollar_cost = 19.99;
                break;
            case 1:
                $businessAd->dollar_cost = 13.99;
                break;
            case 2:
                $businessAd->dollar_cost = 16.99;
                break;
        }

        DB::transaction(function () use ($businessAd) {
            $businessAd->save();
        }, 3);

        $newAd = $businessAd->refresh();

        $response = [
            'success' => true,
            'newAd'   => $newAd,
        ];

        return response($response);
    }

    public function updateModel(Request $request)
    {
        $validatedData = $request->validate([
            'id'            => 'required|numeric',
            'name'          => 'required|string|max:75|min:1',
            'images'        => 'required|string|max:500|min:1',
            'images_mobile' => 'required|string|max:500|min:1',
            'type'          => 'required|numeric|max:6',
        ]);

        $user = \Auth::user();

        if ($user)
        {
            $business = $user->business;
        }

        $businessAd = $business->ads()->find($validatedData['id']);

        $businessAd->business_id = $business->id;
        $businessAd->name = $validatedData['name'];

        $businessAd->images = $validatedData['images'];
        $businessAd->images_mobile = $validatedData['images_mobile'];

        $businessAd->type = $validatedData['type'];

        switch ($businessAd->type)
        {
            case 0:
                $businessAd->dollar_cost = 19.99;

                break;
            case 1:
                $businessAd->dollar_cost = 13.99;

                break;
            case 2:
                $businessAd->dollar_cost = 16.99;

                break;
        }

        DB::transaction(function () use ($businessAd) {
            $businessAd->save();
        }, 3);

        $response = [
            'success' => true,
            'newAd'   => $businessAd,
        ];

        return response($response);
    }

    public function deleteModel(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'required|numeric',
        ]);

        $user = \Auth::user();

        $adToDelete = $validatedData['id'];

        if ($user)
        {
            DB::transaction(function () use ($adToDelete) {
                Ads::where('id', $adToDelete)
                    ->update([
                        'is_live' => 0,
                    ])
                ;

                Ads::where('id', $adToDelete)->delete();
            }, 3);
        }

        $response = [
            'success' => true,
        ];

        return response($response);
    }

    private function returnCategory($categories, $accountType)
    {
        if (!$categories)
        {
            $parentCategory = null;

            switch ($accountType)
            {
                case 1:
                    $parentCategory = config('rpx.my_business_categories_food');

                    break;
                case 2:
                    $parentCategory = config('rpx.my_business_categories_shopping');

                    break;
                case 3:
                    $parentCategory = config('rpx.my_business_categories_events');

                    break;
            }

            $max = count($parentCategory) - 1;
            $needle = $parentCategory[rand(0, $max)];
            $key = array_search($needle, $parentCategory);
            $categories = $key;
        }

        return $categories;
    }
}
