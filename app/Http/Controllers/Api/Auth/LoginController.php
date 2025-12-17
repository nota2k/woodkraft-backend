<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Connexion admin
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Vérifier que la session est disponible
        if (!$request->hasSession()) {
            return response()->json([
                'message' => 'Session non disponible. Vérifiez la configuration.',
            ], 500);
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return response()->json([
                'user' => Auth::user(),
                'message' => 'Connexion réussie',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['Les identifiants fournis sont incorrects.'],
        ]);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnexion réussie']);
    }

    /**
     * Utilisateur connecté
     */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }
}
