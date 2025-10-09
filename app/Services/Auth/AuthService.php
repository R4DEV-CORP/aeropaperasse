<?php

namespace App\Services\Auth;

use App\Mail\TwoFactorCodeMail;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    /**
     * Tente de connecter l'utilisateur et retourne un tableau résultat simple
     *
     * Le Service embarque deux autres fichiers :
     *
     * - UserRedirectService : Gère la redirection après la connexion
     * - LoginResult : Gère le résultat de la connexion
     *
     * @return array ['success' => bool, 'user' => User|null, 'message' => string, 'requires2FA' => bool]
     */
    public function login(string $email, string $password): array
    {
        try {
            Log::info('AuthService: Tentative de connexion', [
                'email' => $email,
            ]);
            if (! Auth::attempt(['email' => $email, 'password' => $password])) {
                Log::info('AuthService: Identifiants invalides', [
                    'email' => $email,
                ]);

                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Identifiants invalides.',
                    'requires2FA' => false,
                ];
            }

            $user = Auth::user();

            // Vérifier si la 2FA est activée
            if ($user->two_factor_enabled) {
                Log::info('AuthService: 2FA activé', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
                $this->generateAndSendTwoFactorCode($user);

                // Stocker l'ID utilisateur en session (pas le password)
                session(['twofa_user_id' => $user->id]);

                // Déconnecter temporairement jusqu'à vérification 2FA
                Auth::logout();

                return [
                    'success' => true,
                    'user' => $user,
                    'message' => 'Code 2FA envoyé par email.',
                    'requires2FA' => true,
                ];
            }

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Connexion réussie.',
                'requires2FA' => false,
            ];

        } catch (\Exception $e) {
            Log::error('AuthService: Erreur de connexion', [
                'message' => $e->getMessage(),
                'email' => $email,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'user' => null,
                'message' => 'Une erreur est survenue lors de la connexion.',
                'requires2FA' => false,
            ];
        }
    }

    public function generateAndSendTwoFactorCode(User $user): string
    {
        Log::info('AuthService: Génération et envoi du code 2FA', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
        // Générer un code à 6 chiffres
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Supprimer les anciens codes non utilisés
        TwoFactorCode::where('user_id', $user->id)->delete();

        // Créer un nouveau code
        TwoFactorCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(10), // Le code expire après 10 minutes
        ]);

        // Envoyer le code par email
        Mail::to($user->email)->send(new TwoFactorCodeMail($code));

        return $code;
    }

    /**
     * Vérifie seulement le code 2FA (utilisateur déjà authentifié en session)
     *
     * @param  string  $code  Code 2FA à 6 chiffres
     * @return array ['success' => bool, 'user' => User|null, 'message' => string]
     */
    public function verifyTwoFACode(string $code): array
    {
        try {
            // Récupérer l'ID utilisateur depuis la session
            $userId = session('twofa_user_id');

            if (! $userId) {
                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Session 2FA expirée. Veuillez vous reconnecter.',
                ];
            }

            $user = User::find($userId);

            if (! $user) {
                // Nettoyer la session si utilisateur inexistant
                session()->forget('twofa_user_id');

                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Utilisateur introuvable.',
                ];
            }

            // Vérifier le code 2FA
            $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
                ->where('code', $code)
                ->where('expires_at', '>', now())
                ->first();

            if (! $twoFactorCode) {
                Log::info('AuthService: Code 2FA invalide ou expiré', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);

                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Code de vérification invalide ou expiré',
                ];
            }

            // Supprimer le code utilisé et nettoyer la session temporaire
            $twoFactorCode->delete();
            session()->forget('twofa_user_id');

            // Connecter l'utilisateur définitivement
            Auth::login($user);

            Log::info('AuthService: Vérification 2FA réussie', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Vérification 2FA réussie',
            ];

        } catch (\Exception $e) {
            Log::error('AuthService: Erreur vérification 2FA', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'user' => null,
                'message' => 'Erreur lors de la vérification du code.',
            ];
        }
    }

    /**
     * Vérifie si une session 2FA est en cours et retourne l'utilisateur
     */
    public function getTwoFAUser(): ?User
    {
        $userId = session('twofa_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    /**
     * Annule la session 2FA (en cas d'abandon par l'utilisateur)
     */
    public function cancelTwoFA(): void
    {
        $userId = session('twofa_user_id');

        if ($userId) {
            // Supprimer les codes 2FA non utilisés
            TwoFactorCode::where('user_id', $userId)->delete();
            session()->forget('twofa_user_id');

            Log::info('AuthService: Session 2FA annulée', [
                'user_id' => $userId,
            ]);
        }
    }

    public function createWebSession(User $user): void
    {
        Auth::login($user);

        // Créer un token Sanctum pour la compatibilité API si nécessaire
        $user->createToken('web_token')->plainTextToken;

        Log::info('AuthService: Session web créée', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Change le mot de passe d'un utilisateur authentifié (vérification du mot de passe actuel requis)
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function changePasswordAuthenticated(User $user, string $currentPassword, string $newPassword): array
    {
        try {
            // Vérifier que l'utilisateur est bien connecté
            if (! Auth::check() || Auth::id() !== $user->id) {
                return [
                    'success' => false,
                    'message' => 'Vous devez être connecté pour effectuer cette opération.',
                ];
            }

            // Vérifier le mot de passe actuel
            if (! Hash::check($currentPassword, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect.',
                ];
            }

            // Mettre à jour le mot de passe et marquer le compte comme n'étant plus nouveau
            $user->password = bcrypt($newPassword);
            $user->is_new = false; // Au cas où l'utilisateur n'aurait jamais changé
            $user->save();

            Log::info('AuthService: Mot de passe changé par utilisateur authentifié', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'success' => true,
                'message' => 'Mot de passe modifié avec succès',
            ];

        } catch (\Exception $e) {
            Log::error('AuthService: Erreur changement mot de passe utilisateur authentifié', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors du changement de mot de passe.',
            ];
        }
    }

    /**
     * Vérifie si l'utilisateur peut accéder à la page de changement de mot de passe
     *
     * @return array ['can_access' => bool, 'reason' => string|null, 'is_first_login' => bool]
     */
    public function canAccessChangePassword(?User $user = null): array
    {
        $user = $user ?: Auth::user();

        if (! $user) {
            return [
                'can_access' => false,
                'reason' => 'Vous devez être connecté.',
                'is_first_login' => false,
            ];
        }

        // Si c'est une première connexion, autoriser l'accès
        if ($user->is_new) {
            return [
                'can_access' => true,
                'reason' => null,
                'is_first_login' => true,
            ];
        }

        // Pour les utilisateurs existants, ils doivent être authentifiés normalement
        if (Auth::check()) {
            return [
                'can_access' => true,
                'reason' => null,
                'is_first_login' => false,
            ];
        }

        return [
            'can_access' => false,
            'reason' => 'Accès non autorisé.',
            'is_first_login' => false,
        ];
    }
}
