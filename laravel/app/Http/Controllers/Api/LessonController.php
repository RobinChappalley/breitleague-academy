<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Theory;

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

    /**
     * Récupérer toutes les théories d'une leçon avec leurs questions
     */
    public function getTheories($lessonId)
    {
        try {
            $theories = Theory::where('lesson_id', $lessonId)
                ->with(['questions' => function($query) {
                    $query->with('choices')->orderBy('position_order');
                }])
                ->orderBy('position_order')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $theories
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur getTheories: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des théories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
