<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Voir les infos du profil
     */
    public function show()
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();

        return response()->json([
            'user' => $user,
            'client' => $client
        ]);
    }

    /**
     * Modifier les infos et adresses
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $client = Client::where('user_id', $user->id)->first();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'shipping_address' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:255'],
            'shipping_zip_code' => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:255'],
            'billing_address' => ['nullable', 'string', 'max:255'],
            'billing_city' => ['nullable', 'string', 'max:255'],
            'billing_zip_code' => ['nullable', 'string', 'max:20'],
            'billing_country' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        // Update User
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Update Client
        $client->update($validated);

        return response()->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => $user,
            'client' => $client
        ]);
    }

    /**
     * Supprimer le compte
     */
    public function destroy()
    {
        $user = Auth::user();
        
        Auth::logout();
        
        // Supprimer le client (cascade via DB possible ou manuel)
        Client::where('user_id', $user->id)->delete();
        $user->delete();

        return response()->json(['message' => 'Compte supprimé avec succès']);
    }
}
