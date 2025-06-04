<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\MissionResource;
use App\Models\Mission;

class MissionController extends Controller
{
    public function index()
    {
        $mission = Mission::get();
        if ($mission->count() > 0) {
            return MissionResource::collection($mission);
        } else {
            return response()->json(['message' => 'No missions'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Mission $mission)
    {
        return new MissionResource($mission);
    }
}
