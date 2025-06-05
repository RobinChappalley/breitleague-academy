<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\TheoryResource;
use App\Models\Theory;

class TheoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $theory = Theory::with('questions.choices')->get();
        if ($theory->count() > 0) {
            return TheoryResource::collection($theory);
        } else {
            return response()->json(['message' => 'No theories'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Theory $theory)
    {
        $theory->load('questions.choices');
        return new TheoryResource($theory);
    }
    public function store()
    {
        return response()->json([
            'message' => 'Creating theories is not allowed on this endpoint.'
        ], 405);
    }

    public function update()
    {
        return response()->json([
            'message' => 'Updating theories is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy()
    {
        return response()->json([
            'message' => 'Deleting theories is not allowed on this endpoint.'
        ], 405);
    }
}
