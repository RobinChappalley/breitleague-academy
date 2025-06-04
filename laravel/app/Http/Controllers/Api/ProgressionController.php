<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProgressionResource;
use App\Models\Progression;

class ProgressionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $progression = Progression::get();
        if ($progression->count() > 0) {
            return ProgressionResource::collection($progression);
        } else {
            return response()->json(['message' => 'No progression'], 200);
        }
    }
}
