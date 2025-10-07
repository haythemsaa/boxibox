<template>
    <ClientLayout>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <!-- Icon d'annulation -->
                    <div class="text-center mb-4">
                        <div class="cancel-icon mb-4">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h2 class="text-warning mb-3">Paiement annulé</h2>
                        <p class="text-muted lead">
                            Votre paiement a été annulé
                        </p>
                    </div>

                    <!-- Informations -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="mb-4">À propos de votre facture</h5>

                            <div class="payment-details">
                                <div class="detail-row">
                                    <span class="detail-label">Facture</span>
                                    <span class="detail-value fw-bold">{{ facture.numero_facture }}</span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Montant</span>
                                    <span class="detail-value fw-bold">
                                        {{ formatCurrency(facture.montant_ttc) }}
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Date d'échéance</span>
                                    <span class="detail-value" :class="{'text-danger fw-bold': isOverdue}">
                                        {{ formatDate(facture.date_echeance) }}
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Statut</span>
                                    <span class="detail-value">
                                        <span class="badge" :class="getBadgeClass(facture.statut)">
                                            {{ getStatusLabel(facture.statut) }}
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Message informatif -->
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Aucun montant n'a été débité</strong><br>
                        Vous pouvez réessayer le paiement à tout moment ou utiliser un autre mode de paiement.
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        <Link
                            :href="route('client.payment.show', facture.id)"
                            class="btn btn-primary"
                        >
                            <i class="fas fa-redo me-2"></i>
                            Réessayer le paiement
                        </Link>

                        <Link
                            :href="route('client.factures.show', facture.id)"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fas fa-file-invoice me-2"></i>
                            Voir la facture
                        </Link>

                        <Link
                            :href="route('client.factures')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fas fa-list me-2"></i>
                            Retour aux factures
                        </Link>
                    </div>

                    <!-- Aide -->
                    <div class="mt-4 text-center">
                        <p class="text-muted small mb-2">
                            <i class="fas fa-question-circle me-1"></i>
                            Besoin d'aide ?
                        </p>
                        <p class="text-muted small mb-0">
                            Contactez-nous si vous rencontrez des difficultés avec le paiement en ligne.
                            <br>
                            Vous pouvez également opter pour un virement bancaire ou un prélèvement SEPA.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import { Link } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

export default {
    components: {
        ClientLayout,
        Link,
    },

    props: {
        facture: {
            type: Object,
            required: true,
        },
    },

    computed: {
        isOverdue() {
            const today = new Date();
            const echeance = new Date(this.facture.date_echeance);
            return echeance < today && this.facture.statut !== 'payee';
        },
    },

    methods: {
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        },

        getStatusLabel(statut) {
            const labels = {
                'brouillon': 'Brouillon',
                'envoyee': 'Envoyée',
                'payee': 'Payée',
                'en_attente': 'En attente',
                'en_retard': 'En retard',
                'annulee': 'Annulée',
            };
            return labels[statut] || statut;
        },

        getBadgeClass(statut) {
            const classes = {
                'brouillon': 'bg-secondary',
                'envoyee': 'bg-info',
                'payee': 'bg-success',
                'en_attente': 'bg-warning',
                'en_retard': 'bg-danger',
                'annulee': 'bg-dark',
            };
            return classes[statut] || 'bg-secondary';
        },
    },
};
</script>

<style scoped>
.cancel-icon {
    font-size: 5rem;
    color: #ffc107;
    animation: shake 0.5s ease-out;
}

@keyframes shake {
    0%, 100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-10px);
    }
    75% {
        transform: translateX(10px);
    }
}

.payment-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: #6c757d;
    font-weight: 500;
}

.detail-value {
    text-align: right;
}

.card {
    border: none;
}
</style>
