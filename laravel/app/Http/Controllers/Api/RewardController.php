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
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Creating rewards is not allowed on this endpoint.'
        ], 405);
    }

    public function update(Request $request, Reward $reward)
    {
        return response()->json([
            'message' => 'Updating rewards is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy(Reward $reward)
    {
        return response()->json([
            'message' => 'Deleting rewards is not allowed on this endpoint.'
        ], 405);
    }
}
