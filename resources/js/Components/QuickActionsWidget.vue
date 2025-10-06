<template>
    <div class="card shadow-sm h-100 quick-actions-widget">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0">
                <i class="fas fa-bolt text-warning me-2"></i>
                Actions Rapides
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <a
                    v-for="action in actions"
                    :key="action.id"
                    :href="action.link"
                    class="list-group-item list-group-item-action d-flex align-items-center py-3"
                >
                    <div
                        class="action-icon me-3"
                        :style="{ background: action.color }"
                    >
                        <i class="fas" :class="action.icon"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="action-title">{{ action.title }}</div>
                        <small class="text-muted">{{ action.description }}</small>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        actions: {
            type: Array,
            default: () => [
                {
                    id: 1,
                    title: 'Payer mes factures',
                    description: 'Régler mes factures en attente',
                    icon: 'fa-credit-card',
                    color: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    link: route('client.factures')
                },
                {
                    id: 2,
                    title: 'Mes codes d\'accès',
                    description: 'Consulter mes codes PIN et QR',
                    icon: 'fa-qrcode',
                    color: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                    link: route('client.codes-acces')
                },
                {
                    id: 3,
                    title: 'Télécharger documents',
                    description: 'Contrats, factures PDF',
                    icon: 'fa-download',
                    color: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                    link: route('client.documents')
                },
                {
                    id: 4,
                    title: 'Mandat SEPA',
                    description: 'Configurer prélèvement auto',
                    icon: 'fa-university',
                    color: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                    link: route('client.sepa')
                }
            ]
        }
    }
};
</script>

<style scoped>
.quick-actions-widget {
    border: none;
    border-radius: 12px;
}

.list-group-item {
    border-left: none;
    border-right: none;
    transition: all 0.3s ease;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}

.list-group-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.action-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.action-title {
    font-weight: 600;
    color: #212529;
    margin-bottom: 0.25rem;
}

/* Dark mode */
.dark-mode .quick-actions-widget {
    background: #2b3035;
}

.dark-mode .card-header {
    background: #2b3035 !important;
    border-bottom-color: #495057 !important;
    color: #f8f9fa;
}

.dark-mode .list-group-item {
    background: #2b3035;
    color: #f8f9fa;
    border-color: #495057;
}

.dark-mode .list-group-item:hover {
    background: #343a40;
}

.dark-mode .action-title {
    color: #f8f9fa;
}
</style>
