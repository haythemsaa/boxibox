<template>
    <ClientLayout>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <!-- Animation de succès -->
                    <div class="text-center mb-4">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="text-success mb-3">Paiement réussi !</h2>
                        <p class="text-muted lead">
                            Votre paiement a été traité avec succès
                        </p>
                    </div>

                    <!-- Détails du paiement -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="mb-4">Récapitulatif du paiement</h5>

                            <div class="payment-details">
                                <div class="detail-row">
                                    <span class="detail-label">Facture</span>
                                    <span class="detail-value fw-bold">{{ facture.numero_facture }}</span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Montant payé</span>
                                    <span class="detail-value text-success fw-bold">
                                        {{ formatCurrency(session.amount) }}
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Date du paiement</span>
                                    <span class="detail-value">{{ formatDate(facture.date_paiement) }}</span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Mode de paiement</span>
                                    <span class="detail-value">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Carte bancaire (Stripe)
                                    </span>
                                </div>

                                <div class="detail-row">
                                    <span class="detail-label">Référence</span>
                                    <span class="detail-value">
                                        <small class="text-muted font-monospace">{{ session.id }}</small>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations complémentaires -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Confirmation envoyée</strong><br>
                        Un email de confirmation a été envoyé à votre adresse email avec les détails de votre paiement.
                    </div>

                    <!-- Actions -->
                    <div class="d-grid gap-2">
                        <Link
                            :href="route('client.factures.show', facture.id)"
                            class="btn btn-primary"
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

                        <Link
                            :href="route('client.dashboard')"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fas fa-home me-2"></i>
                            Retour au tableau de bord
                        </Link>
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
        session: {
            type: Object,
            required: true,
        },
    },

    methods: {
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('fr-FR', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        },
    },
};
</script>

<style scoped>
.success-icon {
    font-size: 5rem;
    color: #28a745;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
        opacity: 1;
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
