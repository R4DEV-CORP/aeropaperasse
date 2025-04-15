<?php

namespace App\Http\Controllers;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\BadgeRequest;

class BadgeController extends Controller
{
    public function index()
    {
        $badges = Badge::with(['badgeRequest', 'badgeRequest.user', 'badgeRequest.user.client'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($badge) {
                \Log::info('Badge ID: ' . $badge->id);
                \Log::info('BadgeRequest: ' . ($badge->badgeRequest ? 'exists' : 'null'));
                \Log::info('User: ' . ($badge->badgeRequest->user ?? 'null'));
                \Log::info('Client: ' . ($badge->badgeRequest->user->client ?? 'null'));
                return [
                    'id' => $badge->id,
                    'badgeNumber' => $badge->badge_number,
                    'holder' => [
                        'nom' => $badge->badgeRequest->nom,
                        'prenom' => $badge->badgeRequest->prenom,
                        'email' => $badge->badgeRequest->email,
                        'client' => $badge->badgeRequest->user->client->name ?? null,
                    ],
                    'status' => $badge->status,
                    'createdAt' => $badge->created_at,
                    'expiryDate' => $badge->expiry_date,
                    'returnedAt' => $badge->returned_at,
                    'returnDocument' => $badge->return_document ? Storage::url($badge->return_document) : null,
                ];
            });

        return response()->json([
            'badges' => $badges
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'badge_request_id' => 'required|exists:badge_requests,id',
            'badge_number' => 'required|string|unique:badges,badge_number',
            'expiry_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si la demande est approuvée
        $badgeRequest = BadgeRequest::find($request->badge_request_id);
        if ($badgeRequest->status !== 'ready_for_delivery') {
            return response()->json([
                'message' => 'La demande de badge doit être approuvée pour créer un badge'
            ], 422);
        }

        $badge = Badge::create([
            'badge_request_id' => $request->badge_request_id,
            'badge_number' => $request->badge_number,
            'status' => 'active',
            'expiry_date' => $request->expiry_date,
        ]);

        return response()->json([
            'message' => 'Badge créé avec succès',
            'badge' => $badge
        ], 201);
    }

    public function show(Badge $badge)
    {
        $badges = Badge::with(['badgeRequest', 'badgeRequest.user', 'badgeRequest.user.client']);
        
        return response()->json([
            'badge' => [
                'id' => $badge->id,
                'badgeNumber' => $badge->badge_number,
                'holder' => [
                    'nom' => $badge->badgeRequest->nom,
                    'prenom' => $badge->badgeRequest->prenom,
                    'email' => $badge->badgeRequest->email,
                    'client' => $badge->badgeRequest->user->client->name ?? null,
                ],
                'status' => $badge->status,
                'createdAt' => $badge->created_at,
                'expiryDate' => $badge->expiry_date,
                'returnedAt' => $badge->returned_at,
                'returnDocument' => $badge->return_document ? Storage::url($badge->return_document) : null,
            ]
        ]);
    }

    public function return(Request $request, Badge $badge)
    {
        // Valider que le badge peut être restitué
        if ($badge->status !== 'active') {
            return response()->json([
                'message' => 'Ce badge ne peut pas être restitué dans son état actuel.'
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
            'return_document' => $returnDocument
        ]);

        return response()->json([
            'message' => 'Badge restitué avec succès',
            'badge' => $badge
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $badge = Badge::find($id);
        
        if (!$badge) {
            return response()->json(['message' => 'Badge non trouvé'], 404);
        }

        $badge->status = $request->status;
        $badge->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'badge' => $badge
        ]);
    }
}