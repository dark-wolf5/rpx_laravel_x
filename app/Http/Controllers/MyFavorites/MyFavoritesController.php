<?php

namespace App\Http\Controllers\MyFavorites;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MyFavorites;

class MyFavoritesController extends Controller
{
    public function saveFavorite(MyFavorites $myFavorites, Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $myFavorites->saveFavorite($request),
        ];

        return response($response);
    }

    public function removeFavorite(MyFavorites $myFavorites, Request $request)
    {
        $response = [
            'success' => true,
            'data'    => $myFavorites->removeFavorite($request),
        ];

        return response($response);
    }

    public function getFavorites(MyFavorites $myFavorites, Request $request)
    {
        $response = [
            'success'        => true,
            'favorite_items' => $myFavorites->getFavorites($request),
        ];

        return response($response);
    }

    public function isAFavorite(MyFavorites $myFavorites, Request $request)
    {
        $response = [
            'success'       => true,
            'is_a_favorite' => $myFavorites->isAFavorite($request),
        ];

        return response($response);
    }
}
