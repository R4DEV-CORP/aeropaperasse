<?php

namespace App\Http\Controllers;

use App\Models\ActivityRequest;
use App\Models\Client;
use App\Services\ClientOverviewPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function all()
    {
        try {
            $clients = Client::all()->map(function ($client) {
                // Forcer le calcul ET l'ajout des attributs à la réponse JSON
                $client->append(['active_badges_count', 'active_vehicle_passes_count']);

                return $client;
            });

            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des clients',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function index()
    {
        try {
            $clients = Client::withCount('users')->get();

            return response()->json($clients);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des clients',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            'safety_referent_name_1' => 'required|string|max:255',
            'safety_referent_email_1' => 'required|email|max:255',
            'safety_referent_phone_1' => 'required|string|max:255',
            'safety_document' => 'required|file|mimes:pdf|max:2048',

            'safety_referent_name_2' => 'nullable|string|max:255',
            'safety_referent_email_2' => 'nullable|email|max:255',
            'safety_referent_phone_2' => 'nullable|string|max:255',
            'safety_referent_name_3' => 'nullable|string|max:255',
            'safety_referent_email_3' => 'nullable|email|max:255',
            'safety_referent_phone_3' => 'nullable|string|max:255',

            'security_correspondent_name' => 'required|string|max:255',
            'security_correspondent_email' => 'required|email|max:255',
            'security_correspondent_phone' => 'required|string|max:255',
            'security_document' => 'required|file|mimes:pdf|max:2048',

            'kbis_document' => 'required|file|mimes:pdf|max:2048',

            'hr_contact_name' => 'required|string|max:255',
            'hr_contact_email' => 'required|email|max:255',
            'hr_contact_phone' => 'required|string|max:255',

            'badge_limit' => 'required|integer|min:1|max:1000',
            'vehicle_pass_limit' => 'required|integer|min:0|max:1000',
        ]);

        try {

            $clientData = $request->except([
                'safety_document',
                'security_document',
                'kbis_document',
            ]);

            if ($request->hasFile('safety_document')) {
                $path = $request->file('safety_document')->store('client_documents/safety_referents', 'public');
                $clientData['safety_document'] = $path;
            }

            if ($request->hasFile('security_document')) {
                $path = $request->file('security_document')->store('client_documents/security_correspondents', 'public');
                $clientData['security_document'] = $path;
            }

            if ($request->hasFile('kbis_document')) {
                $path = $request->file('kbis_document')->store('client_documents/kbis', 'public');
                $clientData['kbis_document'] = $path;
            }

            $client = Client::create($clientData);

            return response()->json($client, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            'safety_referent_name_1' => 'required|string|max:255',
            'safety_referent_email_1' => 'required|email|max:255',
            'safety_referent_phone_1' => 'required|string|max:255',
            'safety_document' => $client->safety_document ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',

            'safety_referent_name_2' => 'nullable|string|max:255',
            'safety_referent_email_2' => 'nullable|email|max:255',
            'safety_referent_phone_2' => 'nullable|string|max:255',
            'safety_referent_name_3' => 'nullable|string|max:255',
            'safety_referent_email_3' => 'nullable|email|max:255',
            'safety_referent_phone_3' => 'nullable|string|max:255',

            'security_correspondent_name' => 'required|string|max:255',
            'security_correspondent_email' => 'required|email|max:255',
            'security_correspondent_phone' => 'required|string|max:255',
            'security_document' => $client->security_document ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',

            'kbis_document' => $client->kbis_document ? 'nullable|file|mimes:pdf|max:2048' : 'required|file|mimes:pdf|max:2048',

            'hr_contact_name' => 'required|string|max:255',
            'hr_contact_email' => 'required|email|max:255',
            'hr_contact_phone' => 'required|string|max:255',

            'badge_limit' => 'required|integer|min:1|max:1000',
            'vehicle_pass_limit' => 'required|integer|min:0|max:1000',
        ]);

        try {

            $clientData = $request->except([
                'safety_document',
                'security_document',
                'kbis_document',
            ]);

            if ($request->hasFile('safety_document')) {
                if ($client->safety_document) {
                    Storage::disk('public')->delete($client->safety_document);
                }
                $path = $request->file('safety_document')->store('client_documents/safety_referents', 'public');
                $clientData['safety_document'] = $path;
            }

            if ($request->hasFile('security_document')) {
                if ($client->security_document) {
                    Storage::disk('public')->delete($client->security_document);
                }
                $path = $request->file('security_document')->store('client_documents/security_correspondents', 'public');
                $clientData['security_document'] = $path;
            }

            if ($request->hasFile('kbis_document')) {
                if ($client->kbis_document) {
                    Storage::disk('public')->delete($client->kbis_document);
                }
                $path = $request->file('kbis_document')->store('client_documents/kbis', 'public');
                $clientData['kbis_document'] = $path;
            }

            $client->update($clientData);

            return response()->json($client);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du client',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Client $client)
    {
        try {
            // Vérifier si le client a des utilisateurs
            $usersCount = $client->users()->count();
            if ($usersCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Impossible de supprimer cette société car elle a {$usersCount} collaborateur(s) associé(s). Vous devez d'abord supprimer ou réassigner tous les collaborateurs.",
                    'error_type' => 'users_associated',
                    'users_count' => $usersCount,
                ], 400);
            }

            // Supprimer les documents associés
            if ($client->safety_document) {
                Storage::disk('public')->delete($client->safety_document);
            }
            if ($client->security_document) {
                Storage::disk('public')->delete($client->security_document);
            }
            if ($client->kbis_document) {
                Storage::disk('public')->delete($client->kbis_document);
            }

            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Société supprimée avec succès',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la société',
                'error' => $e->getMessage(),
                'error_type' => 'server_error',
            ], 500);
        }
    }

    public function downloadDocument(Request $request, Client $client)
    {
        $documentType = $request->input('type');

        $documentPath = null;
        $fileName = '';

        if ($documentType === 'safety_document') {
            $documentPath = $client->safety_document;
            $fileName = "referent_surete_{$client->name}.pdf";
        } elseif ($documentType === 'security_document') {
            $documentPath = $client->security_document;
            $fileName = "correspondant_securite_{$client->name}.pdf";
        } elseif ($documentType === 'kbis') {
            $documentPath = $client->kbis_document;
            $fileName = "kbis_{$client->name}.pdf";
        }

        if (! $documentPath) {
            return response()->json(['success' => false, 'message' => 'Document non défini'], 404);
        }

        if (! Storage::disk('public')->exists($documentPath)) {
            return response()->json(['success' => false, 'message' => "Document non trouvé: {$documentPath}"], 404);
        }

        return response()->json([
            'success' => true,
            // 'path' => $documentPath,
            'url' => Storage::disk('public')->url($documentPath),
            'filename' => $fileName,
        ]);
    }

    public function getQuotaInfo(Client $client)
    {
        try {
            $badgeInfo = [
                'used' => $client->active_badges_count,
                'total' => $client->badge_limit,
                'remaining' => max(0, $client->badge_limit - $client->active_badges_count),
                'percentage' => $client->badge_limit > 0 ? round(($client->active_badges_count / $client->badge_limit) * 100, 1) : 0,
                'can_create' => $client->canCreateBadge(),
            ];

            $vehiclePassInfo = [
                'used' => $client->active_vehicle_passes_count,
                'total' => $client->vehicle_pass_limit,
                'remaining' => max(0, $client->vehicle_pass_limit - $client->active_vehicle_passes_count),
                'percentage' => $client->vehicle_pass_limit > 0 ? round(($client->active_vehicle_passes_count / $client->vehicle_pass_limit) * 100, 1) : 0,
                'can_create' => $client->canCreateVehiclePass(),
            ];

            return response()->json([
                'badge_quota' => $badgeInfo,
                'vehicle_pass_quota' => $vehiclePassInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des quotas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = auth()->user();
            $clientId = (int) $id;

            // Vérification des droits d'accès
            $isAdmin = $user->role === 'sadmin' || $user->role === 'admin';
            if (! $isAdmin && $user->client_id !== $clientId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Récupération du client
            $client = Client::findOrFail($clientId);

            // Récupération de la dernière demande d'activité approuvée
            $activityRequest = ActivityRequest::whereHas('user', function ($query) use ($clientId) {
                $query->where('client_id', $clientId);
            })->where('status', 'approved')
                ->orderBy('created_at', 'desc')
                ->first();

            // Compilation des données (priorité aux données Client, fallback sur ActivityRequest)
            $data = [
                // Infos de base
                'id' => $client->id,
                'name' => $client->name,
                'badge_limit' => $client->badge_limit,
                'vehicle_pass_limit' => $client->vehicle_pass_limit,

                // Informations société (priorité Client)
                'raison_sociale' => $client->raison_sociale ?: ($activityRequest ? $activityRequest->raison_sociale : $client->name),
                'nom_commercial' => $client->nom_commercial ?: ($activityRequest ? $activityRequest->nom_commercial : null),
                'siret' => $client->siret ?: ($activityRequest ? $activityRequest->siret : null),
                'adresse' => $client->adresse ?: ($activityRequest ? $activityRequest->adresse : null),
                'code_postal' => $client->code_postal,
                'ville' => $client->ville,
                'numero_identification' => $client->numero_identification,

                // Responsable principal (priorité Client)
                'responsable_nom' => $client->responsable_nom ?: ($activityRequest ? $activityRequest->responsable_nom : null),
                'responsable_prenom' => $client->responsable_prenom ?: ($activityRequest ? $activityRequest->responsable_prenom : null),
                'responsable_email' => $client->responsable_email ?: ($activityRequest ? $activityRequest->responsable_email : null),
                'responsable_telephone' => $client->responsable_telephone ?: ($activityRequest ? $activityRequest->responsable_telephone : null),
                'responsable_fonction' => $client->responsable_fonction ?: ($activityRequest ? $activityRequest->responsable_fonction : null),

                // Activité
                'activite_description' => $client->activite_description ?: ($activityRequest ? $activityRequest->activite_description : null),
                'sous_traitant_de' => $client->sous_traitant_de,
                'nombre_demandes_activite' => $client->nombre_demandes_activite,
                'numeros_demandes_activite' => $client->numeros_demandes_activite,
                'date_debut_validite' => $client->date_debut_validite,
                'date_fin_validite' => $client->date_fin_validite,
                'aeroports_concernes' => $client->aeroports_concernes,
                'zones_concernees' => $client->zones_concernees,
                'nombre_badges_actifs' => $client->nombre_badges_actifs,
                'nombre_vehicules_actifs' => $client->nombre_vehicules_actifs,

                // Référents sûreté
                'safety_referent_name_1' => $client->safety_referent_name_1,
                'safety_referent_prenom_1' => $client->safety_referent_prenom_1,
                'safety_referent_email_1' => $client->safety_referent_email_1,
                'safety_referent_phone_1' => $client->safety_referent_phone_1,

                'safety_referent_name_2' => $client->safety_referent_name_2,
                'safety_referent_prenom_2' => $client->safety_referent_prenom_2,
                'safety_referent_email_2' => $client->safety_referent_email_2,
                'safety_referent_phone_2' => $client->safety_referent_phone_2,

                'safety_referent_name_3' => $client->safety_referent_name_3,
                'safety_referent_prenom_3' => $client->safety_referent_prenom_3,
                'safety_referent_email_3' => $client->safety_referent_email_3,
                'safety_referent_phone_3' => $client->safety_referent_phone_3,

                // Correspondant sécurité
                'security_correspondent_name' => $client->security_correspondent_name,
                'security_correspondent_prenom' => $client->security_correspondent_prenom,
                'security_correspondent_email' => $client->security_correspondent_email,
                'security_correspondent_phone' => $client->security_correspondent_phone,

                // Contact RH
                'hr_contact_name' => $client->hr_contact_name,
                'hr_contact_prenom' => $client->hr_contact_prenom,
                'hr_contact_email' => $client->hr_contact_email,
                'hr_contact_phone' => $client->hr_contact_phone,
            ];

            return response()->json([
                'status' => 'success',
                'client' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des données',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateOverview(Request $request, $id)
    {
        try {
            $user = auth()->user();
            $clientId = (int) $id;

            // Vérification des droits d'accès
            $isAdmin = $user->role === 'sadmin' || $user->role === 'admin';
            if (! $isAdmin && $user->client_id !== $clientId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Récupération du client
            $client = Client::findOrFail($clientId);

            // Validation des données du bilan uniquement
            $validatedData = $request->validate([
                // Informations société
                'raison_sociale' => 'nullable|string|max:255',
                'nom_commercial' => 'nullable|string|max:255',
                'siret' => 'nullable|string|max:255',
                'adresse' => 'nullable|string|max:500',
                'code_postal' => 'nullable|string|max:10',
                'ville' => 'nullable|string|max:255',
                'numero_identification' => 'nullable|string|max:255',

                // Responsable principal
                'responsable_nom' => 'nullable|string|max:255',
                'responsable_prenom' => 'nullable|string|max:255',
                'responsable_email' => 'nullable|email|max:255',
                'responsable_telephone' => 'nullable|string|max:20',
                'responsable_fonction' => 'nullable|string|max:255',

                // Activité
                'activite_description' => 'nullable|string',
                'sous_traitant_de' => 'nullable|string|max:255',
                'nombre_demandes_activite' => 'nullable|integer|min:0',
                'numeros_demandes_activite' => 'nullable|string',
                'date_debut_validite' => 'nullable|date',
                'date_fin_validite' => 'nullable|date|after_or_equal:date_debut_validite',
                'aeroports_concernes' => 'nullable|array',
                'zones_concernees' => 'nullable|array',
                'nombre_badges_actifs' => 'nullable|integer|min:0',
                'badge_limit' => 'nullable|integer|min:1|max:1000',
                'nombre_vehicules_actifs' => 'nullable|integer|min:0',
                'vehicle_pass_limit' => 'nullable|integer|min:0|max:1000',

                // Référents sûreté
                'safety_referent_name_1' => 'nullable|string|max:255',
                'safety_referent_prenom_1' => 'nullable|string|max:255',
                'safety_referent_email_1' => 'nullable|email|max:255',
                'safety_referent_phone_1' => 'nullable|string|max:20',

                'safety_referent_name_2' => 'nullable|string|max:255',
                'safety_referent_prenom_2' => 'nullable|string|max:255',
                'safety_referent_email_2' => 'nullable|email|max:255',
                'safety_referent_phone_2' => 'nullable|string|max:20',

                'safety_referent_name_3' => 'nullable|string|max:255',
                'safety_referent_prenom_3' => 'nullable|string|max:255',
                'safety_referent_email_3' => 'nullable|email|max:255',
                'safety_referent_phone_3' => 'nullable|string|max:20',

                // Correspondant sécurité
                'security_correspondent_name' => 'nullable|string|max:255',
                'security_correspondent_prenom' => 'nullable|string|max:255',
                'security_correspondent_email' => 'nullable|email|max:255',
                'security_correspondent_phone' => 'nullable|string|max:20',

                // Contact RH
                'hr_contact_name' => 'nullable|string|max:255',
                'hr_contact_prenom' => 'nullable|string|max:255',
                'hr_contact_email' => 'nullable|email|max:255',
                'hr_contact_phone' => 'nullable|string|max:20',
            ]);

            // Mise à jour du client
            $client->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Bilan mis à jour avec succès',
                'client' => $client->fresh(),
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Données invalides',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportOverview($id, ClientOverviewPdfService $pdfService)
    {
        try {
            $user = auth()->user();
            $clientId = (int) $id;

            // Vérification des droits d'accès
            $isAdmin = $user->role === 'sadmin' || $user->role === 'admin';
            if (! $isAdmin && $user->client_id !== $clientId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Utilisation du service
            return $pdfService->generateOverview($clientId);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la génération du PDF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
