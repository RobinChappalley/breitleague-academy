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
    public function show(Progression $progression)
    {
        return new ProgressionResource($progression);
    }

    /**
     * Crée une nouvelle progression (si elle n'existe pas déjà).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:progressions,user_id'],
            'last_lesson_id' => ['nullable', 'exists:lessons,id'],
            'last_checkpoint_id' => ['nullable', 'exists:checkpoints,id'],
            'idofquestionscorrectlyanswered' => ['nullable', 'array'],
        ]);

        $progression = Progression::create([
            'user_id' => $validated['user_id'],
            'last_lesson_id' => $validated['last_lesson_id'] ?? null,
            'last_checkpoint_id' => $validated['last_checkpoint_id'] ?? null,
            'idofquestionscorrectlyanswered' => $validated['idofquestionscorrectlyanswered'] ?? [],
        ]);

        return new ProgressionResource($progression);
    }

    /**
     * Met à jour une progression.
     */
    public function update(Request $request, Progression $progression)
    {
        $validated = $request->validate([
            'last_lesson_id' => ['nullable', 'exists:lessons,id'],
            'last_checkpoint_id' => ['nullable', 'exists:checkpoints,id'],
            'idofquestionscorrectlyanswered' => ['nullable', 'array'],
        ]);

        $progression->update([
            'last_lesson_id' => $validated['last_lesson_id'] ?? $progression->last_lesson_id,
            'last_checkpoint_id' => $validated['last_checkpoint_id'] ?? $progression->last_checkpoint_id,
            'idofquestionscorrectlyanswered' => $validated['idofquestionscorrectlyanswered'] ?? $progression->idofquestionscorrectlyanswered,
        ]);

        return new ProgressionResource($progression);
    }

    /**
     * Supprimer une progression n'est pas autorisé.
     */
    public function destroy(Progression $progression)
    {
        return response()->json([
            'message' => 'Deleting progressions is not allowed.'
        ], 405);
    }
}
