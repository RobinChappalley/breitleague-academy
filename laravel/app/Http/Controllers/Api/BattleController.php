<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BattleResource;
use App\Models\Battle;

class BattleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $battle = Battle::get();
        if ($battle->count() > 0) {
            return BattleResource::collection($battle);
        } else {
            return response()->json(['message' => 'No battles'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Battle $battle)
    {
        return new BattleResource($battle);
    }
}
