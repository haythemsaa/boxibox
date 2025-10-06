<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Envoyer un message
     */
    public function sendMessage(Request $request)
    {
        $client = $request->user();

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'attachment' => 'nullable|file|max:5120', // 5MB max
        ]);

        try {
            $attachmentPath = null;

            // Gestion de l'attachement si présent
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = time() . '_' . $file->getClientOriginalName();
                $attachmentPath = $file->storeAs('chat_attachments', $filename, 'public');
            }

            $chatMessage = ChatMessage::create([
                'tenant_id' => $client->tenant_id,
                'client_id' => $client->id,
                'user_id' => null, // Envoyé par le client
                'message' => $validated['message'],
                'attachment_path' => $attachmentPath,
                'sent_by' => 'client',
                'read_at' => null,
            ]);

            // TODO: Envoyer une notification temps réel aux admins (Pusher/WebSocket)
            // broadcast(new NewChatMessage($chatMessage))->toOthers();

            return response()->json([
                'success' => true,
                'message' => 'Message envoyé',
                'data' => [
                    'id' => $chatMessage->id,
                    'message' => $chatMessage->message,
                    'attachment_url' => $chatMessage->attachment_path ?
                        asset('storage/' . $chatMessage->attachment_path) : null,
                    'sent_by' => $chatMessage->sent_by,
                    'read' => false,
                    'created_at' => $chatMessage->created_at->format('Y-m-d H:i:s'),
                    'formatted_date' => $chatMessage->created_at->format('d/m/Y H:i'),
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi du message',
                'error' => app()->environment('local') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Récupérer l'historique des messages
     */
    public function getMessages(Request $request)
    {
        $client = $request->user();

        $messages = ChatMessage::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        // Marquer les messages admin comme lus
        ChatMessage::where('client_id', $client->id)
            ->where('sent_by', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages->map(function($message) {
                    return [
                        'id' => $message->id,
                        'message' => $message->message,
                        'attachment_url' => $message->attachment_path ?
                            asset('storage/' . $message->attachment_path) : null,
                        'sent_by' => $message->sent_by,
                        'read' => $message->read_at !== null,
                        'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                        'formatted_date' => $message->created_at->format('d/m/Y H:i'),
                        'formatted_time' => $message->created_at->format('H:i'),
                        'is_today' => $message->created_at->isToday(),
                    ];
                }),
                'pagination' => [
                    'total' => $messages->total(),
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                ],
                'unread_count' => ChatMessage::where('client_id', $client->id)
                    ->where('sent_by', 'admin')
                    ->whereNull('read_at')
                    ->count(),
            ]
        ], 200);
    }

    /**
     * Marquer un message comme lu
     */
    public function markAsRead(Request $request, $id)
    {
        $client = $request->user();

        $message = ChatMessage::where('client_id', $client->id)
            ->where('id', $id)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé'
            ], 404);
        }

        if ($message->read_at === null) {
            $message->update(['read_at' => now()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message marqué comme lu'
        ], 200);
    }

    /**
     * Compter les messages non lus
     */
    public function unreadCount(Request $request)
    {
        $client = $request->user();

        $count = ChatMessage::where('client_id', $client->id)
            ->where('sent_by', 'admin')
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ], 200);
    }

    /**
     * Supprimer un message (soft delete)
     */
    public function deleteMessage(Request $request, $id)
    {
        $client = $request->user();

        $message = ChatMessage::where('client_id', $client->id)
            ->where('sent_by', 'client')
            ->where('id', $id)
            ->first();

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Message non trouvé ou non autorisé'
            ], 404);
        }

        // Vérifier que le message a moins de 5 minutes
        if ($message->created_at->addMinutes(5)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous ne pouvez supprimer que les messages de moins de 5 minutes'
            ], 400);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message supprimé'
        ], 200);
    }
}
