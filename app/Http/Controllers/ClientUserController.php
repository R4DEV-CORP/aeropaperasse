<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClientUserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $users = User::where('client_id', $user->client_id)
                        //->where('id', '!=', $user->id)
                        ->get();

            return response()->json([
                'status' => 'success',
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
            'role' => 'required|in:client,formation'
        ]);

        try {
            $currentUser = $request->user();
            
            // Créer l'utilisateur avec le même client_id que l'utilisateur connecté
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'client_id' => $currentUser->client_id
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur créé avec succès',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => $request->password ? [Password::defaults()] : '',
            'role' => 'required|in:client,formation'
        ]);

        try {
            $currentUser = $request->user();
            $user = User::where('client_id', $currentUser->client_id)
                       ->findOrFail($id);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur mis à jour avec succès',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $currentUser = $request->user();
            $user = User::where('client_id', $currentUser->client_id)
                       ->findOrFail($id);

            if ($user->id === $currentUser->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous ne pouvez pas supprimer votre propre compte'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}