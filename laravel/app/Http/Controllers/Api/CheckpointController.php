<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CheckpointResource;
use App\Models\Checkpoint;
use Laravel\Sanctum\Http\Middleware\CheckScopes;

class CheckpointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $checkpoint = Checkpoint::with('questions')->get();
        if ($checkpoint->count() > 0) {
            return CheckpointResource::collection($checkpoint);
        } else {
            return response()->json(['message' => 'No checkpoints'], 200);
        }
    }
    public function show(Checkpoint $checkpoint)
    {
        $checkpoint->questions;
        return new CheckpointResource($checkpoint);
    }
    public function store()
    {
        return response()->json([
            'message' => 'Creating checkpoints is not allowed on this endpoint.'
        ], 405);
    }

    public function update()
    {
        return response()->json([
            'message' => 'Updating checkpoints is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy()
    {
        return response()->json([
            'message' => 'Deleting checkpoints is not allowed on this endpoint.'
        ], 405);
    }
}
