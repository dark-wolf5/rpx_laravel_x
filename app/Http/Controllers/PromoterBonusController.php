<?php

namespace App\Http\Controllers;

use App\Models\PromoterBonus;
use Illuminate\Http\Request;

class PromoterBonusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $bonus = new PromoterBonus();
        $bonus->business_id = '';
        $bonus->lp_amount = '';
        $bonus->redeemed = '';
        $bonus->user_id = '';
        $bonus->device_id = '';
        $bonus->device_ip = '';
        $bonus->expires_at = '';
        $bonus->day;
        $bonus->time_range_1;
        $bonus->time_range_2;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PromoterBonus  $promoterBonus
     * @return \Illuminate\Http\Response
     */
    public function show(PromoterBonus $promoterBonus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PromoterBonus  $promoterBonus
     * @return \Illuminate\Http\Response
     */
    public function edit(PromoterBonus $promoterBonus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PromoterBonus  $promoterBonus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PromoterBonus $promoterBonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PromoterBonus  $promoterBonus
     * @return \Illuminate\Http\Response
     */
    public function destroy(PromoterBonus $promoterBonus)
    {
        //
    }
}
