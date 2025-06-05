<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReward;
use App\Http\Resources\UserRewardResource;

class UserRewardController extends Controller
{
    /**
     * Affiche toutes les récompenses d'un utilisateur donné.
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'user_id is required'], 422);
        }

        $rewards = UserReward::with('reward')
            ->where('user_id', $userId)
            ->get();

        if ($rewards->count() > 0) {
            return UserRewardResource::collection($rewards);
        } else {
            return response()->json(['message' => 'No rewards for this user'], 200);
        }
    }

    /**
     * Attribue une nouvelle récompense à un utilisateur.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reward_id' => ['required', 'exists:rewards,id'],
            'is_favourite' => ['nullable', 'boolean'],
            'acquired_at' => ['nullable', 'date'],
        ]);

        $reward = UserReward::create([
            'user_id' => $validated['user_id'],
            'reward_id' => $validated['reward_id'],
            'is_favourite' => $validated['is_favourite'] ?? false,
            'acquired_at' => $validated['acquired_at'] ?? now(),
        ]);

        return new UserRewardResource($reward->load('reward'));
    }

    /**
     * Affiche une seule relation UserReward.
     */
    public function show(UserReward $userReward)
    {
        return new UserRewardResource($userReward->load('reward'));
    }

    /**
     * Mise à jour facultative (changer is_favourite par ex).
     */
    public function update(Request $request, UserReward $userReward)
    {
        $validated = $request->validate([
            'is_favourite' => ['nullable', 'boolean'],
            'acquired_at' => ['nullable', 'date'],
        ]);

        $userReward->update($validated);

        return new UserRewardResource($userReward->load('reward'));
    }

    /**
     * Supprime une récompense d'un utilisateur.
     */
    public function destroy(UserReward $userReward)
    {
        $userReward->delete();
        return response()->json(['message' => 'Reward removed from user'], 200);
    }
}
