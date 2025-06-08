<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Valider les champs
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // Tenter de connecter avec username + password
        if (!Auth::attempt($request->only('username', 'password'), true)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Regénérer la session (protection fixation de session)
        $request->session()->regenerate();

        // Récupérer l'utilisateur connecté
        $user = Auth::user();

        // Réponse OK avec username
        return response()->json([
            'message' => 'Logged in successfully',
            'username' => $user->username,
        ]);
    }
}
