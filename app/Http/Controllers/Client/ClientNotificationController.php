<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientNotificationController extends Controller
{
    /**
     * Display a listing of the client's notifications.
     */
    public function index()
    {
        $notifications = ClientNotification::where('client_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Client/Notifications', [
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markRead(ClientNotification $notification)
    {
        // Vérifier que la notification appartient bien au client connecté
        if ($notification->client_id !== auth()->id()) {
            abort(403, 'Accès non autorisé');
        }

        $notification->update(['lu' => true]);

        return back();
    }

    /**
     * Mark all notifications as read for the authenticated client.
     */
    public function markAllRead()
    {
        ClientNotification::where('client_id', auth()->id())
            ->where('lu', false)
            ->update(['lu' => true]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues');
    }
}
