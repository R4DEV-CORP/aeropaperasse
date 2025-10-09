<?php

namespace App\Http\Controllers;

use App\Mail\UserCreated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            if ($user->role == 'sadmin') {
                $users = User::with('client')->get();
            } elseif ($user->role == 'admin') {
                $users = User::with('client')->where('role', '!=', 'sadmin')->get();
            } elseif ($user->role == 'sclient') {
                $users = User::with('client')
                    ->where('role', '!=', 'sadmin')
                    ->where('role', '!=', 'admin')
                    ->where('client_id', $user->client_id)
                    ->get();
            }

            return response()->json([
                'status' => 'success',
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des utilisateurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:sadmin,sclient,admin,client',
            'client_id' => 'nullable|exists:clients,id',
            'has_left' => 'nullable|boolean',
            'departure_date' => 'nullable|date|required_if:has_left,true',
            'is_student' => 'nullable|boolean',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'client_id' => $request->client_id,
                'function' => $request->function,
                'has_left' => $request->has_left ?? false,
                'departure_date' => $request->departure_date,
                'is_new' => true,
                'is_student' => $request->is_student ?? false,
            ]);
            // Charger la relation client pour la réponse
            $user->load('client');

            // Envoyer un email à l'utilisateur nouvellement créé
            $this->sendWelcomeEmail($user, $request->password);

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur créé avec succès',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'utilisateur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envoie un email de bienvenue à l'utilisateur nouvellement créé
     *
     * @param  string  $plainPassword
     * @return void
     */
    private function sendWelcomeEmail(User $user, $plainPassword)
    {
        try {
            Mail::to($user->email)->send(new UserCreated($user, $plainPassword));
            Log::info('Email de bienvenue envoyé à '.$user->email);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email de bienvenue: '.$e->getMessage());
        }
    }

    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|string|in:sadmin,sclient,admin,client',
            'client_id' => 'required',
            'password' => 'nullable|string|min:6', // Le mot de passe est optionnel à la mise à jour
            'has_left' => 'nullable|boolean',
            'departure_date' => 'nullable|date|required_if:has_left,true',
            'is_student' => 'nullable|boolean',
        ]);

        try {
            // Préparation des données à mettre à jour
            $userData = $request->only(['name', 'email', 'role', 'client_id']);

            // Si un nouveau mot de passe est fourni, on le hash
            if ($request->filled('password')) {
                $userData['password'] = bcrypt($request->password);
            }

            $userData['has_left'] = $request->has_left ?? false;
            $userData['departure_date'] = $request->departure_date;
            $userData['is_student'] = $request->is_student ?? false;

            $user->update($userData);

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur mis à jour avec succès',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Utilisateur supprimé avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de l\'utilisateur',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->input('query');
            $clientId = $request->input('client_id');

            $user = Auth::user();

            Log::info('User search debug', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'user_client_id' => $user->client_id,
                'search_query' => $request->input('query'),
                'search_client_id' => $request->input('client_id'),
            ]);

            $usersQuery = User::query();

            if ($user->role == 'sadmin') {
                // Les super admins peuvent voir tous les utilisateurs
            } elseif ($user->role == 'admin') {
                // Les admins ne peuvent pas voir les super admins
                $usersQuery->where('role', '!=', 'sadmin');
            } elseif ($user->role == 'sclient') {
                // Les super clients ne peuvent voir que les utilisateurs de leur société
                $usersQuery->where('role', '!=', 'sadmin')
                    ->where('role', '!=', 'admin')
                    ->where('client_id', $user->client_id);
            } else {
                // Pour les autres rôles, on limite l'accès
                return response()->json([
                    'status' => 'error',
                    'message' => 'Accès non autorisé',
                ], 403);
            }

            if ($query) {
                $usersQuery->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%");
                });
            }

            if ($clientId) {
                if ($user->role == 'sclient' && $clientId != $user->client_id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Vous ne pouvez voir que les utilisateurs de votre société',
                    ], 403);
                }
                $usersQuery->where('client_id', $clientId);
            }

            $users = $usersQuery->with('client')->get();

            return response()->json([
                'status' => 'success',
                'users' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la recherche d\'utilisateurs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
