<?php

namespace App\Models;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyPointBalance extends Model
{
    use SoftDeletes;

    public $table = 'loyalty_point_balances';

    public $fillable = ['balance', 'balance_aggregate', 'loyalty_point_dollar_percent_value', 'end_of_month'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function fromBusiness()
    {
        return $this->belongsTo('App\Models\Business', 'from_business', 'id');
    }

    public function redeemed()
    {
        return $this->hasMany('App\Models\RedeemableItems', 'redeemer_id', 'id');
    }

    public function balanceList(Request $request)
    {
        $user = Auth::user();

        if ($user->rpxUser->user_type === 4)
        {
            $balanceList = $user->loyaltyPointBalance()
                ->whereHas('fromBusiness')
                ->with('fromBusiness', function ($query) {
                    $query->with('rpxUser');
                })
                ->orderBy('updated_at', 'desc')
                ->paginate(10);
        }
        else
        {
            // User is a business
            $balanceList = $user->business()->loyaltyPointBalance()->get();
        }

        $response = [
            'success'     => true,
            'balanceList' => $balanceList,
        ];

        return response($response);
    }

    public function setLpRate(Request $request) {
        $user = Auth::user();

        $validatedData = $request->validate([
            'percent' => 'required|numeric'
        ]);

         $lp = LoyaltyPointBalance::where('user_id', $user->id)->first();
         $lp->loyalty_point_dollar_percent_value = $validatedData['percent'];

        DB::transaction(function () use ($lp) {
            $lp->save();
        });

        return response([
            'success' => true,
            'message' => "Reward rate updated successfully.",
        ]);
    }

    public function show()
    {
        $success = false;
        $user = Auth::user();

        if ($user->business)
        {
            $loyaltyPoints = $user
                ->business
                ->loyaltyPointBalance()
                ->select('balance', 'reset_balance', 'loyalty_point_dollar_percent_value', 'end_of_month')
                ->get();
        }
        else
        {
            $loyaltyPoints = $user
                ->loyaltyPointBalance()
                ->select('balance', 'reset_balance', 'loyalty_point_dollar_percent_value', 'end_of_month')
                ->get();
        }

        if ($loyaltyPoints)
        {
            $success = true;
        }
        else
        {
            $loyaltyPoints = new LoyaltyPointBalance();
            $loyaltyPoints->balance = 0;
            $loyaltyPoints->reset_balance = 0;
            $loyaltyPoints->loyalty_point_dollar_percent_value = 0;
            $loyaltyPoints->end_of_month = null;
        }

        $response = [
            'success'        => $success,
            'loyalty_points' => $loyaltyPoints,
        ];

        return response($response);
    }
}
