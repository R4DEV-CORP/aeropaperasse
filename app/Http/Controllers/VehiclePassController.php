<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\VehiclePass;

class VehiclePassController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Si l'utilisateur est admin, récupérer toutes les demandes, sauf les brouillons
        if ($user->role === 'admin' || $user->role === 'sadmin') {
            $requests = VehiclePass::where('status', '!=', 'draft')->latest()->get();
            $draftCount = VehiclePass::where('status', 'draft')->count();
        }
        // Sinon, récupérer les demandes liées à l'utilisateur, sauf les brouillons
        else {
            $requests = VehiclePass::where('status', '!=', 'draft')
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $draftCount = VehiclePass::where('status', 'draft')
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
            'user' => $user,
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

        $validator = Validator::make($request->all(), [
            'nom_entreprise' => 'required|string|max:255',
            'siret' => 'required|string|size:14',
            'adresse' => 'required|string|max:500',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'tampon_entreprise' => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'aeroport' => 'required|string|in:CDG,ORLY,BOURGET',
            'immatriculation' => 'required|string|max:20',
            'marque_vehicule' => 'required|string|max:100',
            'carte_grise' => 'required|file|mimes:pdf,jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            \Log::error('Erreur de validation lors de la création du laisser-passer de véhicule', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all(),
                'user_id' => $user->id,
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Stockage des fichiers
            $tamponPath = null;
            $carteGrisePath = null;

            if ($request->hasFile('tampon_entreprise')) {
                $tamponPath = $request->file('tampon_entreprise')->store('vehicle_passes/tampons', 'public');
            }

            if ($request->hasFile('carte_grise')) {
                $carteGrisePath = $request->file('carte_grise')->store('vehicle_passes/cartes_grises', 'public');
            }

            // Préparation des données et création de la demande
            $data = [
                'user_id' => $user->id,
                'nom_entreprise' => $request->nom_entreprise,
                'siret' => $request->siret,
                'adresse' => $request->adresse,
                'code_postal' => $request->code_postal,
                'ville' => $request->ville,
                'tampon_entreprise' => $tamponPath,
                'aeroport' => $request->aeroport,
                'immatriculation' => strtoupper($request->immatriculation), // Normalisation en majuscules
                'marque_vehicule' => $request->marque_vehicule,
                'carte_grise_path' => $carteGrisePath,
                'status' => 'pending',
                'previous_status' => null,
            ];

            $vehiclePass = VehiclePass::create($data);

            return response()->json([
                'message' => 'Demande de laisser-passer véhicule créée avec succès',
                'vehicle_pass' => $vehiclePass
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la demande de laisser-passer véhicule: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'immatriculation' => $request->immatriculation ?? 'N/A'
            ]);

            // Nettoyage des fichiers en cas d'erreur
            if (isset($tamponPath) && $tamponPath) {
                Storage::disk('public')->delete($tamponPath);
            }
            if (isset($carteGrisePath) && $carteGrisePath) {
                Storage::disk('public')->delete($carteGrisePath);
            }

            return response()->json([
                'message' => 'Erreur lors de la création de la demande de laisser-passer véhicule',
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
            \Log::error('Validation échouée:', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehiclePass = VehiclePass::findOrFail($id);

        $vehiclePass->status = $request->status;

        $vehiclePass->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'request' => $vehiclePass->fresh()
        ]);
    }

    public function getDrafts(Request $request)
    {
        $user = $request->user();

        // Si l'utilisateur est admin, récupérer tous les brouillons
        if ($user->role === 'admin' || $user->role === 'sadmin') {
            $drafts = VehiclePass::where('status', 'draft')->with('user')->latest()->get();
        }
        else {
            // Sinon, récupérer uniquement les brouillons de l'utilisateur
            $drafts = VehiclePass::where('status', 'draft')
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
            'nom_entreprise' => 'nullable|string|max:255',
            'siret' => 'nullable|string|size:14',
            'adresse' => 'nullable|string|max:500',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:255',
            'tampon_entreprise' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'aeroport' => 'nullable|string|in:CDG,ORLY,BOURGET',
            'immatriculation' => 'nullable|string|max:20',
            'marque_vehicule' => 'nullable|string|max:100',
            'carte_grise' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $draftData = [
                'user_id' => auth()->id(),
                'status' => 'draft',
                'draft_at' => now(),
            ];

            // Traitement des champs textuels
            $textFields = [
                'nom_entreprise', 'siret', 'adresse', 'code_postal', 'ville',
                'aeroport', 'immatriculation', 'marque_vehicule'
            ];

            foreach ($textFields as $field) {
                if ($request->has($field) && $request->input($field) !== null) {
                    $value = $request->input($field);
                    // Normalisation de l'immatriculation en majuscules si présente
                    if ($field === 'immatriculation' && !empty($value)) {
                        $value = strtoupper($value);
                    }
                    $draftData[$field] = $value;
                }
            }

            // Traitement des fichiers
            $fileFields = [
                'tampon_entreprise' => 'vehicle_passes/tampons',
                'carte_grise' => 'vehicle_passes/cartes_grises'
            ];

            foreach ($fileFields as $field => $folder) {
                if ($request->hasFile($field)) {
                    $draftData[$field === 'carte_grise' ? 'carte_grise_path' : $field] =
                        $request->file($field)->store($folder, 'public');
                }
            }

            // Création ou mise à jour du brouillon
            $draftId = $request->input('draft_id');

            if ($draftId) {
                $vehiclePass = VehiclePass::findOrFail($draftId);

                // Vérification des permissions
                if ($vehiclePass->status !== 'draft' || $vehiclePass->user_id !== auth()->id()) {
                    return response()->json([
                        'message' => 'Vous ne pouvez pas modifier cette demande'
                    ], 403);
                }

                $vehiclePass->update($draftData);
            } else {
                $vehiclePass = VehiclePass::create($draftData);
            }

            return response()->json([
                'message' => 'Brouillon enregistré avec succès',
                'draft' => $vehiclePass
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du brouillon de laisser-passer véhicule: ' . $e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la création du brouillon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submitDraft(Request $request, $id)
    {
        $user = auth()->user();

        $draft = VehiclePass::findOrFail($id);

        if ($draft->status !== 'draft' || $draft->user_id !== $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas soumettre ce brouillon'
            ], 403);
        }

        // Validation des champs requis pour la soumission
        $validator = Validator::make($draft->toArray(), [
            'nom_entreprise' => 'required|string|max:255',
            'siret' => 'required|string|size:14',
            'adresse' => 'required|string|max:500',
            'code_postal' => 'required|string|max:10',
            'ville' => 'required|string|max:255',
            'tampon_entreprise' => 'required|string',
            'aeroport' => 'required|string|in:CDG,ORLY,BOURGET',
            'immatriculation' => 'required|string|max:20',
            'marque_vehicule' => 'required|string|max:100',
            'carte_grise_path' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Formulaire incomplet - Veuillez compléter tous les champs requis',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validation supplémentaire de l'unicité de l'immatriculation
        $existingVehicle = VehiclePass::where('immatriculation', $draft->immatriculation)
            ->where('id', '!=', $draft->id)
            ->where('status', '!=', 'rejected')
            ->first();

        if ($existingVehicle) {
            return response()->json([
                'message' => 'Cette immatriculation est déjà enregistrée dans le système',
                'errors' => [
                    'immatriculation' => ['Cette immatriculation existe déjà']
                ]
            ], 422);
        }

        // Vérification que les fichiers existent toujours sur le serveur
        $filesToCheck = [
            'tampon_entreprise' => 'Tampon d\'entreprise',
            'carte_grise_path' => 'Carte grise'
        ];

        foreach ($filesToCheck as $field => $label) {
            if ($draft->$field && !Storage::disk('public')->exists($draft->$field)) {
                return response()->json([
                    'message' => "Le fichier {$label} n'existe plus. Veuillez le télécharger à nouveau.",
                    'errors' => [
                        $field => ["Le fichier {$label} est manquant"]
                    ]
                ], 422);
            }
        }

        try {
            // Mise à jour du statut du brouillon vers pending
            $draft->update([
                'status' => 'pending',
                'previous_status' => 'draft',
                'immatriculation' => strtoupper($draft->immatriculation),
                'draft_at' => null,
            ]);

            // Envoi des notifications par mail

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la soumission du brouillon de laisser-passer véhicule: ' . $e->getMessage(), [
                'draft_id' => $id,
                'user_id' => $user->id,
                'immatriculation' => $draft->immatriculation ?? 'N/A'
            ]);

            return response()->json([
                'message' => 'Erreur lors de la soumission de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
