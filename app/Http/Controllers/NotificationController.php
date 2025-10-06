<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Afficher toutes les notifications
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Récupérer les notifications non lues (pour AJAX)
     */
    public function getUnread()
    {
        $notifications = Auth::user()->unreadNotifications;

        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->take(5)->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->data['type'] ?? 'info',
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $notification->data['icon'] ?? 'fas fa-bell',
                    'color' => $notification->data['color'] ?? 'info',
                    'url' => $notification->data['url'] ?? '#',
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications ont été marquées comme lues',
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notification supprimée');
    }

    /**
     * Afficher les paramètres de notifications
     */
    public function settings()
    {
        $settings = Auth::user()->notificationSettings ?? Auth::user()->notificationSettings()->create([
            'tenant_id' => Auth::user()->tenant_id,
        ]);

        return view('notifications.settings', compact('settings'));
    }

    /**
     * Mettre à jour les paramètres de notifications
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'email_paiement_recu' => 'boolean',
            'email_paiement_retard' => 'boolean',
            'email_nouvelle_reservation' => 'boolean',
            'email_fin_contrat' => 'boolean',
            'email_acces_refuse' => 'boolean',
            'push_paiement_recu' => 'boolean',
            'push_paiement_retard' => 'boolean',
            'push_nouvelle_reservation' => 'boolean',
            'push_fin_contrat' => 'boolean',
            'push_acces_refuse' => 'boolean',
            'sms_paiement_retard' => 'boolean',
            'sms_fin_contrat' => 'boolean',
            'sms_acces_refuse' => 'boolean',
            'notifications_activees' => 'boolean',
            'heure_debut_notifications' => 'required|date_format:H:i',
            'heure_fin_notifications' => 'required|date_format:H:i',
        ]);

        // Convertir les heures au format H:i:s
        $validated['heure_debut_notifications'] .= ':00';
        $validated['heure_fin_notifications'] .= ':00';

        $settings = Auth::user()->notificationSettings ?? Auth::user()->notificationSettings()->create([
            'tenant_id' => Auth::user()->tenant_id,
        ]);

        $settings->update($validated);

        return redirect()->back()->with('success', 'Paramètres de notifications mis à jour');
    }
}
