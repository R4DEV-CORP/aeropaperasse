<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\TwoFactorCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Tente de connecter l'utilisateur et retourne un tableau résultat simple
     * 
     * @param string $email
     * @param string $password
     * @return array ['success' => bool, 'user' => User|null, 'message' => string, 'requires2FA' => bool]
     */
    public function login(string $email, string $password): array
    {
        try {
            if (!Auth::attempt(['email' => $email, 'password' => $password])) {
                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Identifiants invalides.',
                    'requires2FA' => false
                ];
            }

            $user = Auth::user();
            
            // Vérifier si la 2FA est activée
            if ($user->two_factor_enabled) {
                $this->generateAndSendTwoFactorCode($user);
                
                // Stocker l'ID utilisateur en session (pas le password)
                session(['twofa_user_id' => $user->id]);
                
                // Déconnecter temporairement jusqu'à vérification 2FA
                Auth::logout();
                
                return [
                    'success' => true,
                    'user' => $user,
                    'message' => 'Code 2FA envoyé par email.',
                    'requires2FA' => true
                ];
            }
            
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Connexion réussie.',
                'requires2FA' => false
            ];
            
        } catch (\Exception $e) {
            Log::error('AuthService: Erreur de connexion', [
                'message' => $e->getMessage(),
                'email' => $email,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return [
                'success' => false,
                'user' => null,
                'message' => 'Une erreur est survenue lors de la connexion.',
                'requires2FA' => false
            ];
        }
    }

    public function handleTwoFactor(User $user): void
    {
        $code = $this->generateAndSendTwoFactorCode($user);
        
        // Stocker temporairement les identifiants en session pour la vérification
        session([
            'temp_email' => $user->email,
            'temp_password' => request()->input('password') ?: session('temp_password')
        ]);
        
        Log::info('AuthService: Code 2FA généré et envoyé', [
            'user_id' => $user->id,
            'code_length' => strlen($code)
        ]);
    }

    public function generateAndSendTwoFactorCode(User $user): string
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

    /**
     * Vérifie seulement le code 2FA (utilisateur déjà authentifié en session)
     * 
     * @param string $code Code 2FA à 6 chiffres
     * @return array ['success' => bool, 'user' => User|null, 'message' => string]
     */
    public function verifyTwoFACode(string $code): array
    {
        try {
            // Récupérer l'ID utilisateur depuis la session
            $userId = session('twofa_user_id');
            
            if (!$userId) {
                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Session 2FA expirée. Veuillez vous reconnecter.'
                ];
            }

            $user = User::find($userId);
            
            if (!$user) {
                // Nettoyer la session si utilisateur inexistant
                session()->forget('twofa_user_id');
                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Utilisateur introuvable.'
                ];
            }

            // Vérifier le code 2FA
            $twoFactorCode = TwoFactorCode::where('user_id', $user->id)
                ->where('code', $code)
                ->where('expires_at', '>', now())
                ->first();

            if (!$twoFactorCode) {
                return [
                    'success' => false,
                    'user' => null,
                    'message' => 'Code de vérification invalide ou expiré'
                ];
            }

            // Supprimer le code utilisé et nettoyer la session temporaire
            $twoFactorCode->delete();
            session()->forget('twofa_user_id');

            // Connecter l'utilisateur définitivement
            Auth::login($user);
            
            Log::info('AuthService: Vérification 2FA réussie', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Vérification 2FA réussie'
            ];
            
        } catch (\Exception $e) {
            Log::error('AuthService: Erreur vérification 2FA', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return [
                'success' => false,
                'user' => null,
                'message' => 'Erreur lors de la vérification du code.'
            ];
        }
    }

    /**
     * Vérifie si une session 2FA est en cours et retourne l'utilisateur
     */
    public function getTwoFAUser(): ?User
    {
        $userId = session('twofa_user_id');
        
        if (!$userId) {
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
                'user_id' => $userId
            ]);
        }
    }

    public function createWebSession(User $user): void
    {
        Auth::login($user);
        
        // Créer un token Sanctum pour la compatibilité API si nécessaire
        $user->createToken('web_token')->plainTextToken;
        
        Log::info('AuthService: Session web créée', [
            'user_id' => $user->id
        ]);
    }
}
