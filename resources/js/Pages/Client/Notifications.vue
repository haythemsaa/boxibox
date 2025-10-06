<template>
    <ClientLayout title="Mes Notifications">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h3">
                <i class="fas fa-bell me-2"></i>
                Mes Notifications
            </h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <button
                    v-if="hasUnread"
                    @click="markAllAsRead"
                    class="btn btn-sm btn-outline-primary me-2"
                    :disabled="marking"
                >
                    <i class="fas fa-check-double me-1"></i>
                    Tout marquer comme lu
                </button>
                <div class="btn-group btn-group-sm">
                    <button
                        @click="filterType = 'all'"
                        class="btn"
                        :class="filterType === 'all' ? 'btn-primary' : 'btn-outline-secondary'"
                    >
                        Toutes
                    </button>
                    <button
                        @click="filterType = 'unread'"
                        class="btn"
                        :class="filterType === 'unread' ? 'btn-primary' : 'btn-outline-secondary'"
                    >
                        Non lues ({{ unreadCount }})
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtres par type -->
        <div class="mb-4">
            <div class="btn-group btn-group-sm" role="group">
                <button
                    v-for="(label, type) in notificationTypes"
                    :key="type"
                    @click="filterCategory = type"
                    type="button"
                    class="btn"
                    :class="filterCategory === type ? 'btn-primary' : 'btn-outline-secondary'"
                >
                    <i class="fas" :class="getTypeIcon(type)" ></i>
                    {{ label }}
                </button>
            </div>
        </div>

        <!-- Liste des Notifications -->
        <div v-if="filteredNotifications.length > 0" class="row">
            <div class="col-12">
                <div class="notifications-list">
                    <div
                        v-for="notification in filteredNotifications"
                        :key="notification.id"
                        class="notification-card card mb-3 shadow-sm"
                        :class="{ 'notification-unread': !notification.lu }"
                        @click="handleNotificationClick(notification)"
                    >
                        <div class="card-body p-3">
                            <div class="d-flex align-items-start">
                                <div class="notification-icon me-3">
                                    <i class="fas fa-2x" :class="getNotificationIcon(notification.type)"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0 notification-title">
                                            {{ notification.titre }}
                                            <span v-if="!notification.lu" class="badge bg-primary ms-2">Nouveau</span>
                                        </h6>
                                        <small class="text-muted">
                                            {{ formatRelativeTime(notification.created_at) }}
                                        </small>
                                    </div>
                                    <p class="mb-2 text-muted notification-message">
                                        {{ notification.message }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge" :class="getTypeBadge(notification.type)">
                                            {{ getTypeLabel(notification.type) }}
                                        </span>
                                        <div>
                                            <button
                                                v-if="!notification.lu"
                                                @click.stop="markAsRead(notification.id)"
                                                class="btn btn-sm btn-outline-primary me-2"
                                            >
                                                <i class="fas fa-check"></i>
                                                Marquer comme lu
                                            </button>
                                            <button
                                                v-if="notification.lien"
                                                @click.stop="goToLink(notification.lien)"
                                                class="btn btn-sm btn-primary"
                                            >
                                                <i class="fas fa-arrow-right"></i>
                                                Voir
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message vide -->
        <div v-else class="text-center py-5">
            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
            <h4 class="text-muted">Aucune notification</h4>
            <p class="text-muted">
                {{ filterType === 'unread' ? 'Vous n\'avez pas de nouvelles notifications' : 'Votre boîte de notifications est vide' }}
            </p>
        </div>

        <!-- Pagination (si nécessaire) -->
        <div v-if="filteredNotifications.length > 0" class="d-flex justify-content-center mt-4">
            <nav>
                <ul class="pagination">
                    <li class="page-item disabled">
                        <span class="page-link">{{ filteredNotifications.length }} notification(s)</span>
                    </li>
                </ul>
            </nav>
        </div>
    </ClientLayout>
</template>

<script>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

export default {
    components: {
        ClientLayout
    },

    props: {
        notifications: {
            type: Array,
            default: () => []
        }
    },

    setup(props) {
        const filterType = ref('all'); // 'all' or 'unread'
        const filterCategory = ref('all'); // notification type filter
        const marking = ref(false);

        const notificationTypes = {
            'all': 'Toutes',
            'facture': 'Factures',
            'paiement': 'Paiements',
            'relance': 'Relances',
            'contrat': 'Contrats',
            'document': 'Documents',
            'systeme': 'Système',
            'message': 'Messages'
        };

        const unreadCount = computed(() => {
            return props.notifications.filter(n => !n.lu).length;
        });

        const hasUnread = computed(() => unreadCount.value > 0);

        const filteredNotifications = computed(() => {
            let filtered = props.notifications;

            // Filtre par statut lu/non lu
            if (filterType.value === 'unread') {
                filtered = filtered.filter(n => !n.lu);
            }

            // Filtre par catégorie
            if (filterCategory.value !== 'all') {
                filtered = filtered.filter(n => n.type === filterCategory.value);
            }

            return filtered;
        });

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

        const getTypeIcon = (type) => {
            const icons = {
                'all': 'fa-list',
                'facture': 'fa-file-invoice',
                'paiement': 'fa-credit-card',
                'relance': 'fa-exclamation-circle',
                'contrat': 'fa-file-contract',
                'document': 'fa-file-alt',
                'systeme': 'fa-cog',
                'message': 'fa-envelope'
            };
            return icons[type] || 'fa-bell';
        };

        const getTypeBadge = (type) => {
            const badges = {
                'facture': 'bg-primary',
                'paiement': 'bg-success',
                'relance': 'bg-warning',
                'contrat': 'bg-info',
                'document': 'bg-secondary',
                'systeme': 'bg-dark',
                'message': 'bg-primary'
            };
            return badges[type] || 'bg-secondary';
        };

        const getTypeLabel = (type) => {
            return notificationTypes[type] || 'Notification';
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
            if (diffDays < 30) return `Il y a ${Math.floor(diffDays / 7)} semaine(s)`;
            return date.toLocaleDateString('fr-FR');
        };

        const markAsRead = (notificationId) => {
            router.post(route('client.notifications.mark-read', notificationId), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    const notification = props.notifications.find(n => n.id === notificationId);
                    if (notification) {
                        notification.lu = true;
                    }
                }
            });
        };

        const markAllAsRead = () => {
            marking.value = true;
            router.post(route('client.notifications.mark-all-read'), {}, {
                preserveScroll: true,
                onFinish: () => {
                    marking.value = false;
                },
                onSuccess: () => {
                    props.notifications.forEach(n => {
                        n.lu = true;
                    });
                }
            });
        };

        const handleNotificationClick = (notification) => {
            if (!notification.lu) {
                markAsRead(notification.id);
            }
        };

        const goToLink = (link) => {
            window.location.href = link;
        };

        return {
            filterType,
            filterCategory,
            marking,
            notificationTypes,
            unreadCount,
            hasUnread,
            filteredNotifications,
            getNotificationIcon,
            getTypeIcon,
            getTypeBadge,
            getTypeLabel,
            formatRelativeTime,
            markAsRead,
            markAllAsRead,
            handleNotificationClick,
            goToLink
        };
    }
};
</script>

<style scoped>
.notifications-list {
    max-width: 900px;
    margin: 0 auto;
}

.notification-card {
    border: none;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
}

.notification-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
}

.notification-card.notification-unread {
    background: #f8f9fa;
    border-left-color: #0d6efd;
}

.notification-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-unread .notification-icon {
    background: #e7f1ff;
}

.notification-title {
    font-weight: 600;
    color: #212529;
}

.notification-message {
    font-size: 0.95rem;
    line-height: 1.5;
}

/* Dark mode */
.dark-mode .notification-card {
    background: #2b3035;
    color: #f8f9fa;
}

.dark-mode .notification-card.notification-unread {
    background: #1a3a52;
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

.dark-mode .notification-unread .notification-icon {
    background: #264a6b;
}
</style>
