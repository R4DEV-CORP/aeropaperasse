<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    // public function index()
    // {
    //     return Client::with('users')->get();
    // }

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
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
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

            'kbis_document'=> 'required|file|mimes:pdf|max:2048',

            'hr_contact_name' => 'required|string|max:255',
            'hr_contact_email' => 'required|email|max:255',
            'hr_contact_phone' => 'required|string|max:255',

            'badge_limit' => 'required|integer|min:1|max:1000',
            'vehicle_pass_limit' => 'required|integer|min:1|max:1000',
        ]);

        try {

            $clientData = $request->except([
                'safety_document',
                'security_document',
                'kbis_document'
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
                'error' => $e->getMessage()
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
            'vehicle_pass_limit' => 'required|integer|min:1|max:1000',
        ]);

        try {

            $clientData = $request->except([
                'safety_document',
                'security_document',
                'kbis_document'
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
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Client $client)
    {
        try {
            // Vérifier si le client a des utilisateurs
            if ($client->users()->count() > 0) {
                return response()->json([
                    'message' => 'Impossible de supprimer un client qui a des utilisateurs'
                ], 400);
            }

            $client->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression du client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadDocument(Request $request, Client $client)
    {
        $documentType = $request->input('type');

        $documentPath = null;
        $fileName = "";

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

        if (!$documentPath) {
            return response()->json(['success' => false, 'message' => 'Document non défini'], 404);
        }

        if (!Storage::disk('public')->exists($documentPath)) {
            return response()->json(['success' => false, 'message' => "Document non trouvé: {$documentPath}"], 404);
        }

        return response()->json([
            'success' => true,
            'path' => $documentPath,
            'url' => Storage::disk('public')->url($documentPath),
            'filename' => $fileName
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
                'can_create' => $client->canCreateBadge()
            ];

            $vehiclePassInfo = [
                'used' => $client->active_vehicle_passes_count,
                'total' => $client->vehicle_pass_limit,
                'remaining' => max(0, $client->vehicle_pass_limit - $client->active_vehicle_passes_count),
                'percentage' => $client->vehicle_pass_limit > 0 ? round(($client->active_vehicle_passes_count / $client->vehicle_pass_limit) * 100, 1) : 0,
                'can_create' => $client->canCreateVehiclePass()
            ];

            return response()->json([
                'badge_quota' => $badgeInfo,
                'vehicle_pass_quota' => $vehiclePassInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des quotas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
