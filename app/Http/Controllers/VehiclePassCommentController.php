<?php

namespace App\Http\Controllers;

use App\Models\VehiclePassComment;
use App\Models\VehiclePassReply;
use Illuminate\Http\Request;

class VehiclePassCommentController extends Controller
{
    public function getComments($vehiclePassId)
    {
        $comments = VehiclePassComment::where('vehicle_pass_id', $vehiclePassId)
            ->with(['user', 'replies.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request)
    {
        try {
            // Récupérer et décoder le corps de la requête
            $data = json_decode($request->getContent(), true);

            // Créer une nouvelle instance de Request avec les données décodées
            $request->replace($data);

            // Validation
            $validated = $request->validate([
                'content' => 'required|string',
                'vehicle_pass_id' => 'required|exists:vehicle_passes,id'
            ]);

            // Création du commentaire
            $comment = VehiclePassComment::create([
                'content' => $validated['content'],
                'vehicle_pass_id' => $validated['vehicle_pass_id'],
                'user_id' => auth()->id()
            ]);

            // Charger les relations et retourner
            return response()->json(
                $comment->load(['user', 'replies']),
                201
            );

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création du commentaire de laisser-passer véhicule:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création du commentaire',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, VehiclePassComment $comment)
    {
        try {
            // Vérifier si l'utilisateur est autorisé à modifier ce commentaire
            if ($comment->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Non autorisé à modifier ce commentaire'
                ], 403);
            }

            $data = json_decode($request->getContent(), true);
            $request->replace($data);

            $validated = $request->validate([
                'content' => 'required|string'
            ]);

            $comment->update([
                'content' => $validated['content']
            ]);

            return response()->json($comment->load(['user', 'replies']));

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification du commentaire',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(VehiclePassComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé avec succès']);
    }

    public function storeReply(Request $request, VehiclePassComment $comment)
    {
        try {
            // Récupérer et décoder le corps de la requête
            $data = json_decode($request->getContent(), true);

            // Créer une nouvelle instance de Request avec les données décodées
            $request->replace($data);

            $validated = $request->validate([
                'content' => 'required|string'
            ]);

            $reply = VehiclePassReply::create([
                'content' => $validated['content'],
                'vehicle_pass_comment_id' => $comment->id,
                'user_id' => auth()->id()
            ]);

            return response()->json(
                $reply->load('user'),
                201
            );

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la réponse au commentaire de laisser-passer véhicule:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création de la réponse',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function updateReply(Request $request, VehiclePassReply $reply)
    {
        $this->authorize('update', $reply);

        $data = json_decode($request->getContent(), true);
        $request->replace($data);

        $validated = $request->validate([
            'content' => 'required|string'
        ]);

        $reply->update($validated);

        return response()->json($reply->load('user'));
    }

    public function destroyReply(VehiclePassReply $reply)
    {
        $this->authorize('delete', $reply);

        $reply->delete();

        return response()->json(['message' => 'Réponse supprimée avec succès']);
    }
}
