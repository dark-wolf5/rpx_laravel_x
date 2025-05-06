<?php

namespace App\Models;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MyFavorites extends Model
{
    use HasFactory, SoftDeletes;

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function getFavorites(Request $request)
    {
        $user = Auth::user();

        $favorites = $user
        ->myFavorites()
        ->select('id', 'third_party_id', 'name', 'type_of_info_object_category', 'loc_x', 'loc_y', 'created_at')
        ->paginate(10);

        return $favorites;
    }

    public function saveFavorite(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'third_party_id'               => ['nullable', 'string'],
            'name'                         => ['required', 'string'],
            'loc_x'                        => ['required', 'numeric'],
            'loc_y'                        => ['required', 'numeric'],
            'type_of_info_object_category' => ['required', 'string'],
        ]);

        $newFavorite = new MyFavorites;

        $newFavorite->third_party_id = $validatedData['third_party_id'];
        $newFavorite->name = $validatedData['name'];
        $newFavorite->type_of_info_object_category = $validatedData['type_of_info_object_category'];
        $newFavorite->loc_x = $validatedData['loc_x'];
        $newFavorite->loc_y = $validatedData['loc_y'];

        $user->myFavorites()->save($newFavorite);

        return true;
    }

    public function removeFavorite(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'id' => ['string'],
        ]);

        $user->myFavorites()
        ->where('third_party_id', $validatedData['third_party_id'])
        ->delete();

        return true;
    }

    public function isAFavorite(Request $request)
    {
        $isAFavorite = false;

        $validatedData = $request->validate([
            'obj_type'       => ['string'],
            'third_party_id' => ['string'],
        ]);

        $user = Auth::user();

        $favorite = $user
        ->myFavorites()
        ->where('third_party_id', $validatedData['third_party_id'])
        ->select('id')
        ->first();

        if ($favorite !== null)
        {
            $isAFavorite = true;
        }

        return $favorite;
    }
}
