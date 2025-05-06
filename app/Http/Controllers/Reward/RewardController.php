<?php

namespace App\Http\Controllers\Reward;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function create(Reward $reward, Request $request)
    {
        return $reward->create($request);
    }

    public function claim(Reward $reward, Request $request)
    {
        return $reward->claim($request);
    }

    public function index(Reward $reward, Request $request)
    {
        return $reward->index($request);
    }

    public function update(Reward $reward, Request $request)
    {
        return $reward->updateReward($request);
    }

    public function delete(Reward $reward, Request $request)
    {
        return $reward->deleteMe($request);
    }

    public function uploadMedia(Reward $reward, Request $request)
    {
        return $reward->uploadMedia($request);
    }
}
