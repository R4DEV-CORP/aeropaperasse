<?php

namespace App\Http\Controllers;

use App\Models\ActivityRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ActivityRequestConfirmation;
use App\Mail\ActivityRequestCreated;
use App\Mail\ActivityRequestStatusUpdated;
use Illuminate\Support\Facades\Validator;

class ActivityRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Si l'utilisateur est admin, récupérer toutes les demandes, sauf les brouillons
        if ($user->role === 'admin' || $user->role === 'sadmin') {
            $requests = ActivityRequest::where('status', '!=', 'draft')->latest()->get();
            $draftCount = ActivityRequest::where('status', 'draft')->count();
        }
        // Sinon, récupérer les demandes liées à l'utilisateur, sauf les brouillons
        else {
            $requests = ActivityRequest::where('status', '!=', 'draft')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
            
            $draftCount = ActivityRequest::where('status', 'draft')
                ->where('user_id', $user->id)
                ->count();
        }

        $stats = [
            'total' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count()
        ];

        return response()->json([
            'user' =>   $user,
            'requests' => $requests,
            'has_drafts' => $draftCount > 0,
            'draft_count' => $draftCount,
            'stats' => $stats
        ]);
    }

    public function store(Request $request)
    {
        if ($request->has('save_as_draft') && $request->save_as_draft) {
            return $this->storeDraft($request);
        }

        $user = auth()->user();

        // Convert "renouvellement" to a boolean value
        $renouvellement = $request->input('renouvellement') === 'true' ? 1 : 0;

        // Merge the modified value back into the request
        $request->merge(['renouvellement' => $renouvellement]);

        $validator = Validator::make($request->all(), [
            'autorisation_anterieur' => 'nullable',
            'renouvellement' => 'nullable',
            'raison_sociale' => 'required|string',
            'nom_commercial' => 'required|string',
            'siret' => 'required|string',
            'adresse' => 'required|string',
            'responsable_nom' => 'required|string',
            'responsable_prenom' => 'required|string',
            'responsable_email' => 'required|email',
            'responsable_telephone' => 'required|string',
            'responsable_fonction' => 'required|string',
            'activite_description' => 'required|string',
            'nombre_personnes' => 'required|integer',
            'nombre_vehicules' => 'required|integer',
            'clients_denomination' => 'required|string',
            'extrait_kbis' => 'required|file',
            'attestations_clients' => 'required|file', // Renommé Mandat sur le front
            'formulaire_surete' => 'required|file', // Renommé Référent sûreté sur le front
            'agrement_prefectoral' => 'nullable|file', // Supprimé sur le front
            'contrat_iata' => 'nullable|file', // Renommmé Demande AAO sur le front
            'cta' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            \Log::error('Erreurs de validation pour la demande d\'activité', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
                'user_id' => $user->id
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {

            // Stocker les fichiers
            $data['extrait_kbis_path'] = $request->file('extrait_kbis')->store('activity_requests', 'public');
            $data['attestations_clients_path'] = $request->file('attestations_clients')->store('activity_requests', 'public');
            $data['formulaire_surete_path'] = $request->file('formulaire_surete')->store('activity_requests', 'public');
            
            if ($request->hasFile('agrement_prefectoral')) {
                $data['agrement_prefectoral_path'] = $request->file('agrement_prefectoral')->store('activity_requests', 'public');
            }
            if ($request->hasFile('contrat_iata')) {
                $data['contrat_iata_path'] = $request->file('contrat_iata')->store('activity_requests', 'public');
            }
            if ($request->hasFile('cta')) {
                $data['cta_path'] = $request->file('cta')->store('activity_requests', 'public');
            }
    
            $data['user_id'] = $user->id;
            $data['created_by'] = $user->id;
            $data['status'] = 'pending';
            $data['pending_at'] = now();
    
            $activityRequest = ActivityRequest::create($data);
    
            // Envoyer l'email de notification
            try {
                Mail::to(config('mail.admin_address', 'admin@example.com'))
                    ->send(new ActivityRequestCreated($activityRequest));
    
                // Envoyer également un email de confirmation au demandeur
                Mail::to($data['responsable_email'])
                    ->send(new ActivityRequestConfirmation($activityRequest));
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas interrompre le processus
                \Log::error('Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
            }
    
            return response()->json([
                'message' => 'Demande d\'activité créée avec succès',
                'request' => $activityRequest
            ], 201);

        } catch (\Exception $e) {
        \Log::error('Erreur lors de la création de la demande d\'activité: ' . $e->getMessage());

        return response()->json([
            'message' => 'Erreur lors de la création de la demande',
            'error' => $e->getMessage()
        ], 500);
    }
    

    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $activityRequest = ActivityRequest::findOrFail($id);
        
        // Récupérer l'ancien statut pour vérifier s'il y a eu un changement
        $oldStatus = $activityRequest->status;
        
        // Mettre à jour le statut
        $activityRequest->status = $request->status;

        $timestampField = $request->status . '_at';
        $activityRequest->$timestampField = now();

        $activityRequest->save();
        
        // Envoyer un email de notification si le statut a changé
        if ($oldStatus !== $request->status) {
            try {
                // Notifier le demandeur du changement de statut
                Mail::to($activityRequest->responsable_email)
                    ->send(new ActivityRequestStatusUpdated($activityRequest));
                
                // Vous pourriez également vouloir notifier les administrateurs
                // Mail::to(config('mail.admin_address', 'admin@example.com'))
                //     ->send(new ActivityRequestStatusUpdated($activityRequest, true)); // Version admin
                
                \Log::info('Email de notification de changement de statut envoyé à ' . $activityRequest->responsable_email);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'envoi de l\'email de notification de statut: ' . $e->getMessage());
            }
        }

        return response()->json(['request' => $activityRequest]);
    }

    public function getDrafts(Request $request)
    {
        $user = $request->user();

        // Si l'utilisateur est admin, récupérer tous les brouillons
        if ($user->role === 'admin' || $user->role === 'sadmin') {
            $drafts = ActivityRequest::where('status', 'draft')->with('user')->latest()->get();
        } 
        else {
            // Sinon, récupérer uniquement les brouillons de l'utilisateur
            $drafts = ActivityRequest::where('status', 'draft')
                ->where('user_id', $user->id)
                ->latest()
                ->get();
        }

        return response()->json([
            'drafts' => $drafts
        ]);
    }

    public function storeDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'renouvellement' => 'nullable|boolean',
            'autorisation_anterieur' => 'nullable|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'nom_commercial' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'responsable_nom' => 'nullable|string|max:255',
            'responsable_prenom' => 'nullable|string|max:255',
            'responsable_email' => 'nullable|email|max:255',
            'responsable_telephone' => 'nullable|string|max:20',
            'responsable_fonction' => 'nullable|string|max:255',
            'activite_description' => 'nullable|string',
            'nombre_personnes' => 'nullable|integer',
            'nombre_vehicules' => 'nullable|integer',
            'clients_denomination' => 'nullable|string',
            'extrait_kbis' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'attestations_clients' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'formulaire_surete' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'agrement_prefectoral' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'contrat_iata' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'cta' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'draft_id' => 'nullable|integer|exists:activity_requests,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $draftData = [
                'user_id' => auth()->id(),
                'status' => 'draft',
                'draft_at' => now(),
                'created_by' => auth()->id()
            ];

            // Traitement des champs textuels
            $textFields = [
                'renouvellement', 'autorisation_anterieur', 'raison_sociale', 
                'nom_commercial', 'siret', 'adresse', 'responsable_nom', 
                'responsable_prenom', 'responsable_email', 'responsable_telephone',
                'responsable_fonction', 'activite_description', 'nombre_personnes',
                'nombre_vehicules', 'clients_denomination'
            ];

            foreach ($textFields as $field) {
                if ($request->has($field) && $request->input($field) !== null) {
                    $draftData[$field] = $request->input($field);
                }
            }

            // Traitement des fichiers
            $fileFields = [
                'extrait_kbis' => 'extrait_kbis_path',
                'attestations_clients' => 'attestations_clients_path',
                'formulaire_surete' => 'formulaire_surete_path',
                'agrement_prefectoral' => 'agrement_prefectoral_path',
                'contrat_iata' => 'contrat_iata_path',
                'cta' => 'cta_path'
            ];

            foreach ($fileFields as $field => $dbField) {
                if ($request->hasFile($field)) {
                    $draftData[$dbField] = $request->file($field)->store('activity_requests', 'public');
                }
            }

            // Mise à jour ou création du brouillon
            $draftId = $request->input('draft_id');
            
            if ($draftId) {
                $activityRequest = ActivityRequest::findOrFail($draftId);
                
                // Vérification des permissions
                if ($activityRequest->status !== 'draft' || $activityRequest->user_id !== auth()->id()) {
                    return response()->json([
                        'message' => 'Vous ne pouvez pas modifier cette demande'
                    ], 403);
                }
                
                $activityRequest->update($draftData);
            } else {
                $activityRequest = ActivityRequest::create($draftData);
            }

            return response()->json([
                'message' => 'Brouillon enregistré avec succès',
                'draft' => $activityRequest
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du brouillon: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur lors de la création du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitDraft(Request $request, $id)
    {
        $user = auth()->user();
        
        $draft = ActivityRequest::findOrFail($id);
        
        if ($draft->status !== 'draft' || $draft->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas soumettre cette demande'
            ], 403);
        }

        $validator = Validator::make($draft->toArray(), [
            'raison_sociale' => 'required|string|max:255',
            'nom_commercial' => 'required|string|max:255',
            'siret' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'responsable_nom' => 'required|string|max:255',
            'responsable_prenom' => 'required|string|max:255',
            'responsable_email' => 'required|email|max:255',
            'responsable_telephone' => 'required|string|max:20',
            'responsable_fonction' => 'required|string|max:255',
            'activite_description' => 'required|string',
            'nombre_personnes' => 'required|integer|min:1',
            'nombre_vehicules' => 'required|integer|min:1',
            'clients_denomination' => 'required|string',
            'extrait_kbis_path' => 'required|string',
            'attestations_clients_path' => 'required|string',
            'formulaire_surete_path' => 'required|string',
            'cta_path' => 'required|string',
        ]);

        if ($draft->renouvellement) {
            $additionalValidator = Validator::make($draft->toArray(), [
                'autorisation_anterieur' => 'required|string|max:255'
            ]);
            
            if ($additionalValidator->fails()) {
                return response()->json([
                    'message' => 'Numéro d\'autorisation antérieure requis pour un renouvellement',
                    'errors' => $additionalValidator->errors()
                ], 422);
            }
        }

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formulaire incomplet',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $draft->update([
                'user_id' => $user->id,
                'status' => 'pending',
                'pending_at' => now(),
            ]);

            return response()->json([
                'message' => 'Demande soumise avec succès',
                'request' => $draft
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la soumission du brouillon: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur lors de la soumission de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}