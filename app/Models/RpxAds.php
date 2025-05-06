<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RpxAds extends Model
{
    use HasFactory;

    public static function getRpxAd($adType)
    {
        $adId = 0;
        $query = null;

        switch($adType)
        {
            case 0:
            case 2:
                $adId = rand(1, 2);
                $query = RpxAds::select('*')
                ->where('id', $adId)
                ->first();
                break;
            case 1:
                $adId = rand(3, 5);
                $query = RpxAds::select('*')
                ->where('id', $adId)
                ->first();
                break;
        }

        return $query;
    }
}
