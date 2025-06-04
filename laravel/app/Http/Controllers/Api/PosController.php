<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Pos;
use App\Http\Resources\PosResource;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pos = Pos::get();
        if ($pos->count() > 0) {
            return PosResource::collection($pos);
        } else {
            return response()->json(['message' => 'No point of sales available'], 200);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Pos $pos)
    {
        return new PosResource($pos);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
