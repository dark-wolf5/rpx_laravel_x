<?php

namespace App\Models;

use App\Services\SurroundingsApi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Bus;

class MeetUpInvitation extends Model
{
    use HasFactory;

    protected $table = 'meet_up_invitations';
    protected $fillable = [];

    public function friendProfile() {
        return $this->hasOne('App\Models\RpxUser', 'id', 'friend_id');
    }

    public function userProfile() {
        return $this->hasOne('App\Models\User', 'id', 'friend_id');
    }

    public function meetUp() {
        return $this->belongsTo('App\Models\MeetUp', 'meet_up_id', 'id');
    }

    public function ownerAccount() {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function acceptInvitation(Request $request) {
        $this->going = 1;
        $this->save();

        return response(['message' => 'Accepted']);
    }

    public function showMui(): Response {
        if (is_null($this->meetUp)) {
            return response(['message' => 'Not found.'], 404);
        }

        $userProfileList = array();
        foreach ($this->meetUp->invitationList as $invitation) {
            $profile = User::find(intval($invitation->friend_id));

            // The friend_id field in the mui can also be a string so we can't use the foreign key relationship
            if (!is_null($profile) && !empty($profile)) {
                $profile =  User::where('id', intval($invitation->friend_id))->with('rpxUser')->get()[0];
                array_push($userProfileList, $profile);
            }
        }

        $contactListProfiles = array();
        $contactList = json_decode($this->meetUp->contact_list);
        foreach ($contactList as $invitation) {
            array_push($contactListProfiles, json_decode($invitation));
        }

        $ownerProfile = User::find(intval($this->meetUp->user_id));
        if (!is_null($ownerProfile)) {
            $ownerProfile = User::where('id', intval($this->meetUp->user_id))->with('rpxUser')->get()[0];
        }

        $meetUpLocation = null;
        if (! is_null($this->meetUp->business_id_sb)) {
            $meetUpLocation = Business::find($this->meetUp->business_id_sb);
        } else if (! is_null($this->meetUp->business_id)) {
            $businessId = $this->meetUp->business_id;
            $yelpConfigUrl = "https://api.yelp.com/v3/businesses/$businessId";
            $meetUpLocation = app(SurroundingsApi::class)->pullInfoObject($yelpConfigUrl);
        }

        $r = [
            'meetUp' => $this->meetUp,
            'invitationList' => $this->meetUp->invitationList,
            'userProfileList' => $userProfileList,
            'contactListProfiles' => $contactListProfiles,
            'ownerProfile' => $ownerProfile,
            'meetUpLocation' => $meetUpLocation
        ];

        return response($r);
    }
}
