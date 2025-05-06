<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Auth;

class FriendshipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = Auth::user();
        $friendList = Friendship::select('id', 'user_id', 'friend_id', 'relationship')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('friend_id', $user->id);
            })
            ->with('userProfile', function ($query) {
                $query->with('rpxUser');
            })
            ->with('friendProfile', function ($query) {
                $query->with('rpxUser');
            })
            ->paginate(20);

        return response([
            'friendList' => $friendList,
        ]);
    }

    public function requestFriendship(Friendship $friendship, Request $request): Response {
        return $friendship->requestFriendship($request);
    }

    public function deleteFriendship(Friendship $friendship, Request $request): Response {
        return $friendship->deleteFriendship($request);
    }

    public function acceptFriendship(Friendship $friendship, Request $request): Response {
        return $friendship->acceptFriendship($request);
    }

    public function blockFriendship(Friendship $friendship, Request $request): Response {
        return $friendship->blockFriendship($request);
    }

    public function randomNearby(Friendship $friendship, Request $request) {
        return $friendship->randomNearby($request);
    }

    public function searchForUser(Friendship $friendship, Request $request) {
        return $friendship->searchforUser($request);
    }

    public function searchForFriends(Friendship $friendship, Request $request) {
        return $friendship->searchForFriends($request);
    }

    public function inviteContact(Friendship $friendship, Request $request) {
        return $friendship->inviteContact($request);
    }
}
