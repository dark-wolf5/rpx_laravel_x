<?php

namespace App\Models;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLocation extends Model
{
    use HasFactory, SoftDeletes;

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id');
    }

    public function rpxUser()
    {
        return $this->belongsTo('App\Models\RpxUser', 'id', 'id');
    }

    public function getMyLocation()
    {
        $user = Auth::user();

        $location = $user->userLocation()->select('loc_x', 'loc_y');

        $response = [
            'loc_x' => $location['loc_x'],
            'loc_y' => $location['loc_y'],
        ];

        return response($response);
    }

    public function saveCurrentLocation(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'loc_x' => 'required|max:90|min:-90|numeric',
            'loc_y' => 'required|max:180|min:-180|numeric',
        ]);

        $user->userLocation->loc_x = round($validatedData['loc_x'], 6);
        $user->userLocation->loc_y = round($validatedData['loc_y'], 6);

        if ($user->userLocation->save())
        {
            $msg = 'success';
        }
        else
        {
            $msg = 'failed';
        }

        $response = [
            'message' => $msg,
            'loc_x'   => $user->userLocation->loc_x,
            'loc_y'   => $user->userLocation->loc_y,
        ];

        return response($response);
    }

    public function retrieveSurroundings(Request $request)
    {
        $validatedData = $request->validate([
            'search_type' => 'required|max:4|min:0|numeric',
            'loc_x'       => 'required|max:90|min:-90|numeric',
            'loc_y'       => 'required|max:180|min:-180|numeric',
        ]);

        $xr = '';

        $search_type = $validatedData['search_type'];
        $loc_x = $validatedData['loc_x'];
        $loc_y = $validatedData['loc_y'];

        if (Auth::check())
        {
            $user = Auth::user();

            switch($search_type)
            {
                case '0':
                case '1':
                    $xr = "user_locations.id != '" . $user['id'] . "' AND";
                    break;
                default:
                    $xr = '';
            }
        }

        $surrounding_object_list = DB::table('user_locations')
        ->join('rpx_users', 'user_locations.id', 'rpx_users.id')
        ->join('users', 'rpx_users.id', 'users.id')
        ->select('rpx_users.default_picture', 'rpx_users.description', 'users.username')
        ->whereRaw("((($xr user_locations.id != 0 AND user_locations.loc_x = $loc_x AND user_locations.loc_y = $loc_y)
        OR ($xr user_locations.id != 0 
        AND (ABS(SQRT(((POWER((user_locations.loc_x - $loc_x), 2)) + (POWER ((user_locations.loc_y - $loc_y), 2))))) <= 0.06)))
        AND rpx_users.user_type = '$search_type')")
        ->offset(0)
        ->limit(200)
        ->get();

        $response = [
            'message'                 => 'success',
            'surrounding_object_list' => $surrounding_object_list,
        ];

        return response($response);
    }

    public function createUserLocation(User $user)
    {
        $userLocation = new UserLocation();

        $userLocation->id = $user->id;
        $userLocation->loc_x = config('rpx.my_loc_x');
        $userLocation->loc_y = config('rpx.my_loc_y');
        $userLocation->ip_address = 0;

        $userLocation->save();
    }
}
