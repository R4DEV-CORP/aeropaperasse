<?php

namespace App\Http\Controllers;

use App\Mail\NewMessage;
use App\Models\Discussion;
use App\Models\DiscussionFile;
use App\Models\DiscussionReadStatus;
use App\Models\MessageComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageCommentController extends Controller
{
    public function store(Request $request, Discussion $discussion)
    {
        try {
            $currentUserId = auth()->id();

            $request->validate([
                'content' => 'required|string',
                'parent_id' => 'nullable|exists:message_comments,id',
                'files' => 'nullable|array',
                'files.*' => 'nullable|file|max:10240',
            ]);

            \DB::beginTransaction();

            $comment = MessageComment::create([
                'content' => $request->content,
                'user_id' => $currentUserId,
                'discussion_id' => $discussion->id,
                'parent_id' => $request->parent_id,
            ]);

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('comment-files', 'public');
                    DiscussionFile::create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'message_comment_id' => $comment->id,
                    ]);
                }
            }

            $discussion->update([
                'last_comment_user_id' => $currentUserId,
            ]);
            $discussion->markAsReadForUser($currentUserId);

            DiscussionReadStatus::where('discussion_id', $discussion->id)
                ->where('user_id', '!=', $currentUserId)
                ->delete();

            // Envoyer une notification par email
            $this->sendNewMessageNotification($discussion, $comment);

            \DB::commit();

            // Charger les relations nécessaires
            $comment->load(['user', 'files', 'responses.user', 'responses.files']);

            return response()->json([
                'message' => 'Commentaire créé avec succès',
                'comment' => $comment,
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur lors de la création du commentaire: '.$e->getMessage());

            return response()->json([
                'message' => 'Erreur lors de la création du commentaire',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Envoie une notification par email selon la logique suivante:
     * - Si c'est un nouveau message client (parent_id null): envoyer aux admin et sadmin
     * - Si c'est une réponse d'admin/sadmin: envoyer à l'auteur du message initial (client)
     * - Si c'est une réponse du client: envoyer aux admins/sadmins
     */
    private function sendNewMessageNotification(Discussion $discussion, MessageComment $comment)
    {
        $currentUser = auth()->user();
        $currentUserId = $currentUser->id;

        // Si c'est une réponse à un message existant
        if ($comment->parent_id) {
            // Vérifier qui est l'auteur de la réponse et à qui il faut envoyer la notification
            if ($currentUser->role === 'admin' || $currentUser->role === 'sadmin') {
                // Si la réponse vient d'un admin, envoyer à l'auteur de la discussion
                $discussionAuthor = User::find($discussion->user_id);
                if ($discussionAuthor && $discussionAuthor->id != $currentUserId && $discussionAuthor->email) {
                    Mail::to($discussionAuthor->email)->send(new NewMessage($discussion, $comment));
                }
            } else {
                // Si la réponse vient d'un client, envoyer aux admins/sadmins
                $admins = User::whereIn('role', ['admin', 'sadmin'])->get();
                foreach ($admins as $admin) {
                    if ($admin->id != $currentUserId && $admin->email) {
                        Mail::to($admin->email)->send(new NewMessage($discussion, $comment));
                    }
                }
            }
        }
        // Si c'est un nouveau message (pas une réponse)
        else {
            // Si le message vient d'un client, envoyer à tous les admins et super admins
            if ($currentUser->role !== 'admin' && $currentUser->role !== 'sadmin') {
                $admins = User::whereIn('role', ['admin', 'sadmin'])->get();

                foreach ($admins as $admin) {
                    if ($admin->email) {
                        Mail::to($admin->email)->send(new NewMessage($discussion, $comment));
                    }
                }
            }
            // Si le message vient d'un admin/sadmin, envoyer au client concerné
            else {
                $discussionAuthor = User::find($discussion->user_id);
                if ($discussionAuthor && $discussionAuthor->id != $currentUserId && $discussionAuthor->email) {
                    Mail::to($discussionAuthor->email)->send(new NewMessage($discussion, $comment));
                }
            }
        }
    }
}
