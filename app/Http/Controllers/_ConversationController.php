<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = auth()->user()->conversations()
        ->with([
            'messages' => function ($query) {
                $query->latest(); // Charger les messages par ordre décroissant
            },
        ])
        ->withCount([
            'messages as unread_count' => function ($query) {
                $query->where('is_read', false)
                    ->where('user_id', '!=', auth()->id()); // Exclure les messages lus par l'utilisateur
            },
        ])
        ->get();
    
    // Statistiques
    $stats = [
        'total' => $conversations->count(),
        'open' => $conversations->where('status', 'pending')->count(),
        'closed' => $conversations->where('status', 'completed')->count(),
        'unread' => $conversations->sum('unread_count'),
    ];
    
    // Réponse JSON
    return response()->json([
        'conversations' => $conversations,
        'stats' => $stats,
    ]);
    
    }

    public function show($id)
    {
        $conversation = Conversation::with(['participants', 'messages.attachments'])
            ->findOrFail($id);

        // Marquer les messages comme lus
        $conversation->messages()
            ->where('user_id', '!=', auth()->id())
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json($conversation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'object' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'file|max:10240' // 10MB max par fichier
        ]);
        \Log::info("Tentative conv : ".auth()->id());
        $conversation = Conversation::create([
            'object' => $validated['object'],
            'status' => "pending",
            'created_by' => auth()->id(),
            'status' => 'open'
        ]);

        // // Ajouter les participants
        // $conversation->participants()->attach([
        //     auth()->id(),
        //     ...$validated['participants']
        // ]);

        // Créer le premier message
        $message = $conversation->messages()->create([
            'content' => $validated['message'],
            'user_id' => auth()->id()
        ]);

        // Gérer les pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments');
                $message->attachments()->create([
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_path' => $path
                ]);
            }
        }

        return response()->json($conversation->load('messages.attachments'));
    }

    public function addMessage(Request $request, $id)
    {
        \Log::info("Tentative de message : ".json_encode($request->input()));
        $validated = $request->validate([
            'content' => 'required|string',
            'attachments.*' => 'file|max:10240'
        ]);

        $conversation = Conversation::findOrFail($id);
        
        $message = $conversation->messages()->create([
            'content' => $validated['content'],
            'user_id' => auth()->id()
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments');
                $message->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'path' => $path
                ]);
            }
        }

        return response()->json($message->load('attachments'));
    }

    public function getMessages($id)
    {
        $messages = Message::with(['attachments', 'user'])
            ->where('conversation_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['messages' => $messages]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,closed'
        ]);

        $conversation = Conversation::findOrFail($id);
        $conversation->update(['status' => $validated['status']]);

        return response()->json($conversation);
    }
}