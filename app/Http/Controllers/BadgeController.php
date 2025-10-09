<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use App\Models\BadgeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BadgeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $badgesQuery = Badge::with(['badgeRequest', 'badgeRequest.user', 'badgeRequest.user.client'])
            ->orderBy('created_at', 'desc');

        if ($user->role == 'sadmin') {
            // Les super admins peuvent voir tous les badges
        } elseif ($user->role == 'admin') {
            // Les admins peuvent voir tous les badges
        } elseif ($user->role == 'sclient' || $user->role == 'client') {
            // Les clients ne peuvent voir que les badges de leur société
            $badgesQuery->whereHas('badgeRequest.user', function ($query) use ($user) {
                $query->where('client_id', $user->client_id);
            })
            // OU les badges importés pour leur société
                ->orWhere('holder_client', $user->client->name ?? '');
        } else {
            // Pour les autres rôles, pas d'accès
            return response()->json([
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $badges = $badgesQuery->get()
            ->map(function ($badge) {
                return [
                    'id' => $badge->id,
                    'holder' => [
                        'nom' => $badge->holder_nom ?? ($badge->badgeRequest->nom ?? null),
                        'prenom' => $badge->holder_prenom ?? ($badge->badgeRequest->prenom ?? null),
                        'email' => $badge->holder_email ?? ($badge->badgeRequest->email ?? null),
                        'client' => $badge->holder_client ?? ($badge->badgeRequest && $badge->badgeRequest->user && $badge->badgeRequest->user->client ? $badge->badgeRequest->user->client->name : 'Client non défini'),
                    ],
                    'status' => $badge->status,
                    'createdAt' => $badge->created_at,
                    'expiryDate' => $badge->expiry_date,
                    'returnedAt' => $badge->returned_at,
                    'returnDocument' => $badge->return_document ? Storage::url($badge->return_document) : null,
                    'isImported' => $badge->import_source !== null,
                    'externalRequestNumber' => $badge->external_request_number,
                    'requestDate' => $badge->request_date,
                    'importSource' => $badge->import_source,
                ];
            });

        return response()->json([
            'badges' => $badges,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'badge_request_id' => 'nullable|exists:badge_requests,id',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Vérifier si la demande est approuvée (seulement si badge_request_id est fourni)
        if ($request->badge_request_id) {
            $badgeRequest = BadgeRequest::find($request->badge_request_id);
            if ($badgeRequest->status !== 'ready_for_delivery') {
                return response()->json([
                    'message' => 'La demande de badge doit être approuvée pour créer un badge',
                ], 422);
            }
        }

        $badge = Badge::create([
            'badge_request_id' => $request->badge_request_id,
            'status' => 'active',
            'expiry_date' => $request->expiry_date,
        ]);

        return response()->json([
            'message' => 'Badge créé avec succès',
            'badge' => $badge,
        ], 201);
    }

    public function show(Badge $badge)
    {
        $badge->load(['badgeRequest', 'badgeRequest.user', 'badgeRequest.user.client']);

        return response()->json([
            'badge' => [
                'id' => $badge->id,
                'holder' => [
                    'nom' => $badge->holder_nom ?? ($badge->badgeRequest->nom ?? null),
                    'prenom' => $badge->holder_prenom ?? ($badge->badgeRequest->prenom ?? null),
                    'email' => $badge->holder_email ?? ($badge->badgeRequest->email ?? null),
                    'client' => $badge->holder_client ?? ($badge->badgeRequest && $badge->badgeRequest->user && $badge->badgeRequest->user->client ? $badge->badgeRequest->user->client->name : 'Client non défini'),
                    'telephone' => $badge->holder_telephone ?? ($badge->badgeRequest->telephone ?? null),
                ],
                'status' => $badge->status,
                'createdAt' => $badge->created_at,
                'expiryDate' => $badge->expiry_date,
                'returnedAt' => $badge->returned_at,
                'returnDocument' => $badge->return_document ? Storage::url($badge->return_document) : null,
                'isImported' => $badge->import_source !== null,
                'externalRequestNumber' => $badge->external_request_number,
                'requestDate' => $badge->request_date,
                'importSource' => $badge->import_source,
            ],
        ]);
    }

    public function return(Request $request, Badge $badge)
    {
        // Valider que le badge peut être restitué
        if ($badge->status !== 'active') {
            return response()->json([
                'message' => 'Ce badge ne peut pas être restitué dans son état actuel.',
            ], 422);
        }

        // Gérer le document de restitution
        $returnDocument = null;
        if ($request->hasFile('return_document')) {
            $returnDocument = $request->file('return_document')->store('return-documents', 'public');
        }

        // Mettre à jour le badge
        $badge->update([
            'status' => 'returned',
            'returned_at' => now(),
            'return_document' => $returnDocument,
        ]);

        return response()->json([
            'message' => 'Badge restitué avec succès',
            'badge' => $badge,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $badge = Badge::find($id);

        if (! $badge) {
            return response()->json(['message' => 'Badge non trouvé'], 404);
        }

        $badge->status = $request->status;
        $badge->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'badge' => $badge,
        ]);
    }

    public function updateExpiryDate(Request $request, Badge $badge)
    {
        $validator = Validator::make($request->all(), [
            'expiry_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        $badge->update([
            'expiry_date' => $request->expiry_date,
        ]);

        return response()->json([
            'message' => 'Date d\'expiration mise à jour avec succès',
            'badge' => $badge,
        ]);
    }

    public function import(Request $request)
    {
        try {
            // Validation du fichier uploadé
            $validator = Validator::make($request->all(), [
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Fichier invalide',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Lecture du fichier CSV
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getPathname()));

            // Récupérer l'en-tête (première ligne)
            $header = array_shift($csvData);

            $successCount = 0;
            $errors = [];
            $badgesToInsert = [];

            // Traiter chaque ligne
            foreach ($csvData as $index => $row) {
                $rowNumber = $index + 2;

                try {
                    // Combiner l'en-tête avec les données
                    $data = array_combine($header, $row);

                    // Validation simple
                    if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
                        $errors[] = "Ligne {$rowNumber}: Nom, prénom et email sont obligatoires";

                        continue;
                    }

                    // Préparer les données pour l'insertion
                    $badgesToInsert[] = [
                        'holder_nom' => $data['nom'],
                        'holder_prenom' => $data['prenom'],
                        'holder_email' => $data['email'],
                        'holder_telephone' => $data['telephone'] ?? null,
                        'holder_client' => $data['raison_sociale'] ?? null,
                        'external_request_number' => $data['numero_demande'] ?? null,
                        'request_date' => ! empty($data['date_demande']) ? $data['date_demande'] : null,
                        'status' => $data['statut'] ?? 'active',
                        'import_source' => 'csv_import',
                        'badge_request_id' => null,
                        'expiry_date' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Ligne {$rowNumber}: ".$e->getMessage();
                }
            }

            // Insertion en une seule fois
            if (! empty($badgesToInsert)) {
                Badge::insert($badgesToInsert);
            }

            return response()->json([
                'message' => 'Import terminé',
                'success_count' => $successCount,
                'total_lines' => count($csvData),
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de l\'import',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
