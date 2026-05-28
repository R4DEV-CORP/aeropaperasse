<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Discussion;
use App\Models\DiscussionFile;
use Illuminate\Http\Request;

class DiscussionController extends Controller
{
    public function index()
    {
        // Pour filtrer par l'utilisateur connecté
        $user = auth()->user();
        $userId = $user->id;

        $discussions = Discussion::with(['user', 'files', 'messageComments' => function ($query) {
            $query->whereNull('parent_id')
                ->with(['user', 'files', 'responses.user', 'responses.files']);
        }, 'readStatuses' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }]);

        // Option 1: Filtrer uniquement sur l'utilisateur connecté

        // OU Option 2: Filtrer selon le rôle de l'utilisateur
        if ($user->role == Role::RemSuperAdmin->value) {
            // Les admins peuvent voir toutes les discussions
            // Pas besoin de filtre supplémentaire
        } elseif ($user->role == Role::RemAdmin->value) {

        } elseif ($user->role == Role::SClient->value) {
            // Les modérateurs peuvent voir les discussions de leur département
            $discussions = $discussions->whereHas('user', function ($query) use ($user) {
                $query->where('client_id', $user->client_id);
            });
        } else {
            // Les utilisateurs normaux ne voient que leurs propres discussions
            $discussions = $discussions->where('user_id', $user->id);
        }

        $discussions = $discussions->latest()->get();

        $discussions->each(function ($discussion) use ($userId) {
            $discussion->is_unread_for_current_user = $discussion->isUnreadForUser($userId);
        });

        return response()->json([
            'discussions' => $discussions,
        ]);
        /*$discussions = Discussion::with(['user', 'files', 'messageComments' => function($query) {
            $query->whereNull('parent_id')
                ->with(['user', 'files', 'responses.user', 'responses.files']);
        }])
        ->latest()
        ->get();

        return response()->json([
            'discussions' => $discussions
        ]);*/
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'files.*' => 'nullable|file|max:10240', // 10MB max par fichier
        ]);

        $userId = auth()->id();

        $discussion = Discussion::create([
            'subject' => $request->subject,
            'content' => $request->content,
            'user_id' => $userId,
            'status' => 'open',
            'last_comment_user_id' => $userId,
        ]);

        $discussion->markAsReadForUser($userId);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('discussion-files', 'public');
                DiscussionFile::create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'discussion_id' => $discussion->id,
                ]);
            }
        }

        $discussion->load(['user', 'files']);
        $discussion->is_unread_for_current_user = false;

        return response()->json([
            'discussion' => $discussion,
        ], 201);
    }

    public function show(Discussion $discussion)
    {
        $userId = auth()->id();

        $discussion->load([
            'user',
            'files',
            'message_comments' => function ($query) {
                $query->with(['user', 'files'])
                    ->whereNull('parent_id')
                    ->orderBy('created_at', 'desc');
            },
            'message_comments.responses' => function ($query) {
                $query->with(['user', 'files'])
                    ->orderBy('created_at', 'asc');
            },
            'readStatuses' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
        ]);

        $discussion->is_unread_for_current_user = $discussion->isUnreadForUser($userId);

        return response()->json([
            'discussion' => $discussion,
        ]);
    }

    public function updateStatus(Request $request, Discussion $discussion)
    {
        $request->validate([
            'status' => 'required|in:open,closed',
        ]);

        $discussion->update([
            'status' => $request->status,
        ]);

        $userId = auth()->id();
        $discussion->is_unread_for_current_user = $discussion->isUnreadForUser($userId);

        return response()->json([
            'discussion' => $discussion,
        ]);
    }

    public function markAsRead(Discussion $discussion)
    {
        $user = auth()->user();
        $userId = $user->id;

        if ($user->role !== Role::RemSuperAdmin->value && $user->role !== Role::RemAdmin->value) {
            if ($user->role === Role::SClient->value) {
                $discussionUser = $discussion->user;

                if ($discussionUser->client_id !== $user->client_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous n\'avez pas accès à cette discussion',
                    ], 403);
                }
            } else {
                if ($discussion->user_id !== $user->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vous n\'avez pas accès à cette discussion',
                    ], 403);
                }
            }
        }

        $discussion->markAsReadForUser($userId);

        $discussion = $discussion->fresh(['user', 'files', 'readStatuses' => function ($query) use ($userId) {
            $query->where('user_id', $userId);
        }]);

        $discussion->is_unread_for_current_user = false;

        return response()->json([
            'success' => true,
            'discussion' => $discussion,
        ]);
    }
}
