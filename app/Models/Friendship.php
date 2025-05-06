<?php

namespace App\Models;

use App\Jobs\SendInviteContactSms;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Friendship extends Model
{
    use HasFactory;

    protected $fillable = [];

    public function userProfile(){
        return $this->belongsTo( '\App\Models\User', 'user_id', 'id');
    }

    public function rpxProfile(){
        return $this->belongsTo( '\App\Models\RpxUser', 'user_id', 'id');
    }

    public function friendRpxProfile(){
        return $this->belongsTo( '\App\Models\RpxUser', 'friend_id', 'id');
    }


    public function friendProfile(){
        return $this->belongsTo( '\App\Models\User', 'friend_id', 'id');
    }

    public function requestFriendship(Request $request): Response {
        $validatedData = $request->validate([
            'friend_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $existing = Friendship::where(function ($qry) use ($user, $validatedData) {
                $qry->where('user_id', $user->id)
                    ->orWhere('user_id', $validatedData['friend_id']);
            })->where(function ($qry) use ($user, $validatedData) {
                $qry->where('friend_id', $user->id)
                    ->orWhere('friend_id', $validatedData['friend_id']);
            })->get();

        if (count($existing) > 0) {
            return response(['message' => 'Friendship already exists.'], 403);
        }

        $friendship = new Friendship();
        $friendship->user_id = $user->id;
        $friendship->friend_id = $validatedData['friend_id'];
        $friendship->relationship = config('enums.friendships.PENDING');
        $friendship->save();
        $friendship->refresh();

        return response([
            'friendship' => $friendship,
        ]);
    }

    public function deleteFriendship(Request $request) {
        $validateData = $request->validate([
            'friendship_id' => 'required|integer|exists:friendships,id',
        ]);

        $user = Auth::user();

        $friendship = Friendship::find($validateData['friendship_id']);

        if (is_null($friendship)) {
            return response(['message' => 'Friendship not found.'], 404);
        }

        $friendship->delete();

        return response([
            'friendship' => $friendship
        ]);
    }

    public function acceptFriendship(Request $request) {
        $validateData = $request->validate([
            'friend_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $friendship = Friendship::where(function ($query) use ($validateData, $user) {
                $query->where('friend_id', $validateData['friend_id'])
                    ->orWhere('friend_id', $user->id);
            })
            ->where(function ($query) use ($validateData, $user) {
                $query->where('user_id', $validateData['friend_id'])
                    ->orWhere('user_id', $user->id);
            })
            ->where('relationship', config('enums.friendships.PENDING'))
            ->first();

        if (is_null($friendship)) {
            return response([
                'status' => 'This friendship is not pending.',
            ]);
        }

        $friendship->relationship = config('enums.friendships.ACCEPTED');
        $friendship->save();
        $friendship->refresh();

        return response([
            'friendship' => $friendship
        ]);
    }

    public function blockFriendship(Request $request): Response {
        // report and block a person
        $validateData = $request->validate([
            'friend_id' => 'required|integer',
        ]);

        $user = Auth::user();

        $friendship = Friendship::where(function ($query) use ($validateData, $user) {
                $query->where('user_id', $validateData['friend_id'])
                    ->orWhere('user_id', $user->id);
            })
            ->where(function ($query) use ($validateData, $user) {
                $query->where('friend_id', $validateData['friend_id'])
                    ->orWhere('friend_id', $user->id);
            })
            ->where('relationship', config('enums.friendships.BLOCKED'))
            ->first();

        if (! is_null($friendship)) {
            return response([
                'message' => 'You have already blocked this user.'
            ], 403);
        }

        $friendship = Friendship::where(function ($query) use ($validateData, $user) {
            $query->where('user_id', $validateData['friend_id'])
                ->orWhere('user_id', $user->id);
            })
            ->where(function ($query) use ($validateData, $user) {
                $query->where('friend_id', $validateData['friend_id'])
                    ->orWhere('friend_id', $user->id);
            })
            ->first();

        if (is_null($friendship)) {
            $friendship = new Friendship();
        }

        $friendship->user_id = $user->id;
        $friendship->friend_id = $validateData['friend_id'];
        $friendship->relationship = config('enums.friendships.BLOCKED');
        $friendship->save();
        $friendship->refresh();

        return response([
            'friendship' => $friendship
        ]);
    }

    public function inviteContact(Request $request) {
        $validatedData = $request->validate([
            'phoneNumber' => 'required|numeric',
            'displayName' => 'required|string',
        ]);

        $sms = app(SystemSms::class)->createInviteContactSms($validatedData['phoneNumber']);

        $user = Auth::user();

        SendInviteContactSms::dispatch(
            $validatedData['displayName'],
            $sms,
            $validatedData['phoneNumber'],
            $user
        )
            ->onQueue(config('rpx.sms.queue'));

        return response([
            'status' => 'ok',
        ]);
    }

    public function searchforUser(Request $request) {
        $validatedData = $request->validate([
            'search_string' => 'required|string',
        ]);

        $user = Auth::user();

        $matchingUserList = User::join('rpx_users', 'rpx_users.id', '=', 'users.id')
            ->where('users.id', '!=', $user->id)
            ->where(function ($query) use ($validatedData) {
                    $query->where('users.username', 'like', '%' . $validatedData['search_string'] . '%')
                        ->orWhereRaw("
                            CONCAT(rpx_users.first_name, ' ', rpx_users.last_name)
                            LIKE ?", ['%'.$validatedData['search_string'].'%']
                        );
            })
            ->with('rpxUser')
            ->get();

        return response([
            'matchingUserList' => $matchingUserList,
        ]);
    }

    public function searchforFriends(Request $request) {
        $validatedData = $request->validate([
            'search_string' => 'required|string',
        ]);

        $user = Auth::user();

        $matchingUserList = Friendship::select([
            'friendships.*',
            'users.username',
            'rpx_users.first_name',
            'rpx_users.last_name',
        ])->join('rpx_users', 'friendships.friend_id', '=', 'rpx_users.id')
            ->join('users', 'friendships.friend_id', '=', 'users.id')
            ->where(function ($query) use ($user){
                $query->where('friendships.friend_id', $user->id)
                        ->orWhere('friendships.user_id', $user->id);
            })
            ->where(function ($query) {
                $query->where('friendships.relationship', config('enums.friendships.ACCEPTED'))
                    ->orWhere('friendships.relationship', config('enums.friendships.PENDING'));
            })
            ->where(function ($query) use ($validatedData) {
                $query->where('users.username', 'like', '%' . $validatedData['search_string'] . '%')
                    ->orWhereRaw("
                            CONCAT(rpx_users.first_name, ' ', rpx_users.last_name)
                            LIKE ?", ['%'.$validatedData['search_string'].'%']
                    );
            })
            ->with('rpxProfile')
            ->with('friendRpxProfile')
            ->get();

        return response([
            'matchingUserList' => $matchingUserList,
        ]);
    }

    public function randomNearby(Request $request) {
        $validatedData = $request->validate([
            'loc_x' => 'required|max:90|min:-90|numeric',
            'loc_y' => 'required|max:180|min:-180|numeric',
        ]);

        $user = Auth::user();
        $loc_x = $validatedData['loc_x'];
        $loc_y = $validatedData['loc_y'];

        $randomNearby = UserLocation::join('rpx_users', 'rpx_users.id', '=', 'user_locations.id')
            ->where('rpx_users.user_type', 4)
            ->whereRaw("(
                (loc_x = $loc_x AND loc_y = $loc_y)
                OR (
                        ABS (
                                SQRT    (
                                            (POWER ( (loc_x - $loc_x), 2) ) +
                                            (POWER ( (loc_y - $loc_y), 2) )
                                        )
                            )
                        <= 0.5
                    )

                )")
            ->inRandomOrder()
            ->paginate(20);

        $matchingUserList = User::whereIn('id', $randomNearby->pluck('id'))
            ->where('id', '!=', $user->id)
            ->with('rpxUser')
            ->get();

        return response([
            'matchingUserList' => $matchingUserList,
        ]);
    }
}
