<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Reply;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function getComments($badgeRequestId)
    {
        $comments = Comment::where('badge_request_id', $badgeRequestId)
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
                'badge_request_id' => 'required|exists:badge_requests,id',
            ]);

            // Création du commentaire
            $comment = Comment::create([
                'content' => $validated['content'],
                'badge_request_id' => $validated['badge_request_id'],
                'user_id' => auth()->id(),
            ]);

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

    public function update(Request $request, Comment $comment)
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

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }

    public function storeReply(Request $request, Comment $comment)
    {
        try {
            // Récupérer et décoder le corps de la requête
            $data = json_decode($request->getContent(), true);

            // Créer une nouvelle instance de Request avec les données décodées
            $request->replace($data);

            $validated = $request->validate([
                'content' => 'required|string',
            ]);

            $reply = Reply::create([
                'content' => $validated['content'],
                'comment_id' => $comment->id,
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

    public function updateReply(Request $request, Reply $reply)
    {
        $this->authorize('update', $reply);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply->update($validated);

        return response()->json($reply->load('user'));
    }

    public function destroyReply(Reply $reply)
    {
        $this->authorize('delete', $reply);

        $reply->delete();

        return response()->json(['message' => 'Reply deleted']);
    }
}
