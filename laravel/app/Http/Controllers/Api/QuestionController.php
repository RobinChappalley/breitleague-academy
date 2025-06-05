<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\QuestionResource;
use App\Models\Question;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $question = Question::get();
        if ($question->count() > 0) {
            return QuestionResource::collection($question);
        } else {
            return response()->json(['message' => 'No questions'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Question $question)
    {
        return new QuestionResource($question);
    }
    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Creating questions is not allowed on this endpoint.'
        ], 405);
    }

    public function update(Request $request, Question $question)
    {
        return response()->json([
            'message' => 'Updating questions is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy(Question $question)
    {
        return response()->json([
            'message' => 'Deleting questions is not allowed on this endpoint.'
        ], 405);
    }
}
