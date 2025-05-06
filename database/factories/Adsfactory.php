<?php

namespace Database\Factories;

use App\Models\Ads;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ads::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->realText(25);

        $images = '';

        $adType = rand(0, 2);

        return [
            'uuid'          => Str::uuid(),
            'type'          => $adType,
            'name'          => $name,
            'images'        => $images,
            'images_mobile' => $images,
            'dollar_cost'   => 0,
            'clicks'        => 0,
            'views_from_start' => 0,
            'views'         => 0,
            'is_live'       => 1,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Ads $ad) {
            $rpxUserType = $ad->business->user->rpxUser->user_type;

            $adImage = $this->getAdsPhoto($rpxUserType);

            $ad->images = $adImage;
            $ad->images_mobile = $adImage;

            DB::transaction(function () use ($ad) {
                $ad->save();
            });
        });
    }

    public function getAdsPhoto($userType)
    {
        $businessPhotoFolder = 'assets/images/in-house/places-to-eat/';

        switch($userType)
        {
            case '1':
                $businessPhotoFolder = 'assets/images/in-house/places-to-eat/';
                break;
            case '2':
                $businessPhotoFolder = 'assets/images/in-house/shopping/';
                break;
            case '3':
                $businessPhotoFolder = 'assets/images/in-house/events/';
                break;
        }

        $businessPhoto = config('rpx.rpx_front_end_ip') . $businessPhotoFolder . rand(1, 25) . '.jpg';

        return $businessPhoto;
    }
}
