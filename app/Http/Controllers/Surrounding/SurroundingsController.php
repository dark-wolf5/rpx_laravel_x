<?php

namespace App\Http\Controllers\Surrounding;

use App\Http\Controllers\Controller;
use App\Services\SurroundingsApi;
use Illuminate\Http\Request;
use App\Models\Business;
use Illuminate\Support\Facades\DB;

class SurroundingsController extends Controller
{
    private $apiService;

    public function __construct()
    {
        $this->apiService = new SurroundingsApi();
    }

    public function pullInfoObject(Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $this->apiService->pullInfoObject($request->config_url),
        ];

        return response($response);
    }

    public function searchBusinesses(Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $this->apiService->searchBusinesses($request),
        ];

        return response($response);
    }

    public function searchEvents(Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $this->apiService->searchEvents($request),
        ];

        return response($response);
    }

    public function getEvent(Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $this->apiService->getEvent($request),
        ];

        return response($response);
    }

    public function getClassifications(Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $this->apiService->getClassifications($request),
        ];

        return response($response);
    }

    public function getSbCommunityMembers(Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $this->nearByBusinessList($request),
        ];

        return response($response);
    }

    public function nearByBusinessList(Request $request)
    {
        $validatedData = $request->validate([
            'loc_x'      => 'required|max:90|min:-90|numeric',
            'loc_y'      => 'required|max:180|min:-180|numeric',
            'categories' => 'required|string|numeric',
        ]);

        $categories = $validatedData['categories'];

        $loc_x = $validatedData['loc_x'];
        $loc_y = $validatedData['loc_y'];

        $maxDistance = 0.1;
        $earthRadius = "3958.8";

        $data = Business::select(
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
            DB::raw("(
                $earthRadius * ACOS(
                    COS(RADIANS($loc_y)) * COS(RADIANS(business.loc_y)) *
                    COS(RADIANS(business.loc_x) - RADIANS($loc_x)) +
                    SIN(RADIANS($loc_y)) * SIN(RADIANS(business.loc_y))
                )
            ) AS max_distance"),
        )
        ->with('loyaltyTiers')
        ->join('rpx_users', 'business.id', '=', 'rpx_users.id')
        ->join('loyalty_point_balances', function ($join) {
            $join->on('business.id', '=', 'loyalty_point_balances.business_id')
            ->where('loyalty_point_balances.loyalty_point_dollar_percent_value', '>', 0);
        })
        ->where('business.is_verified', 1)
        ->where('business.categories', $categories)
        ->having('max_distance', '>', $maxDistance)
        ->has('rewards')
        ->inRandomOrder()
        ->limit(8)
        ->get();

        return $data;
    }
}
