<?php

namespace Database\Factories;

use App\Models\UserLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserLocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserLocation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'loc_x'      => config('rpx.my_loc_x'),
            'loc_y'      => config('rpx.my_loc_y'),
            'ip_address' => '0',
        ];
    }
}
