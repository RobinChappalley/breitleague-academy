<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\Pos;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('pos', 'rewards')->get();

        if ($users->count() > 0) {
            return UserResource::collection($users);
        } else {
            return response()->json(['message' => 'No users'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //Récupérer tous les pos de chaque user, et l'ajouter dans l'objet json retourné
        $user->pos;
        //$test = User::find(1);
        //die($test->pos_id);

        //$test->pos;
        //return $user;
        $user->load('missions', 'rewards', 'battlesAsChallenger', 'battlesAsChallenged', 'progression');

        return new UserResource($user);
    }
    public function update(Request $request, User $user)
    {
        // (optionnel) s'assurer que l'utilisateur modifie son propre profil
        //if ($request->user()->id !== $user->id) {
        //    return response()->json(['message' => 'Unauthorized'], 403);
        //}

        $validated = $request->validate([
            'is_BS'    => ['nullable', 'boolean'],
        ]);

        // Si le mot de passe est présent, le hasher
        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return new UserResource($user);
    }


    public function store()
    {
        return response()->json([
            'message' => 'Not allowed on this endpoint.'
        ], 405);
    }

    public function destroy()
    {
        return response()->json([
            'message' => 'Not allowed on this endpoint.'
        ], 405);
    }
}
