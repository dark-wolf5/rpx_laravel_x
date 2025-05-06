<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Ads;
use App\Models\Business;
use App\Models\Reward;
use App\Models\RpxUser;

class BusinessFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Business::class;

    private $restaurantNameList = [
        'Bistro Bazaar',
        'Bistro Captain',
        'Bistroporium',
        'Cuisine Street',
        'Cuisine Wave',
        'Deli Divine',
        'Deli Feast',
        'Eatery Hotspot',
        'Eateryworks',
        'Feast Lounge',
        'Feast Palace',
        'Grub Chef',
        'Grub lord',
        'Kitchen Sensation',
        'Kitchen Takeout',
        'Menu Feed',
        'Menu Gusto',
        'Munchies',
        'Munch Grill',
        'Munchtastic',
    ];

    private $restaurantNameListSec = [
        'Bistro',
        'Captain',
        'Palace',
        'Cuisine',
        'Wave',
        'Divine',
        'Feast',
        'Eatery',
        'Hotspot',
        'Lounge',
        'Castle',
        'Chef',
        'Grub',
        'Sensation',
        'Takeout',
        'Menu',
        'Gusto',
        'Munchies',
        'Munch',
        'Munchtastic',
    ];


    private function getName() {
        $name = $this->restaurantNameList[rand(0, count($this->restaurantNameList) - 1)];

        var_dump("Adding Business Name Exists: ", Business::where('name', $name)->exists());

        while (
            Business::where('name', $name)->exists()
        ) {
            $nameSec = $this->restaurantNameList[rand(0, count($this->restaurantNameList) - 1)];
            $name = $name .' '. $nameSec;
        }

        return $name;
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // We must add a random number to the business name so that we can bypass the unique constraint for slug and name
        // columns
        $name = $this->getName();
        $description = $this->faker->unique()->realText(150);

        // Remember that my_loc_y && my_loc_x can be negative... You might have to change this. I didn't have time to implement this correctly. Fuck StartUps :')

        $minX = floatval(config('rpx.my_loc_x')); //Avoid Miami's Ocean. This is too close to the water.
        $maxX = floatval(config('rpx.my_loc_x')) + .02;

        $minY = floatval(config('rpx.my_loc_y')) - .02;
        $maxY = floatval(config('rpx.my_loc_y')); //Avoid Miami's Ocean. This is too close to the water.

        $randomLocX = $this->faker->randomFloat(6, $minX, $maxX);
        $randomLocY = $this->faker->randomFloat(6, $minY, $maxY);

        return [
            'name'         => $name,
            'slug'         => Str::slug($name),
            'description'  => $description,
            'loc_x'        => $randomLocX,
            'loc_y'        => $randomLocY,
            'address'      => config('rpx.my_address'),
            'photo'        => '',
            'is_verified'  => true,
            'qr_code_link' => Str::uuid(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Business $business) {
            $rpxUser = RpxUser::select('user_type')
                ->where('id', '=', $business->id)
                ->get()[0];

            $userType = $rpxUser->user_type;
            $business->photo = $this->getBusinessPhoto($userType);

            if ($business->categories == null)
            {
                $categories = '';
                switch($userType)
                {
                    case '1':
                        $categories = array_rand(config('rpx.my_business_categories_food'));
                        break;
                    case '2':
                        $categories = array_rand(config('rpx.my_business_categories_shopping'));
                        break;
                    case '3':
                        $categories = array_rand(config('rpx.my_business_categories_events'));
                }
                $business->categories = json_encode($categories);
            }

            $business->save();

            $balance = rand(12000, 35000);
            $loyalty_point_dollar_percent_value = $this->faker->randomFloat(2, 1, 3);
            $business->loyaltyPointBalance()->create([
                'user_id'                            => $business->id,
                'business_id'                        => $business->id,
                'from_business'                      => 0,
                'balance'                            => $balance,
                'reset_balance'                      => $balance,
                'loyalty_point_dollar_percent_value' => $loyalty_point_dollar_percent_value,
                'end_of_month'                       => Carbon::now()->addMonth(),
            ]);

            $business = Business::find($business->id);

            Reward::factory()
                ->count(7)
                ->for($business)
                ->create();

            //Let's make 3 ads of each type for the business.
            Ads::factory()
                ->state(['type' => 0])
                ->count(1)
                ->for($business)
                ->create();

            Ads::factory()
                ->state(['type' => 1])
                ->count(1)
                ->for($business)
                ->create();

            Ads::factory()
                ->state(['type' => 2])
                ->count(1)
                ->for($business)
                ->create();

            // Award random reward number from business to users.
            $userList = User::where('username', '=', 'agent000')
                ->orWhere('username', '=', 'agent001')
                ->get();

            foreach ($userList as $user)
            {
                // Let's attach some Loyalty Point Ledger records from respective businesses.
                $balance = 0;
                $businessLp = $business->loyaltyPointBalance;

                while (1000 > $balance)
                {
                    $randCreatedAt = Carbon::now()->subDays(rand(1, 55))->subSeconds(rand(1, 55));

                    $totalSpent = rand(10, 250); // How much the user spent in dollars.
                    $dollarValue = $totalSpent * ($businessLp->loyalty_point_dollar_percent_value / 100); // The dollar value of the LP being awarded.
                    $randLp = ($dollarValue * 100) / $businessLp->loyalty_point_dollar_percent_value; // The LP awarded to user for spending at the business.

                    $ledgerRecord = $user->loyaltyPointLedger()->create([
                        'uuid'           => Str::uuid(),
                        'business_id'    => $business->id,
                        'loyalty_amount' => $randLp,
                        'created_at'     => $randCreatedAt,
                        'user_id'        => $user->id,
                        'type'           => 'points',
                    ]);

                    $business->loyaltyPointLedger()->create([
                        'uuid'           => Str::uuid(),
                        'business_id'    => $business->id,
                        'loyalty_amount' => -abs($randLp),
                        'created_at'     => $randCreatedAt,
                        'user_id'        => $user->id,
                        'type'           => 'points_expense',
                    ]);

                    $user->redeemed()->create([
                        'uuid'                               => Str::uuid(),
                        'business_id'                        => $business->id,
                        'amount'                             => $randLp,
                        'total_spent'                        => $totalSpent,
                        'dollar_value'                       => $dollarValue,
                        'loyalty_point_dollar_percent_value' => $businessLp->loyalty_point_dollar_percent_value,
                        'redeemed'                           => 1,
                        'ledger_record_id'                   => $ledgerRecord->id,
                        'created_at'                         => $randCreatedAt,
                    ]);
                    $balance += $randLp;
                }

                $randomRewardList = $business->rewards()->inRandomOrder()->limit(3)->get();
                for ($r = 0; $r < count($randomRewardList); $r++)
                {
                    $randCreatedAt = Carbon::now()->subDays(rand(1, 55))->subSeconds(rand(1, 55));
                    $randomReward = $randomRewardList[$r];

                    $dollarValue = ($businessLp->loyalty_point_dollar_percent_value / 100) * $randomReward->point_cost; // How much the user spent in dollars.

                    $rewardLedgerRecord = $user->loyaltyPointLedger()->create([
                        'uuid'           => Str::uuid(),
                        'business_id'    => $business->id,
                        'loyalty_amount' => -($randomReward->point_cost),
                        'created_at'     => $randCreatedAt,
                        'user_id'        => $user->id,
                        'type'           => 'reward_expense',
                    ]);

                    $user->redeemed()->create([
                        'uuid'                               => Str::uuid(),
                        'business_id'                        => $business->id,
                        'amount'                             => 0,
                        'total_spent'                        => $randomReward->point_cost,
                        'dollar_value'                       => $dollarValue,
                        'loyalty_point_dollar_percent_value' => $businessLp->loyalty_point_dollar_percent_value,
                        'redeemed'                           => 1,
                        'redeemer_id'                        => $user->id,
                        'reward_id'                          => $randomReward->id,
                        'created_at'                         => $randCreatedAt,
                        'ledger_record_id'                   => $rewardLedgerRecord->id,
                    ]);
                }
            }
        });
    }

    public function getBusinessPhoto($userType)
    {
        $businessPhotoFolder = match ($userType)
        {
            1 => 'assets/images/def/places-to-eat/',
            2 => 'assets/images/def/shopping/',
            3 => 'assets/images/def/events/',
        };

        return config('rpx.rpx_front_end_ip') . $businessPhotoFolder . rand(1, 25) . '.jpg';
    }
}
