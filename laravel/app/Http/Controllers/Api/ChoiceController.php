<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ChoiceResource;
use App\Models\Choice;

class ChoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $choice = Choice::get();
        if ($choice->count() > 0) {
            return ChoiceResource::collection($choice);
        } else {
            return response()->json(['message' => 'No choices'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Choice $choice)
    {
        return new ChoiceResource($choice);
    }
    public function store()
    {
        return response()->json([
            'message' => 'Creating choices is not allowed on this endpoint.'
        ], 405);
    }

    public function update(Request $request, Choice $choice)
    {
        return response()->json([
            'message' => 'Updating choices is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy()
    {
        return response()->json([
            'message' => 'Deleting choices is not allowed on this endpoint.'
        ], 405);
    }
}
