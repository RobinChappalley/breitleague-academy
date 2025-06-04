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
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Creating missions is not allowed on this endpoint.'
        ], 405);
    }

    public function update(Request $request, Mission $mission)
    {
        return response()->json([
            'message' => 'Updating missions is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy(Mission $mission)
    {
        return response()->json([
            'message' => 'Deleting missions is not allowed on this endpoint.'
        ], 405);
    }
}
