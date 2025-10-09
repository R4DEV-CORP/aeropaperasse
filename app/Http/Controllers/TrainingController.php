<?php

namespace App\Http\Controllers;

use App\Models\BadgeRequest;
use App\Models\Client;
use App\Models\User;
use App\Models\UserTraining;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TrainingController extends Controller
{
    public function getClientsWithTrainings()
    {
        try {
            $clients = Client::with(['users'])->get();

            $stats = [
                'totalClients' => $clients->count(),
                'totalUtilisateurs' => User::count(),
                'expiresSoon' => UserTraining::whereNotNull('expires_at')
                    ->where('expires_at', '<=', Carbon::now()->addMonth())
                    ->distinct('user_id')
                    ->count(),
            ];

            $clientsData = $clients->map(function ($client) {
                $latestTraining = UserTraining::whereHas('user', function ($query) use ($client) {
                    $query->where('client_id', $client->id);
                })
                    ->orderBy('started_at', 'desc')
                    ->first();

                return [
                    'id' => $client->id,
                    'nom' => $client->name,
                    'referent' => $client->referent_name ?? 'Non défini',
                    'email' => $client->referent_email ?? 'Non défini',
                    'nombreUtilisateurs' => $client->users->count(),
                    'derniereFormation' => optional($latestTraining)->started_at,
                    'dateExpiration' => optional($latestTraining)->expires_at,
                ];
            });

            return response()->json([
                'status' => 'success',
                'clients' => $clientsData,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans getClientsWithTrainings: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getClientDetails($clientId)
    {
        try {
            $client = Client::with(['users' => function ($query) {
                $query->with(['trainings' => function ($q) {
                    $q->withPivot(['started_at', 'expires_at', 'certificate_path', 'validity_years'])
                        ->orderBy('user_trainings.expires_at', 'desc');
                }]);
            }])->findOrFail($clientId);

            $expiresSoon = UserTraining::whereHas('user', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now()->addMonth())
                ->count();

            $userData = $client->users->map(function ($user) {
                $latestTraining = $user->trainings->first();

                return [
                    'id' => $user->id,
                    'nom' => $user->name,
                    'email' => $user->email,
                    'nombreFormations' => $user->trainings->count(),
                    'derniereFormation' => $latestTraining ? $latestTraining->pivot->started_at : null,
                    'dateExpiration' => $latestTraining ? $latestTraining->pivot->expires_at : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'client' => [
                    'id' => $client->id,
                    'nom' => $client->name,
                ],
                'users' => $userData,
                'stats' => [
                    'expiresSoon' => $expiresSoon,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans getClientDetails: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des détails du client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserTrainings($id)
    {
        try {
            $user = User::with(['trainings' => function ($query) {
                $query->withPivot(['id', 'started_at', 'expires_at', 'certificate_path', 'validity_years']);
            }])->findOrFail($id);

            $latestBadgeRequest = BadgeRequest::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();

            $trainings = $user->trainings->map(function ($training) {
                $hasCertificate = ! empty($training->pivot->certificate_path);

                return [
                    'id' => $training->id,
                    'userTrainingId' => $training->pivot->id,
                    'title' => $training->title,
                    'validity_years' => $training->pivot->validity_years,
                    'started_at' => $training->pivot->started_at,
                    'expires_at' => $training->pivot->expires_at,
                    'certificate' => [
                        'exists' => $hasCertificate,
                        'path' => $hasCertificate ? $training->pivot->certificate_path : null,
                        'url' => $hasCertificate ? Storage::disk('public')->url($training->pivot->certificate_path) : null,
                    ],
                ];
            });

            return response()->json([
                'status' => 'success',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $latestBadgeRequest ? $latestBadgeRequest->telephone : null,
                    'function' => $user->function,
                    'client' => $user->client->name,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ],
                'trainings' => $trainings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des formations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'training_id' => 'required|exists:trainings,id',
                'started_at' => 'required|date',
                'validity_years' => 'required|in:3,5',
            ]);

            // Calcul de la date d'expiration
            $startDate = Carbon::parse($validatedData['started_at']);
            $expirationDate = $startDate->copy()->addYears($validatedData['validity_years']);

            $userTraining = UserTraining::create([
                'user_id' => $validatedData['user_id'],
                'training_id' => $validatedData['training_id'],
                'started_at' => $validatedData['started_at'],
                'expires_at' => $expirationDate,
                'validity_years' => $validatedData['validity_years'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Formation attribuée avec succès',
                'userTraining' => $userTraining,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation des données',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur dans store: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de l\'attribution de la formation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadCertificate(Request $request, $userTrainingId)
    {
        try {
            $userTraining = UserTraining::with(['user', 'training'])
                ->find($userTrainingId);

            if (! $userTraining) {
                return response()->json([
                    'message' => 'La formation utilisateur avec ID '.$userTrainingId.' n\'existe pas',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'certificate' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if ($userTraining->certificate_path) {
                Storage::disk('public')->delete($userTraining->certificate_path);
            }

            $path = $request->file('certificate')->store('certificats', 'public');

            $userTraining->update([
                'certificate_path' => $path,
            ]);

            return response()->json([
                'message' => 'Certificat uploadé avec succès',
                'certificate' => [
                    'exists' => true,
                    'path' => $path,
                    'url' => Storage::disk('public')->url($path),
                ],
            ]);
        } catch (\Exception $e) {

            Log::error('Erreur lors de l\'upload du certificat: '.$e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de l\'upload du certificat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadCertificate($userTrainingId)
    {
        try {
            $userTraining = UserTraining::findOrFail($userTrainingId);

            if (! $userTraining->certificate_path) {
                return response()->json(['message' => 'Aucun certificat disponible pour cette formation'], 404);
            }

            if (! Storage::disk('public')->exists($userTraining->certificate_path)) {
                return response()->json(['message' => 'Fichier non trouvé'], 404);
            }

            $training = $userTraining->training;
            $user = $userTraining->user;

            $certificatePath = $userTraining->certificate_path;

            return response()->json([
                'success' => true,
                'path' => $certificatePath,
                'filename' => "certificat_{$training->title}_{$user->name}.pdf",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du certificat',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
