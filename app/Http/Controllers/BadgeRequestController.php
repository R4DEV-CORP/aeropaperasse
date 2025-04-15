<?php

namespace App\Http\Controllers;

use App\Models\BadgeRequest;
use App\Models\User;
use App\Models\Client;
use App\Http\Controllers\BadgeRequestRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class BadgeRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(BadgeRequest::class);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $client_id = $user->client_id;

        // Si l'utilisateur est admin, récupérer toutes les demandes, sauf les brouillons
        if ($user->role === 'admin' || $user->role === 'sadmin' ) {
            $requests = BadgeRequest::where('status', '!=', 'draft')->with('user')->latest()->get();
            $draftCount = BadgeRequest::where('status', 'draft')->count();
        }
        // Sinon, récupérer les demandes liées au client de l'utilisateur, sauf les brouillons
        else {
            $requests = BadgeRequest::where('status', '!=', 'draft')
                ->whereHas('user', function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->with('user')
                ->latest()
                ->get();
            
            $draftCount = BadgeRequest::where('status', 'draft')
                ->whereHas('user', function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->count();
        }

        $stats = [
            'total' => $requests->count(),
            'pending_rem' => $requests->where('status', 'pending_rem')->count(),
            'rejected_rem' => $requests->where('status', 'rejected_rem')->count(),
            'pending_adp' => $requests->where('status', 'pending_adp')->count(),
            'approved_adp' => $requests->where('status', 'approved_adp')->count(),
            'rejected_adp' => $requests->where('status', 'rejected_adp')->count(),
            'pending_fabrication' => $requests->where('status', 'pending_fabrication')->count(),
            'ready_for_delivery' => $requests->where('status', 'ready_for_delivery')->count(),
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

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'photoIdentite' => 'required|file|mimes:jpeg,png,jpg|max:2048',
            'pieceIdentite' => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'autorisationActivite' => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'certificatFormation' => 'file|mimes:pdf,jpeg,png,jpg|max:2048',
            'est_habilitation' => 'boolean',
            'documentFor' => 'required|file|mimes:pdf,xlsx,xls,jpeg,png,jpg|max:2048',
            'facture' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'airport' => 'required|string|in:aeroportOrly,aeroportBourget,aeroportCDG',
            'client_id' => 'required|exists:clients,id',
            'reject_reason' => 'nullable|string|max:500',
        ]);
        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = 'pending_rem';

        if ($validator->fails()) {
            \Log::error('Erreurs de validation pour le rôle sclient', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
                'user_role' => $request->user()->role
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                $password = Str::random(12);
                
                $user = User::create([
                    'name' => $request->prenom . ' ' . $request->nom,
                    'email' => $request->email,
                    'password' => Hash::make($password),
                    'role' => 'client',
                    'client_id' => $request->client_id,
                    'function' => '',
                    'two_factor_enabled' => true
                ]);
            }

            $photoPath = $request->file('photoIdentite')->store('photos', 'public');
            $pieceIdentitePath = $request->file('pieceIdentite')->store('pieces_identite', 'public');
            $autorisationPath = $request->file('autorisationActivite')->store('autorisations', 'public');
            
            $certificatPath = null;
            if ($request->hasFile('certificatFormation')) {
                $certificatPath = $request->file('certificatFormation')->store('certificats', 'public');
            }

            $documentForPath = $request->file('documentFor')->store('documents_for', 'public');
            \Log::info('Document FOR path', ['path' => $documentForPath]);
            
            $facturePath = null;
            if ($request->hasFile('facture')) {
                $facturePath = $request->file('facture')->store('factures', 'public');
            }

            $badgeRequest = BadgeRequest::create([
                'user_id' => $user->id,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'telephone' => $request->telephone,
                'photoIdentite' => $photoPath,
                'pieceIdentite' => $pieceIdentitePath,
                'autorisationActivite' => $autorisationPath,
                'certificatFormation' => $certificatPath,
                'est_habilitation' => $request->boolean('est_habilitation'),
                'documentFor' => $documentForPath,
                'facture' => $facturePath,
                'airport' => $request->airport,
                'client_id' => $request->client_id,
                'status' => 'pending_rem',
                'pending_rem_at' => now(),
                'reject_reason' => $request->reject_reason,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Demande de badge créée avec succès',
                'request' => $badgeRequest
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la demande: ' . $e->getMessage());
            
            if (isset($photoPath)) Storage::disk('public')->delete($photoPath);
            if (isset($pieceIdentitePath)) Storage::disk('public')->delete($pieceIdentitePath);
            if (isset($autorisationPath)) Storage::disk('public')->delete($autorisationPath);
            if (isset($certificatPath)) Storage::disk('public')->delete($certificatPath);
            if (isset($documentForPath)) Storage::disk('public')->delete($documentForPath);
            if (isset($facturePath)) Storage::disk('public')->delete($facturePath);

            return response()->json([
                'message' => 'Erreur lors de la création de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getApproved()
    {
        try {
            $approvedRequests = BadgeRequest::where('status', 'ready_for_delivery')
                ->whereDoesntHave('badge') // On exclut les demandes qui ont déjà un badge
                ->select('id', 'nom', 'prenom', 'email')
                ->get();

            return response()->json([
                'requests' => $approvedRequests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des demandes approuvées',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updateStatus(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending_rem,rejected_rem,pending_adp,approved_adp,rejected_adp,pending_fabrication,ready_for_delivery',
            'reject_reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation échouée:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $badgeRequest = BadgeRequest::findOrFail($id);
        
        $badgeRequest->status = $request->status;
                
        if ($request->has('reject_reason') && in_array($request->status, ['rejected_rem', 'rejected_adp'])) {
            $badgeRequest->reject_reason = $request->reject_reason;
        } else {
            \Log::info('Aucun motif de rejet trouvé ou conditions non remplies', [
                'has_reject_reason' => $request->has('reject_reason'),
                'status' => $request->status
            ]);
        }
        
        $timestampField = $request->status . '_at';
        $badgeRequest->$timestampField = now();

        $badgeRequest->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'request' => $badgeRequest->fresh()
        ]);
    }

    public function show($id)
    {
        $badgeRequest = BadgeRequest::with(['user', 'client'])->findOrFail($id);
        return response()->json(['request' => $badgeRequest]);
    }

    public function getDrafts(Request $request)
    {
        $user = $request->user();
        $client_id = $user->client_id;

        if ($user->role === 'admin' || $user->role === 'sadmin') {
            $drafts = BadgeRequest::where('status', 'draft')->with('user')->latest()->get();
        } 
        else {
            $drafts = BadgeRequest::where('status', 'draft')
                ->whereHas('user', function($query) use ($client_id) {
                    $query->where('client_id', $client_id);
                })
                ->with('user')
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
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'photoIdentite' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'pieceIdentite' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'autorisationActivite' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'certificatFormation' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'est_habilitation' => 'boolean',
            'documentFor' => 'nullable|file|mimes:pdf,xlsx,xls,jpeg,png,jpg|max:2048',
            'facture' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'airport' => 'nullable|string|in:aeroportOrly,aeroportBourget,aeroportCDG',
            'client_id' => 'nullable|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $draftData = [
                'user_id' => auth()->id(),
                'status' => 'draft',
                'draft_at' => now()
            ];

            foreach (['nom', 'prenom', 'email', 'telephone', 'airport', 'client_id'] as $field) {
                if ($request->has($field)) {
                    $draftData[$field] = $request->$field;
                }
            }

            if ($request->has('est_habilitation')) {
                $draftData['est_habilitation'] = $request->boolean('est_habilitation');
            }

            $fileFields = [
                'photoIdentite' => 'photos',
                'pieceIdentite' => 'pieces_identite',
                'autorisationActivite' => 'autorisations',
                'certificatFormation' => 'certificats',
                'documentFor' => 'documents_for',
                'facture' => 'factures'
            ];

            foreach ($fileFields as $field => $folder) {
                if ($request->hasFile($field)) {
                    $draftData[$field] = $request->file($field)->store($folder, 'public');
                }
            }

            $badgeRequestId = $request->input('draft_id');
            
            if ($badgeRequestId) {
                $badgeRequest = BadgeRequest::findOrFail($badgeRequestId);
                
                if ($badgeRequest->status !== 'draft' || $badgeRequest->user_id !== auth()->id()) {
                    return response()->json([
                        'message' => 'Vous ne pouvez pas modifier cette demande'
                    ], 403);
                }
                
                $badgeRequest->update($draftData);
            } else {
                $badgeRequest = BadgeRequest::create($draftData);
            }

            return response()->json([
                'message' => 'Brouillon enregistré avec succès',
                'draft' => $badgeRequest
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
        $draft = BadgeRequest::findOrFail($id);
        
        if ($draft->status !== 'draft' || $draft->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Vous ne pouvez pas soumettre cette demande'
            ], 403);
        }

        $validator = Validator::make($draft->toArray(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:20',
            'photoIdentite' => 'required|string',
            'pieceIdentite' => 'required|string',
            'autorisationActivite' => 'required|string',
            'documentFor' => 'required|string',
            'airport' => 'required|string|in:aeroportOrly,aeroportBourget,aeroportCDG',
            'client_id' => 'required|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formulaire incomplet',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::where('email', $draft->email)->first();
            if (!$user) {
                $password = Str::random(12);
                
                $user = User::create([
                    'name' => $draft->prenom . ' ' . $draft->nom,
                    'email' => $draft->email,
                    'password' => Hash::make($password),
                    'role' => 'client',
                    'client_id' => $draft->client_id,
                    'function' => 'Utilisateur badge',
                    'two_factor_enabled' => true
                ]);
            }

            $draft->update([
                'user_id' => $user->id,
                'status' => 'pending_rem',
                'pending_rem_at' => now()
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

    public function deleteDraft(Request $request, $id)
    {
        try {
            $draft = BadgeRequest::findOrFail($id);

            if ($draft->status !== 'draft') {
                return response()->json([
                    'message' => 'Seuls les brouillons peuvent être supprimés'
                ], 403);
            }

            // Vérifier les permissions:
            // - L'utilisateur qui a créé le brouillon peut le supprimer
            // - Les admin/sadmin peuvent supprimer n'importe quel brouillon
            $user = $request->user();
            if ($draft->user_id !== $user->id && !in_array($user->role, ['admin', 'sadmin'])) {
                return response()->json([
                    'message' => 'Vous n\'êtes pas autorisé à supprimer ce brouillon'
                ], 403);
            }

            $fileFields = [
                'photoIdentite', 'pieceIdentite', 'autorisationActivite', 
                'certificatFormation', 'documentFor', 'facture'
            ];
    
            foreach ($fileFields as $field) {
                if (!empty($draft->$field)) {
                    Storage::disk('public')->delete($draft->$field);
                }
            }

            $draft->delete();

            return response()->json([
                'message' => 'Brouillon supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du brouillon: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erreur lors de la suppression du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}