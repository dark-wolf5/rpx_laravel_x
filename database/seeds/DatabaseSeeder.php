<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(SpotbieAdsSeeder::class);
        $this->call(ChangeBusinessNamesSeeder::class);
        $this->call(AddBusinessExposureSeeder::class);
    }
}
