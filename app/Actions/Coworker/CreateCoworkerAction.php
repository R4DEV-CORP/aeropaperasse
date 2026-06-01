<?php

namespace App\Actions\Coworker;

use App\DataTransferObjects\CreateCoworkerData;
use App\Models\Client;
use App\Models\Coworker;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Action pour créer un collaborateur avec optionnellement un utilisateur associé
 */
class CreateCoworkerAction
{
    /**
     * Exécute la création d'un collaborateur
     */
    public function execute(CreateCoworkerData $data): CreateCoworkerResult
    {
        try {
            return DB::transaction(function () use ($data) {
                // 1. Vérifier que le client existe
                $client = Client::findOrFail($data->client_id);

                // 2. Résoudre l'utilisateur : soit un existant à rattacher, soit on en crée un.
                $user = null;
                if ($data->shouldAttachExistingUser()) {
                    $user = User::findOrFail($data->existing_user_id);
                } elseif ($data->shouldCreateUser()) {
                    $user = $this->createUser($data);
                }

                // 3. Créer le collaborateur
                $coworker = $this->createCoworker($data, $user);

                // 4. Lier l'utilisateur au collaborateur + attacher la pivot tenant.
                if ($user) {
                    $this->linkUserToCoworker($user, $coworker);
                    $this->attachUserToActiveTenant($user, $data);
                }

                // 5. Log de la création
                $this->logCreationSuccess($coworker, $user, $client, $data);

                return CreateCoworkerResult::success($coworker, $user, 'Collaborateur créé avec succès');
            });
        } catch (\Exception $e) {
            return $this->handleException($e, $data);
        }
    }

    /**
     * Crée un utilisateur associé au collaborateur
     */
    private function createUser(CreateCoworkerData $data): User
    {
        $userData = $data->getUserData();

        if (! $userData) {
            throw new \Exception('Données utilisateur manquantes');
        }

        // Hasher le mot de passe
        $userData['password'] = Hash::make($userData['password']);

        return User::create($userData);
    }

    /**
     * Crée le collaborateur
     */
    private function createCoworker(CreateCoworkerData $data, ?User $user): Coworker
    {
        $coworkerData = $data->getCoworkerData();

        // Ajouter l'ID de l'utilisateur si créé
        if ($user) {
            $coworkerData['user_id'] = $user->id;
        }

        return Coworker::create($coworkerData);
    }

    /**
     * Lie l'utilisateur au collaborateur
     */
    private function linkUserToCoworker(User $user, Coworker $coworker): void
    {
        $user->coworker_id = $coworker->id;
        $user->save();
    }

    /**
     * Attache l'utilisateur au tenant actif via la pivot `tenant_user` (rôle + client_id).
     * Sans cette ligne, EnsureTenantMembership redirige le compte vers tenant.no-access
     * au prochain login. Les rôles REM court-circuitent et n'ont pas besoin de pivot.
     */
    private function attachUserToActiveTenant(User $user, CreateCoworkerData $data): void
    {
        $activeTenant = tenant();
        if ($activeTenant === null) {
            return;
        }

        if (in_array($data->role, ['rem_admin', 'rem_super_admin'], true)) {
            return;
        }

        $tenantKey = $activeTenant->getTenantKey();

        // Idempotent attach — REM staff and users already on this tenant skip,
        // protecting the unique(user_id, tenant_id) constraint on tenant_user.
        if ($user->belongsToTenant($tenantKey) && ! $user->isRemStaff()) {
            return;
        }

        $user->tenants()->attach($tenantKey, [
            'role' => $data->role ?? 'client',
            'client_id' => $data->client_id,
            'can_access_formation' => $data->can_access_formation,
        ]);
    }

    /**
     * Log le succès de la création
     */
    private function logCreationSuccess(
        Coworker $coworker,
        ?User $user,
        Client $client,
        CreateCoworkerData $data
    ): void {
        Log::info('Collaborateur créé avec succès', [
            'coworker_id' => $coworker->id,
            'user_id' => $user?->id,
            'client_id' => $client->id,
            'company_name' => $client->company_name,
            'coworker_name' => $data->getFullName(),
            'coworker_email' => $data->email,
            'user_created' => $data->shouldCreateUser(),
            'created_by' => $data->created_by,
        ]);
    }

    /**
     * Gère les exceptions et retourne un résultat d'échec
     */
    private function handleException(\Exception $e, CreateCoworkerData $data): CreateCoworkerResult
    {
        Log::error('Erreur lors de la création du collaborateur', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'coworker_data' => $data->getLogData(),
        ]);

        $message = 'Erreur lors de la création du collaborateur : '.$e->getMessage();

        return CreateCoworkerResult::failure($message);
    }
}
