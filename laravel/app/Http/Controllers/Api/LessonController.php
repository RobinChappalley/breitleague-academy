<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\LessonResource;
use App\Models\Lesson;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lesson = Lesson::get();
        if ($lesson->count() > 0) {
            return LessonResource::collection($lesson);
        } else {
            return response()->json(['message' => 'No lessons'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson)
    {
        return new LessonResource($lesson);
    }
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Creating lessons is not allowed on this endpoint.'
        ], 405);
    }

    public function update(Request $request, Lesson $lesson)
    {
        return response()->json([
            'message' => 'Updating lessons is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy(Lesson $lesson)
    {
        return response()->json([
            'message' => 'Deleting lessons is not allowed on this endpoint.'
        ], 405);
    }
}
