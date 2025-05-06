<?php

namespace App\Http\Controllers\RedeemableItems;

use App\Http\Controllers\Controller;
use App\Models\RedeemableItems;
use Illuminate\Http\Request;

class RedeemableItemsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, RedeemableItems $redeemableItems): \Illuminate\Http\Response
    {
        return $redeemableItems->index($request);
    }

    public function lpRedeemed(Request $request, RedeemableItems $redeemableItems): \Illuminate\Http\Response
    {
        return $redeemableItems->lpRedeemed($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, RedeemableItems $redeemableItems): \Illuminate\Http\Response
    {
        return $redeemableItems->create($request);
    }

    public function redeem(Request $request, RedeemableItems $redeemableItems)
    {
        return $redeemableItems->redeem($request);
    }

    public function scanReceipt(Request $request, RedeemableItems $redeemableItems)
    {
        return $redeemableItems->scanReceipt($request);
    }
}
