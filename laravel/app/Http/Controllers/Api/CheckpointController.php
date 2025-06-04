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
        $checkpoint = Checkpoint::get();
        if ($checkpoint->count() > 0) {
            return CheckpointResource::collection($checkpoint);
        } else {
            return response()->json(['message' => 'No checkpoints'], 200);
        }
    }
    public function show(Checkpoint $checkpoint)
    {
        return new CheckpointResource($checkpoint);
    }
}
