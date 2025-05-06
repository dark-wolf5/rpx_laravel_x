<?php

namespace Database\Factories;

use App\Models\LoyaltyPointBalance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyPointBalanceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LoyaltyPointBalance::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $balance = rand(12000, 35000);
        $loyalty_point_dollar_percent_value = $this->faker->randomFloat(2, 1, 3);
        return [
            'balance'                            => $balance,
            'reset_balance'                      => $balance,
            'loyalty_point_dollar_percent_value' => $loyalty_point_dollar_percent_value,
            'end_of_month'                       => Carbon::now()->addMonth(),
        ];
    }
}
