<?php

namespace App\Http\Controllers;

use App\Models\MeetUpInvitation;
use Auth;
use App\Models\MeetUp;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MeetUpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(): Response
    {
        $user = Auth::user();

        $meetUpListing = MeetUpInvitation::select(
            'mu.time',
            'meet_up_invitations.meet_up_id'
        )
            ->join('meet_ups as mu', function ($qry) use ($user) {
                $qry->on('mu.id', '=', 'meet_up_invitations.meet_up_id');
            })
            ->where('meet_up_invitations.user_id', $user->id)
            ->orWhere('meet_up_invitations.friend_id', $user->id)
            ->with('meetUp', function ($qry) {
                $qry->with('invitationList', function ($qry) {
                    $qry->with('userProfile');
                    $qry->with('friendProfile');
                })->with('owner', function($qry) {
                    $qry->with('rpxUser');
                })
                    ->with('business');
            })
            ->groupBy('meet_up_invitations.meet_up_id')
            ->orderBy('mu.time', 'asc')
            ->paginate(20);

        return response([
            'meetUpListing' => $meetUpListing
        ]);
    }

    public function createMeetUp(Request $request, MeetUp $meetUp): Response {
        return $meetUp->createMeetUp($request);
    }


    public function editMeetUp(MeetUp $meetUp, Request $request): Response {
        return $meetUp->editMeetUp($request, $meetUp);
    }

    public function acceptInvitation(Request $request, MeetUpInvitation $mui): Response {
        return $mui->acceptInvitation($request);
    }

    public function destroy(Request $request, MeetUp $meetUp): int {
        $validatedData = $request->validate([
            'meet_up_id' => 'required|integer|exists:meet_ups,id',
        ]);

        // Let's delete the MeetUpInvitations... There's something def. wrong with the MeetUp -> invitationList()
        // relationship because cascades and the MeetUpObserver deleted method don't work.
        MeetUpInvitation::where('meet_up_id', $validatedData['meet_up_id'])->delete();

        return $meetUp->destroy($validatedData['meet_up_id']);
    }

    public function show(MeetUp $meetUp): Response {
        return $meetUp->show($meetUp);
    }

    public function showMui(Request $request, MeetUpInvitation $mui): Response {
        return $mui->showMui();
    }
}
