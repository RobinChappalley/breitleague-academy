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
        $battle = Battle::get();
        if ($battle->count() > 0) {
            return BattleResource::collection($battle);
        } else {
            return response()->json(['message' => 'No battles'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Battle $battle)
    {
        return new BattleResource($battle);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'challenger_id' => ['required', 'exists:users,id'],
            'challenged_id' => ['required', 'exists:users,id', 'different:challenger_id'],
            'challenger_init_date' => ['nullable', 'date'], // sinon on met now()
            'questions_id' => ['nullable', 'array'],
        ]);

        $validated['challenger_init_date'] = $validated['challenger_init_date'] ?? now();

        $battle = Battle::create($validated);

        return new BattleResource($battle);
    }

    public function update(Request $request, Battle $battle)
    {
        return response()->json([
            'message' => 'Updating battles is not allowed on this endpoint.'
        ], 405);
    }

    public function destroy(Battle $battle)
    {
        $requestingUserId = request()->input('user_id');

        // Vérifie que le user est bien impliqué dans le match
        if (
            $requestingUserId != $battle->challenger_id &&
            $requestingUserId != $battle->challenged_id
        ) {
            return response()->json([
                'message' => 'You are not part of this match.'
            ], 403);
        }

        // Si la requête vient du challenger, il peut annuler sa propre demande
        if ($requestingUserId == $battle->challenger_id && !$battle->has_challenger_accepted && !$battle->has_challenged_accepted) {
            $battle->delete();
            return response()->json(['message' => 'Match cancelled by challenger.'], 200);
        }

        // Si la requête vient du challenged, il peut refuser
        if ($requestingUserId == $battle->challenged_id && !$battle->has_challenged_accepted) {
            $battle->delete();
            return response()->json(['message' => 'Match refused by challenged user.'], 200);
        }

        // Vérifie si le match a expiré (invitation > 24h)
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
