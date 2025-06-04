<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\RewardResource;
use App\Models\Reward;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reward = Reward::get();
        if ($reward->count() > 0) {
            return rewardResource::collection($reward);
        } else {
            return response()->json(['message' => 'No rewards'], 200);
        }
    }
    /**
     * Display the specified resource.
     */

    public function show(Reward $reward)
    {
        return new RewardResource($reward);
    }
}
