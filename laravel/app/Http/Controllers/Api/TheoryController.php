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
        $theory = Theory::get();
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
        return new TheoryResource($theory);
    }
}
