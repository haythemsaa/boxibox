<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ClientChatController extends Controller
{
    /**
     * Send a new chat message from the client.
     */
    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        ChatMessage::create([
            'client_id' => auth()->id(),
            'from_client' => true,
            'message' => $request->message,
            'lu' => false
        ]);

        // Récupérer tous les messages pour rafraîchir le chat
        $messages = ChatMessage::where('client_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        return back()->with([
            'chatMessages' => $messages,
            'success' => 'Message envoyé'
        ]);
    }

    /**
     * Mark all messages from admin as read.
     */
    public function markAllRead()
    {
        ChatMessage::where('client_id', auth()->id())
            ->where('from_client', false)
            ->where('lu', false)
            ->update(['lu' => true]);

        return back();
    }
}
