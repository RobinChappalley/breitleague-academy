<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserMission;
use App\Http\Resources\UserMissionResource;

class UserMissionController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json(['message' => 'user_id is required'], 422);
        }

        $missions = UserMission::with('mission')
            ->where('user_id', $userId)
            ->get();

        if ($missions->count() > 0) {
            return UserMissionResource::collection($missions);
        } else {
            return response()->json(['message' => 'No missions found for this user'], 200);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'mission_id' => ['required', 'exists:missions,id'],
            'is_completed' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $mission = UserMission::create([
            'user_id' => $validated['user_id'],
            'mission_id' => $validated['mission_id'],
            'is_completed' => $validated['is_completed'] ?? false,
            'start_date' => $validated['start_date'] ?? now(),
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return new UserMissionResource($mission->load('mission'));
    }

    public function show(UserMission $userMission)
    {
        return new UserMissionResource($userMission->load('mission'));
    }

    public function update(Request $request, UserMission $userMission)
    {
        $validated = $request->validate([
            'is_completed' => ['nullable', 'boolean'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $userMission->update($validated);

        return new UserMissionResource($userMission->load('mission'));
    }

    public function destroy(UserMission $userMission)
    {
        $userMission->delete();
        return response()->json(['message' => 'User mission removed'], 200);
    }
}
