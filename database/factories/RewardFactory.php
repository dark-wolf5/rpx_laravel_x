<?php

namespace Database\Factories;

use App\Models\Reward;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class RewardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reward::class;

    public $rewardImageList = [
        'assets/images/def/rewards/sample_image_0.jpg',
        'assets/images/def/rewards/sample_image_1.jpg',
        'assets/images/def/rewards/sample_image_2.jpg',
        'assets/images/def/rewards/sample_image_3.jpg',
        'assets/images/def/rewards/sample_image_4.jpg',
    ];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->realText(50);
        $description = $this->faker->unique()->realText(150);

        $images = config('rpx.rpx_front_end_ip') . $this->rewardImageList[rand(0, 4)];

        $point_cost = rand(450, 1200);
        $monthly_times_available = rand(1, 200);
        $times_claimed_this_month = rand(1, 200);

        $type = rand(0, 1);

        return [
            'uuid'                     => Str::uuid(),
            'type'                     => $type,
            'name'                     => $name,
            'description'              => $description,
            'images'                   => $images,
            'point_cost'               => $point_cost,
            'monthly_times_available'  => $monthly_times_available,
            'times_claimed_this_month' => $times_claimed_this_month,
        ];
    }
}
