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
}
