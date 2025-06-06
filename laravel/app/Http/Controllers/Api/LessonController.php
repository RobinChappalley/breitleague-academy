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
        $lesson = Lesson::with('theories.questions.choices')->get();
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
        $lesson->load('theories.questions.choices');
        return new LessonResource($lesson);
    }
    public function store()
    {
        return response()->json([
            'message' => 'Creating lessons is not allowed on this endpoint.'
        ], 405);
    }

    public function update()
    {
        return response()->json([
            'message' => 'Updating lessons is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy()
    {
        return response()->json([
            'message' => 'Deleting lessons is not allowed on this endpoint.'
        ], 405);
    }
}
