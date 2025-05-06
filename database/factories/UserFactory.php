<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\LoyaltyPointBalanceAggregator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid'     => Str::uuid(),
            'username' => $this->faker->unique()->username,
            'email'    => $this->faker->unique()->safeEmail,
            'password' => Hash::make('HelloWorld33!'),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (User $user) {
            if (!$user->business)
            {
                if ($user->username === 'agent000' || $user->username === 'agent001')
                {
                    $toCreate = 30;
                }
                else
                {
                    $toCreate = 10;
                }
                $aggregateBalance = 0;
                // Let's attach some Loyalty Point Balances from different stores.
                for ($i = 0; $i < $toCreate; $i++)
                {
                    // We seed DB with 90 business accounts;
                    $randBusinessId = rand(12, 122);

                    while (in_array($randBusinessId, $user->loyaltyPointBalance()->pluck('from_business')->toArray()))
                    {
                        $randBusinessId = rand(12, 122);
                    }

                    $balance = rand(1000, 2000);
                    $user->loyaltyPointBalance()->create([
                        'balance'       => $balance,
                        'from_business' => $randBusinessId,
                        'business_id'   => 0,
                    ]);
                    $aggregateBalance += $balance;
                }
                $lpAggregator = new LoyaltyPointBalanceAggregator();
                $lpAggregator->id = $user->id;
                $lpAggregator->balance = $aggregateBalance;
                $lpAggregator->save();
            }
        });
    }
}
