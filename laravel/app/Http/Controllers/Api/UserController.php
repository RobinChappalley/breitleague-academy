<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::get();
        if ($user->count() > 0) {
            return UserResource::collection($user);
        } else {
            return response()->json(['message' => 'No users'], 200);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }
    public function update(Request $request, User $user)
    {
        // (optionnel) s'assurer que l'utilisateur modifie son propre profil
        //if ($request->user()->id !== $user->id) {
        //    return response()->json(['message' => 'Unauthorized'], 403);
        //}

        $validated = $request->validate([
            'avatar'   => ['nullable', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'email'    => ['nullable', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
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
