<?php

namespace App\Http\Controllers;

use App\Mail\ActivityCommentCreated;
use App\Models\ActivityComment;
use App\Models\ActivityRequest;
use App\Models\ReplyActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ActivityCommentController extends Controller
{
    public function getComments($badgeRequestId)
    {
        $comments = ActivityComment::where('activity_request_id', $badgeRequestId)
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
                'activity_request_id' => 'required|exists:activity_requests,id',
            ]);

            // Récupérer la demande d'activité
            $activityRequest = ActivityRequest::findOrFail($validated['activity_request_id']);

            // Création du commentaire
            $comment = ActivityComment::create([
                'content' => $validated['content'],
                'activity_request_id' => $validated['activity_request_id'],
                'user_id' => auth()->id(),
            ]);

            // Envoyer un email au créateur de la demande si ce n'est pas lui qui a commenté
            $this->sendCommentNotification($activityRequest, $comment);

            // Charger les relations et retourner
            return response()->json(
                $comment->load(['user', 'replies']),
                201
            );

        } catch (\Exception $e) {
            \Log::error('Erreur:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création du commentaire',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, ActivityComment $comment)
    {
        try {
            // Vérifier si l'utilisateur est autorisé à modifier ce commentaire
            if ($comment->user_id !== auth()->id()) {
                return response()->json([
                    'message' => 'Non autorisé à modifier ce commentaire',
                ], 403);
            }

            $data = json_decode($request->getContent(), true);
            $request->replace($data);

            $validated = $request->validate([
                'content' => 'required|string',
            ]);

            $comment->update([
                'content' => $validated['content'],
            ]);

            return response()->json($comment->load(['user', 'replies']));

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la modification du commentaire',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(ActivityComment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }

    public function storeReply(Request $request, ActivityComment $comment)
    {
        try {
            // Récupérer et décoder le corps de la requête
            $data = json_decode($request->getContent(), true);

            // Créer une nouvelle instance de Request avec les données décodées
            $request->replace($data);

            $validated = $request->validate([
                'content' => 'required|string',
            ]);

            $reply = ReplyActivity::create([
                'content' => $validated['content'],
                'activity_comment_id' => $comment->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json(
                $reply->load('user'),
                201
            );

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de la réponse:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création de la réponse',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function updateReply(Request $request, ReplyActivity $reply)
    {
        $this->authorize('update', $reply);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply->update($validated);

        return response()->json($reply->load('user'));
    }

    public function destroyReply(ReplyActivity $reply)
    {
        $this->authorize('delete', $reply);

        $reply->delete();

        return response()->json(['message' => 'Reply deleted']);
    }

    /**
     * Envoie une notification par email au créateur de la demande d'activité
     * si ce n'est pas lui qui a ajouté le commentaire
     */
    private function sendCommentNotification(ActivityRequest $activityRequest, ActivityComment $comment): void
    {
        try {
            $currentUserId = auth()->id();
            $creator = $activityRequest->creator;

            // Ne pas envoyer d'email si :
            // - Le créateur n'existe pas
            // - Le créateur est la même personne que l'auteur du commentaire
            // - Le créateur n'a pas d'email
            if (! $creator || $creator->id === $currentUserId || ! $creator->email) {
                return;
            }

            Mail::to($creator->email)->send(new ActivityCommentCreated($activityRequest, $comment));
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi de l\'email de notification de commentaire:', [
                'activity_request_id' => $activityRequest->id,
                'comment_id' => $comment->id,
                'error' => $e->getMessage(),
            ]);
            // Ne pas faire échouer la création du commentaire si l'email ne fonctionne pas
        }
    }
}
