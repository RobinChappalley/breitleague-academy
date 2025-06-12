<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BattleResource;
use App\Models\Battle;

class BattleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $battles = Battle::with(['challenger.pos', 'challenged.pos'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($battles->count() > 0) {
            return BattleResource::collection($battles);
        } else {
            return response()->json([
                'message' => 'No battles found',
                'data' => []
            ], 200);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Battle $battle)
    {
        $battle->load(['challenger.pos', 'challenged.pos']);
        return new BattleResource($battle);
    }

    public function store(Request $request)
    {
        try {
            // VALIDATION COMPLÈTE pour accepter les données de bataille
            $validated = $request->validate([
                'challenger_id' => ['required', 'integer', 'exists:users,id'],
                'challenged_id' => ['required', 'integer', 'exists:users,id', 'different:challenger_id'],
                'has_challenger_won' => ['nullable', 'boolean'],
                'has_challenger_accepted' => ['boolean'],
                'has_challenged_accepted' => ['boolean'],
                'questions_id' => ['nullable', 'array'],
                'questions_id.*' => ['integer'],
                'challenger_summary' => ['nullable', 'array'], // Accepter les données complètes
                'challenged_summary' => ['nullable', 'array'], // Accepter les données complètes
            ]);

            $validated['challenger_init_date'] = now();

            $battle = Battle::create($validated);
            $battle->load(['challenger.pos', 'challenged.pos']);

            return response()->json([
                'message' => 'Battle created successfully',
                'data' => new BattleResource($battle)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating battle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // NOUVELLE MÉTHODE : Accepter/refuser une invitation
    public function updateStatus(Request $request, Battle $battle)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:accept,decline'],
            'user_id' => ['required', 'exists:users,id']
        ]);

        $userId = $validated['user_id'];
        $action = $validated['action'];

        // Vérifier que l'utilisateur est impliqué dans cette bataille
        if ($userId !== $battle->challenger_id && $userId !== $battle->challenged_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($action === 'accept') {
            if ($userId === $battle->challenged_id) {
                $battle->has_challenged_accepted = true;
            } elseif ($userId === $battle->challenger_id) {
                $battle->has_challenger_accepted = true;
            }
            $battle->save();
            
            return response()->json(['message' => 'Battle accepted'], 200);
        } else {
            // Decline = supprimer la bataille
            $battle->delete();
            return response()->json(['message' => 'Battle declined'], 200);
        }
    }

    // Garder tes autres méthodes (update, destroy) comme elles sont
    public function update(Request $request, Battle $battle)
    {
        try {
            $validated = $request->validate([
                'has_challenger_won' => ['nullable', 'boolean'],
                'challenger_summary' => ['nullable', 'array'],
                'challenged_summary' => ['nullable', 'array'],
            ]);

            // Mettre à jour la bataille
            $battle->update($validated);
            
            // NOUVEAU : Si les deux summaries sont maintenant présents, déterminer le gagnant
            if ($battle->challenger_summary && $battle->challenged_summary) {
                $this->determineWinner($battle);
            }
            
            $battle->load(['challenger.pos', 'challenged.pos']);

            return response()->json([
                'message' => 'Battle updated successfully',
                'data' => new BattleResource($battle)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating battle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // NOUVELLE MÉTHODE : Déterminer automatiquement le gagnant
    private function determineWinner(Battle $battle)
    {
        try {
            $challengerPoints = $battle->challenger_summary['totalPoints'] ?? 0;
            $challengedPoints = $battle->challenged_summary['totalPoints'] ?? 0;
            
            $challengerWon = $challengerPoints > $challengedPoints;
            
            $battle->update([
                'has_challenger_won' => $challengerWon
            ]);
            
            \Log::info('Winner determined automatically:', [
                'battle_id' => $battle->id,
                'challenger_points' => $challengerPoints,
                'challenged_points' => $challengedPoints,
                'challenger_won' => $challengerWon
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error determining winner:', [
                'battle_id' => $battle->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Battle $battle)
    {
        $requestingUserId = request()->input('user_id');

        if (
            $requestingUserId != $battle->challenger_id &&
            $requestingUserId != $battle->challenged_id
        ) {
            return response()->json([
                'message' => 'You are not part of this match.'
            ], 403);
        }

        if ($requestingUserId == $battle->challenger_id && !$battle->has_challenger_accepted && !$battle->has_challenged_accepted) {
            $battle->delete();
            return response()->json(['message' => 'Match cancelled by challenger.'], 200);
        }

        if ($requestingUserId == $battle->challenged_id && !$battle->has_challenged_accepted) {
            $battle->delete();
            return response()->json(['message' => 'Match refused by challenged user.'], 200);
        }

        if (
            !$battle->has_challenger_accepted &&
            !$battle->has_challenged_accepted &&
            $battle->challenger_init_date &&
            now()->diffInHours($battle->challenger_init_date) >= 24
        ) {
            $battle->delete();
            return response()->json(['message' => 'Match invitation expired.'], 200);
        }

        return response()->json([
            'message' => 'Match cannot be cancelled anymore.'
        ], 403);
    }
}
