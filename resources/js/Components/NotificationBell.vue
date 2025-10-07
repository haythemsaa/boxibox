<template>
    <div class="notification-bell">
        <div class="dropdown">
            <button
                class="btn btn-link position-relative p-2"
                type="button"
                id="notificationDropdown"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                @click="markAsViewed"
            >
                <i class="fas fa-bell" :class="hasUnread ? 'text-warning' : 'text-white'" style="font-size: 1.3rem;"></i>
                <span
                    v-if="unreadCount > 0"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                >
                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                    <span class="visually-hidden">notifications non lues</span>
                </span>
                <!-- Indicateur WebSocket connecté -->
                <span
                    v-if="isConnected"
                    class="position-absolute bottom-0 start-100 translate-middle badge rounded-circle bg-success p-1"
                    title="Temps réel activé"
                    style="width: 10px; height: 10px;"
                >
                    <span class="visually-hidden">WebSocket connecté</span>
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end notification-dropdown shadow" aria-labelledby="notificationDropdown">
                <li class="dropdown-header d-flex justify-content-between align-items-center">
                    <span>
                        <i class="fas fa-bell me-2"></i>
                        Notifications
                    </span>
                    <span class="badge bg-primary">{{ unreadCount }}</span>
                </li>
                <li><hr class="dropdown-divider"></li>

                <div class="notification-list" style="max-height: 400px; overflow-y: auto;">
                    <template v-if="notifications && notifications.length > 0">
                        <li v-for="notification in notifications.slice(0, 10)" :key="notification.id">
                            <a
                                class="dropdown-item notification-item"
                                :class="{ 'unread': !notification.lu }"
                                href="#"
                                @click.prevent="handleNotificationClick(notification)"
                            >
                                <div class="d-flex">
                                    <div class="notification-icon me-3">
                                        <i class="fas" :class="getNotificationIcon(notification.type)"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="notification-title">{{ notification.titre }}</div>
                                        <div class="notification-message">{{ notification.message }}</div>
                                        <div class="notification-time">
                                            <i class="far fa-clock me-1"></i>
                                            {{ formatRelativeTime(notification.created_at) }}
                                        </div>
                                    </div>
                                    <div v-if="!notification.lu" class="unread-indicator"></div>
                                </div>
                            </a>
                        </li>
                    </template>
                    <li v-else>
                        <div class="dropdown-item text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Aucune notification</p>
                        </div>
                    </li>
                </div>

                <li v-if="notifications && notifications.length > 0"><hr class="dropdown-divider"></li>
                <li v-if="notifications && notifications.length > 0">
                    <a class="dropdown-item text-center text-primary fw-bold" :href="route('client.notifications')">
                        Voir toutes les notifications
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { useNotifications } from '@/composables/useNotifications';

export default {
    props: {
        initialNotifications: {
            type: Array,
            default: () => []
        }
    },

    setup(props) {
        // Utiliser le composable pour les notifications temps réel
        const {
            notifications: realtimeNotifications,
            unreadCount: realtimeUnreadCount,
            isConnected,
            markAsRead: markNotificationAsRead
        } = useNotifications();

        const notifications = ref(props.initialNotifications || []);

        // Synchroniser avec les notifications temps réel si disponibles
        watch(realtimeNotifications, (newNotifications) => {
            if (newNotifications.length > 0) {
                notifications.value = newNotifications;
            }
        }, { deep: true });

        const unreadCount = computed(() => {
            // Utiliser le compteur temps réel si WebSocket connecté, sinon le compteur local
            return isConnected.value ? realtimeUnreadCount.value : notifications.value.filter(n => !n.lu).length;
        });

        const hasUnread = computed(() => unreadCount.value > 0);

        const getNotificationIcon = (type) => {
            const icons = {
                'facture': 'fa-file-invoice text-primary',
                'paiement': 'fa-credit-card text-success',
                'relance': 'fa-exclamation-circle text-warning',
                'contrat': 'fa-file-contract text-info',
                'document': 'fa-file-alt text-secondary',
                'systeme': 'fa-cog text-dark',
                'message': 'fa-envelope text-primary'
            };
            return icons[type] || 'fa-bell text-secondary';
        };

        const formatRelativeTime = (dateString) => {
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'À l\'instant';
            if (diffMins < 60) return `Il y a ${diffMins} min`;
            if (diffHours < 24) return `Il y a ${diffHours}h`;
            if (diffDays < 7) return `Il y a ${diffDays}j`;
            return date.toLocaleDateString('fr-FR');
        };

        const markAsViewed = () => {
            // Cette fonction marque les notifications comme "vues" (ouverture du dropdown)
            // On peut ajouter un appel API ici si nécessaire
        };

        const handleNotificationClick = (notification) => {
            // Marquer comme lu en utilisant le composable
            if (!notification.lu && !notification.read_at) {
                if (isConnected.value && markNotificationAsRead) {
                    // Utiliser le composable si WebSocket actif
                    markNotificationAsRead(notification.id);
                } else {
                    // Sinon utiliser la méthode classique
                    router.post(route('client.notifications.mark-read', notification.id), {}, {
                        preserveScroll: true,
                        onSuccess: () => {
                            notification.lu = true;
                        }
                    });
                }
            }

            // Rediriger vers la ressource liée
            if (notification.lien) {
                window.location.href = notification.lien;
            }
        };

        onMounted(() => {
            // Plus besoin de polling si WebSocket actif
            // Le polling reste en fallback si WebSocket non configuré
            if (!window.Echo) {
                console.log('📡 Fallback: Polling activé (WebSocket non configuré)');
                setInterval(() => {
                    router.reload({ only: ['notifications'], preserveScroll: true });
                }, 30000);
            } else {
                console.log('⚡ WebSocket actif - Notifications temps réel activées');
            }
        });

        return {
            notifications,
            unreadCount,
            hasUnread,
            isConnected,
            getNotificationIcon,
            formatRelativeTime,
            markAsViewed,
            handleNotificationClick
        };
    }
};
</script>

<style scoped>
.notification-bell .btn-link {
    text-decoration: none;
    border: none;
    background: transparent;
}

.notification-bell .btn-link:hover {
    opacity: 0.8;
}

.notification-dropdown {
    width: 380px;
    border: none;
    border-radius: 12px;
    padding: 0;
}

.dropdown-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 1.25rem;
    font-weight: 600;
    border-radius: 12px 12px 0 0;
}

.notification-list {
    padding: 0;
}

.notification-item {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #f1f3f5;
    transition: all 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e7f1ff;
}

.notification-item.unread:hover {
    background-color: #d0e7ff;
}

.notification-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: #212529;
    margin-bottom: 0.25rem;
}

.notification-message {
    font-size: 0.85rem;
    color: #6c757d;
    margin-bottom: 0.5rem;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.notification-time {
    font-size: 0.75rem;
    color: #adb5bd;
}

.unread-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #0d6efd;
    flex-shrink: 0;
    margin-top: 0.5rem;
}

.badge.rounded-pill {
    padding: 0.35em 0.6em;
    font-size: 0.7rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Dark mode support */
.dark-mode .notification-dropdown {
    background: #2b3035;
    color: #f8f9fa;
}

.dark-mode .notification-item {
    border-bottom-color: #495057;
    color: #f8f9fa;
}

.dark-mode .notification-item:hover {
    background-color: #343a40;
}

.dark-mode .notification-item.unread {
    background-color: #1a3a52;
}

.dark-mode .notification-title {
    color: #f8f9fa;
}

.dark-mode .notification-message {
    color: #adb5bd;
}

.dark-mode .notification-icon {
    background: #343a40;
}
</style>
