<?php

namespace App\Http\Controllers;

use App\Models\SharedExperience;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SharedExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        $validatedData = $request->validate([
            'business_id' => 'nullable|exists:business,id',
        ]);

        $user = Auth::user();
        $sharedList = SharedExperience::where('business_id', $validatedData['business_id'])->paginate(20)->get();
        $mySharedList = SharedExperience::where('business_id', $validatedData['business_id'])
            ->where('user_id', $user->id)
            ->paginate(20)->get();

        return response([
            'shared_list' => $sharedList,
            'my_shared_list' => $mySharedList,
        ]);
    }

    public function store(Request $request): Response {
        $validatedData = $request->validate([
            'business_id' => 'nullable|exists:business,id',
            'image' => 'required|image|max:25000',
            'comment' => 'required|min:5,max:3600'
        ]);

        $sharedExperience = new SharedExperience();
        $sharedExperience->image = $validatedData['image'];
        $sharedExperience->business_id = $validatedData['business_id'];
        $sharedExperience->comment = $validatedData['comment'];

        $sharedExperience->save();
        $sharedExperience->refresh();

        return response([
            'sharedExperience' => $sharedExperience
        ]);
    }

    public function destroy(Request $request, SharedExperience $sharedExperience): int {
        $validatedDate = $request->validate([
            'shared_experience_id' => 'required|integer|exists:meet_ups,id',
        ]);

        return $sharedExperience->destroy($validatedDate['shared_experience_id']);
    }

    public function show(SharedExperience $sharedExperience) {
        return response([
            'sharedExperience' => $sharedExperience,
        ]);
    }
}
