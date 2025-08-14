<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TwoFactorCode;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\TwoFactorCodeMail;
use App\Mail\PasswordResetMail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|in:sclient,sadmin,client,admin', // Facultatif : rôle spécifique
        ]);
        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'client', // Par défaut "user"
            'is_new' => true, // Nouvel utilisateur est marqué comme nouveau
            'is_student' => false,
        ];

        $user = User::create($userData);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            // Vérifier si c'est la première connexion
            $isFirstLogin = $user->is_new;

            // Vérifier si la 2FA est activée pour l'utilisateur
            if ($user->two_factor_enabled) {
                // Générer et envoyer le code 2FA
                $code = $this->generateAndSendTwoFactorCode($user);

                return response()->json([
                    'requires2FA' => true,
                    'is_first_login' => $isFirstLogin,
                    'message' => 'Code de vérification envoyé par email'
                ]);
            }

            // Si la 2FA n'est pas activée, connecter directement
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Connexion réussie',
                'is_first_login' => $isFirstLogin,
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_new' => $user->is_new,
                    'is_student' => $user->is_student,
                    'client_id' => $user->client_id
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Identifiants invalides'
        ], 401);
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'code' => 'required|string|size:6'
        ]);

        // Vérifier les identifiants
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants invalides'
            ], 401);
        }

        $user = Auth::user();

        // Vérifier si c'est la première connexion
        $isFirstLogin = $user->is_new;

        // Vérifier le code 2FA
        $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->first();

        if (!$twoFactorCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Code de vérification invalide ou expiré'
            ], 401);
        }

        // Supprimer le code utilisé
        $twoFactorCode->delete();

        // Générer le token d'authentification
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Vérification réussie',
            'is_first_login' => $isFirstLogin,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'is_new' => $user->is_new,
                'is_student' => $user->is_student,
                'client_id' => $user->client_id
            ]
        ]);
    }

    private function generateAndSendTwoFactorCode(User $user)
    {
        // Générer un code à 6 chiffres
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Supprimer les anciens codes non utilisés
        TwoFactorCode::where('user_id', $user->id)->delete();

        // Créer un nouveau code
        TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10) // Le code expire après 10 minutes
        ]);

        // Envoyer le code par email
        Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        return $code;
    }

    public function verifyToken(Request $request)
    {
        $response = [
            'status' => 'success',
            'message' => 'Token valid',
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'is_new' => $request->user()->is_new,
                'is_student' => $request->user()->is_student,
                'client_id' => $request->user()->client_id
            ],
        ];

        // \Log::info('Response data: ', $response);

        return response()->json($response);
    }

    public function user(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function client(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'client' => $user->client
        ]);
    }

    /**
     * Change le mot de passe de l'utilisateur et marque le compte comme n'étant plus nouveau
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        $user = $request->user();

        // Vérifier le mot de passe actuel
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Le mot de passe actuel est incorrect'
            ], 422);
        }

        // Mettre à jour le mot de passe et marquer le compte comme n'étant plus nouveau
        $user->password = Hash::make($request->password);
        $user->is_new = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe modifié avec succès'
        ]);
    }

    /**
     * Change le mot de passe lors de la première connexion
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function firstLoginChangePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string|min:8',
        ]);

        $user = $request->user();

        // Vérifier si c'est bien un nouvel utilisateur
        if (!$user->is_new) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cette opération est réservée à la première connexion'
            ], 403);
        }

        // Mettre à jour le mot de passe et marquer le compte comme n'étant plus nouveau
        $user->password = Hash::make($request->password);
        $user->is_new = false;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe défini avec succès'
        ]);
    }

   /**
   * Demande de mot de passe oublié
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function forgotPassword(Request $request)
  {
      $request->validate(['email' => 'required|email']);

      $user = User::where('email', $request->email)->first();

      if (!$user) {
          return response()->json([
              'status' => 'success',
              'message' => 'Si un compte associé à cet email existe, un lien de réinitialisation a été envoyé.'
          ]);
      }

      // Supprimer les anciens tokens
      PasswordResetToken::where('email', $request->email)->delete();

      // Créer un nouveau token
      $token = Str::random(64);

      PasswordResetToken::create([
          'email' => $request->email,
          'token' => $token,
          'created_at' => now(),
          'expires_at' => now()->addHours(1)
      ]);

      // Envoyer l'email avec le lien de réinitialisation
      try {
          Mail::to($request->email)->send(new PasswordResetMail($token, $request->email));

          return response()->json([
              'status' => 'success',
              'message' => 'Si un compte associé à cet email existe, un lien de réinitialisation a été envoyé.'
          ]);
      } catch (\Exception $e) {
          return response()->json([
              'status' => 'error',
              'message' => 'Une erreur est survenue lors de l\'envoi de l\'email.',
              'error' => $e->getMessage()
          ], 500);
      }
  }

   /**
   * Vérifier le token de réinitialisation
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function checkResetToken(Request $request)
  {
      $request->validate([
          'token' => 'required|string',
          'email' => 'required|email'
      ]);

      $resetToken = PasswordResetToken::where('token', $request->token)
          ->where('email', $request->email)
          ->where('expires_at', '>', now())
          ->first();

      if (!$resetToken) {
          return response()->json([
              'status' => 'error',
              'message' => 'Ce lien de réinitialisation est invalide ou a expiré.'
          ], 400);
      }

      return response()->json([
          'status' => 'success',
          'message' => 'Token valide'
      ]);
  }

   /**
   * Réinitialiser le mot de passe
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function resetPassword(Request $request)
  {
      $request->validate([
          'token' => 'required|string',
          'email' => 'required|email',
          'password' => 'required|string|min:8|confirmed',
          'password_confirmation' => 'required|string|min:8'
      ]);

      $resetToken = PasswordResetToken::where('token', $request->token)
          ->where('email', $request->email)
          ->where('expires_at', '>', now())
          ->first();

      if (!$resetToken) {
          return response()->json([
              'status' => 'error',
              'message' => 'Ce lien de réinitialisation est invalide ou a expiré.'
          ], 400);
      }

      $user = User::where('email', $request->email)->first();

      if (!$user) {
          return response()->json([
              'status' => 'error',
              'message' => 'Aucun utilisateur trouvé avec cet email.'
          ], 404);
      }

      // Mettre à jour le mot de passe
      $user->password = Hash::make($request->password);
      $user->save();

      // Supprimer tous les tokens pour cet email
      PasswordResetToken::where('email', $request->email)->delete();

      return response()->json([
          'status' => 'success',
          'message' => 'Votre mot de passe a été réinitialisé avec succès.'
      ]);
  }
}
