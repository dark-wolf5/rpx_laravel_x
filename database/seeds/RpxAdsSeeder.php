<?php

namespace Database\Seeders;

use App\Models\RpxAds;
use Illuminate\Database\Seeder;

class RpxAdsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        RpxAds::factory()
        ->state([
            'type' => 0,
        ])
        ->count(1)
        ->create();

        RpxAds::factory()
        ->state([
            'type' => 1,
        ])
        ->count(1)
        ->create();

        RpxAds::factory()
        ->state([
            'type' => 2,
        ])
        ->count(1)
        ->create();

        RpxAds::factory()
        ->state([
            'type' => 3,
        ])
        ->count(1)
        ->create();

        RpxAds::factory()
        ->state([
            'type' => 4,
        ])
        ->count(1)
        ->create();
    }
}
