<template>
    <div class="card shadow-sm h-100 activity-widget">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-history me-2 text-info"></i>
                Activité Récente
            </h6>
            <a :href="route('client.suivi')" class="btn btn-sm btn-outline-primary">
                Tout voir
            </a>
        </div>
        <div class="card-body p-0">
            <div v-if="activities && activities.length > 0" class="activity-timeline">
                <div
                    v-for="(activity, index) in activities.slice(0, 5)"
                    :key="activity.id"
                    class="activity-item"
                    :class="{ 'last': index === activities.slice(0, 5).length - 1 }"
                >
                    <div class="activity-icon" :class="getActivityIconClass(activity.type)">
                        <i class="fas" :class="getActivityIcon(activity.type)"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">{{ activity.titre }}</div>
                        <div class="activity-description text-muted">{{ activity.description }}</div>
                        <div class="activity-time">
                            <i class="far fa-clock me-1"></i>
                            {{ formatRelativeTime(activity.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Aucune activité récente</p>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        activities: {
            type: Array,
            default: () => []
        }
    },

    setup() {
        const getActivityIcon = (type) => {
            const icons = {
                'facture': 'fa-file-invoice',
                'paiement': 'fa-credit-card',
                'contrat': 'fa-file-contract',
                'document': 'fa-file-alt',
                'acces': 'fa-key',
                'profil': 'fa-user',
                'sepa': 'fa-university',
                'connexion': 'fa-sign-in-alt'
            };
            return icons[type] || 'fa-circle';
        };

        const getActivityIconClass = (type) => {
            const classes = {
                'facture': 'bg-primary',
                'paiement': 'bg-success',
                'contrat': 'bg-info',
                'document': 'bg-secondary',
                'acces': 'bg-warning',
                'profil': 'bg-dark',
                'sepa': 'bg-success',
                'connexion': 'bg-primary'
            };
            return classes[type] || 'bg-secondary';
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

        return {
            getActivityIcon,
            getActivityIconClass,
            formatRelativeTime
        };
    }
};
</script>

<style scoped>
.activity-widget {
    border: none;
    border-radius: 12px;
}

.activity-timeline {
    padding: 1.5rem;
}

.activity-item {
    display: flex;
    padding-bottom: 1.5rem;
    position: relative;
}

.activity-item:not(.last)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
    margin-right: 1rem;
    position: relative;
    z-index: 1;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

.activity-description {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.activity-time {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Dark mode */
.dark-mode .activity-widget {
    background: #2b3035;
}

.dark-mode .card-header {
    background: #2b3035 !important;
    border-bottom-color: #495057 !important;
    color: #f8f9fa;
}

.dark-mode .activity-title {
    color: #f8f9fa;
}

.dark-mode .activity-item:not(.last)::after {
    background: #495057;
}
</style>
