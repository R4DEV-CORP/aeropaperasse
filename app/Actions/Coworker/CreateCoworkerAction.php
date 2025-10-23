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

                // 2. Créer l'utilisateur si demandé
                $user = null;
                if ($data->shouldCreateUser()) {
                    $user = $this->createUser($data);
                }

                // 3. Créer le collaborateur
                $coworker = $this->createCoworker($data, $user);

                // 4. Lier l'utilisateur au collaborateur si nécessaire
                if ($user) {
                    $this->linkUserToCoworker($user, $coworker);
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
