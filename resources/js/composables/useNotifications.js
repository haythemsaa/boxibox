import { ref, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from './useToast';

/**
 * Composable pour gérer les notifications en temps réel avec Laravel Echo
 *
 * Usage:
 * <script setup>
 * import { useNotifications } from '@/composables/useNotifications';
 *
 * const {
 *   notifications,
 *   unreadCount,
 *   markAsRead,
 *   markAllAsRead
 * } = useNotifications();
 * </script>
 */
export function useNotifications() {
    const page = usePage();
    const toast = useToast();

    // État réactif
    const notifications = ref([]);
    const unreadCount = ref(0);
    const isConnected = ref(false);
    const echoChannel = ref(null);

    /**
     * Initialise les notifications depuis les props Inertia
     */
    const initNotifications = () => {
        if (page.props.notifications) {
            notifications.value = Array.isArray(page.props.notifications)
                ? page.props.notifications
                : [];
            updateUnreadCount();
        }
    };

    /**
     * Met à jour le compteur de non-lus
     */
    const updateUnreadCount = () => {
        unreadCount.value = notifications.value.filter(n => !n.read_at).length;
    };

    /**
     * Connecte au canal WebSocket privé de l'utilisateur
     */
    const connectToEcho = () => {
        if (!window.Echo) {
            console.warn('Laravel Echo n\'est pas configuré. Les notifications temps réel sont désactivées.');
            return;
        }

        const user = page.props.auth?.user;
        if (!user?.id) {
            console.warn('Utilisateur non authentifié. WebSocket non démarré.');
            return;
        }

        try {
            // Écouter le canal privé de l'utilisateur
            echoChannel.value = window.Echo.private(`user.${user.id}`);

            // Écouter l'événement de nouvelle notification
            echoChannel.value.listen('.notification.new', (data) => {
                console.log('Nouvelle notification reçue:', data);

                // Ajouter la notification au début de la liste
                notifications.value.unshift({
                    id: data.id,
                    type: data.type,
                    data: data.data,
                    read_at: data.read_at,
                    created_at: data.created_at
                });

                // Mettre à jour le compteur
                updateUnreadCount();

                // Afficher un toast
                showNotificationToast(data);
            });

            // Événements de connexion
            echoChannel.value.subscribed(() => {
                console.log('✅ Connecté au canal de notifications');
                isConnected.value = true;
            });

            echoChannel.value.error((error) => {
                console.error('❌ Erreur WebSocket:', error);
                isConnected.value = false;
            });

        } catch (error) {
            console.error('Erreur lors de la connexion Echo:', error);
        }
    };

    /**
     * Affiche un toast pour une nouvelle notification
     */
    const showNotificationToast = (notification) => {
        const type = notification.type?.split('\\').pop() || 'Notification';
        const message = notification.data?.message || 'Vous avez une nouvelle notification';

        // Choisir le type de toast selon le type de notification
        if (type.includes('Payment') || type.includes('Paiement')) {
            toast.success(message);
        } else if (type.includes('Warning') || type.includes('Alert')) {
            toast.warning(message);
        } else if (type.includes('Error') || type.includes('Erreur')) {
            toast.error(message);
        } else {
            toast.info(message);
        }
    };

    /**
     * Marque une notification comme lue
     */
    const markAsRead = async (notificationId) => {
        try {
            await window.axios.post(`/client/notifications/${notificationId}/mark-read`);

            // Mettre à jour localement
            const notification = notifications.value.find(n => n.id === notificationId);
            if (notification) {
                notification.read_at = new Date().toISOString();
                updateUnreadCount();
            }
        } catch (error) {
            console.error('Erreur lors du marquage comme lu:', error);
            toast.error('Erreur lors de la mise à jour de la notification');
        }
    };

    /**
     * Marque toutes les notifications comme lues
     */
    const markAllAsRead = async () => {
        try {
            await window.axios.post('/client/notifications/mark-all-read');

            // Mettre à jour localement
            notifications.value.forEach(n => {
                n.read_at = n.read_at || new Date().toISOString();
            });
            updateUnreadCount();

            toast.success('Toutes les notifications ont été marquées comme lues');
        } catch (error) {
            console.error('Erreur lors du marquage de toutes les notifications:', error);
            toast.error('Erreur lors de la mise à jour');
        }
    };

    /**
     * Déconnecte du canal WebSocket
     */
    const disconnectFromEcho = () => {
        if (echoChannel.value) {
            echoChannel.value.stopListening('.notification.new');
            window.Echo.leave(echoChannel.value.name);
            echoChannel.value = null;
            isConnected.value = false;
            console.log('🔌 Déconnecté du canal de notifications');
        }
    };

    /**
     * Rafraîchit les notifications depuis le serveur
     */
    const refreshNotifications = async () => {
        try {
            const response = await window.axios.get('/client/notifications');
            notifications.value = response.data.notifications || [];
            updateUnreadCount();
        } catch (error) {
            console.error('Erreur lors du rafraîchissement des notifications:', error);
        }
    };

    // Lifecycle hooks
    onMounted(() => {
        initNotifications();
        connectToEcho();
    });

    onUnmounted(() => {
        disconnectFromEcho();
    });

    return {
        notifications,
        unreadCount,
        isConnected,
        markAsRead,
        markAllAsRead,
        refreshNotifications,
    };
}
