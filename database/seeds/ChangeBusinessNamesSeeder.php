<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChangeBusinessNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $randomBusinessList = [
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

        $businessList = DB::table('business')->where('id', '<', '109')->get();

        foreach ($businessList as $business)
        {
            $randomName = $randomBusinessList[rand(0, count($randomBusinessList) - 1)] . random_int(0, 359);

            DB::table('business')
            ->where('id', $business->id)
            ->update([
                'name' => $randomName,
                'slug' => Str::slug($randomName),
            ]);
        }
    }
}
