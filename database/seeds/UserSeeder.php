<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        $personalAccountsToCreate = 10;
        User::factory()
            ->count($personalAccountsToCreate)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    //create personal accounts
                    'user_type' => 4,
                ];
            })
            ->create();

        /* Account #1 */
        User::factory()
            ->state([
                'email'    => 'maindedeux@gmail.com',
                'username' => 'agent000',
                'password' => Hash::make('HelloWorld33!'),
            ])
            ->count(1)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    'first_name' => 'Franco',
                    'last_name'  => 'Petitfour',
                    'user_type'  => 4,
                ];
            })
            ->create();

        /* Account #2 */
        User::factory()
            ->state([
                'email'    => 'agent001@rpx.com',
                'username' => 'agent001',
                'password' => Hash::make('HelloWorld33!'),
            ])
            ->count(1)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    'first_name' => 'Franco',
                    'last_name'  => 'Petitfour',
                    'user_type'  => 4,
                ];
            })
            ->create();

        $numberOfBusinessUsers = 90;
        $numberOfBusinessTypes = 3; // Events, Retail, Places to Eat.

        $businessesToCreate = $numberOfBusinessUsers / $numberOfBusinessTypes;

        User::factory()
            ->count($businessesToCreate)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    'user_type' => 1, //create places to eat business account
                ];
            })
            ->hasBusiness(1)
            ->create();

        User::factory()
            ->count($businessesToCreate)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    'user_type' => 2, // create retail stores business account
                ];
            })
            ->hasBusiness(1)
            ->create();

        User::factory()
            ->count($businessesToCreate)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    'user_type' => 3, //Create events business account
                ];
            })
            ->hasBusiness(1)
            ->create();

        $placeToEatCategories = config('rpx.my_business_categories_food');
        $indexOfBurgers = array_search('Burgers', $placeToEatCategories);
        var_dump($indexOfBurgers);
        $placeToEatCategory = $indexOfBurgers;

        // Let's create 20 Burger shops so that this category may always return full paged results.
        User::factory()
            ->count(20)
            ->hasRpxUser(1, function (array $attributes) {
                return [
                    'user_type' => 1,
                ];
            })
            ->hasBusiness(1, function (array $attributes) use ($placeToEatCategory) {
                return [
                    'categories' => $placeToEatCategory,
                ];
            })
            ->create();
    }
}
