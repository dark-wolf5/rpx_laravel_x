<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessExposure;
use Illuminate\Database\Seeder;

class AddBusinessExposureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $businessList = Business::all();

        foreach ($businessList as $business) {
            $be = BusinessExposure::where('business_id', $business->id)->first();

            if( is_null($be) ) {
                $be = new BusinessExposure();
                $be->business_id = $business->id;
                $be->total_exposure = 0;
                $be->save();
            }
        }
    }
}
