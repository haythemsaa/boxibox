<template>
    <ClientLayout>
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- En-tête -->
                    <div class="d-flex align-items-center mb-4">
                        <Link :href="route('client.factures.show', facture.id)" class="btn btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </Link>
                        <div>
                            <h2 class="mb-0">Paiement en ligne</h2>
                            <p class="text-muted mb-0">Paiement sécurisé via Stripe</p>
                        </div>
                    </div>

                    <!-- Résumé de la facture -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-file-invoice me-2"></i>
                                Facture {{ facture.numero_facture }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Date d'émission :</strong><br>
                                        {{ formatDate(facture.date_emission) }}
                                    </p>
                                    <p class="mb-2">
                                        <strong>Date d'échéance :</strong><br>
                                        <span :class="{'text-danger fw-bold': isOverdue}">
                                            {{ formatDate(facture.date_echeance) }}
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2" v-if="facture.contrat">
                                        <strong>Contrat :</strong><br>
                                        {{ facture.contrat.numero_contrat }}
                                    </p>
                                    <p class="mb-2">
                                        <strong>Statut :</strong><br>
                                        <span class="badge" :class="getBadgeClass(facture.statut)">
                                            {{ getStatusLabel(facture.statut) }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Montant à payer</h5>
                                <h3 class="mb-0 text-primary">{{ formatCurrency(facture.montant_ttc) }}</h3>
                            </div>

                            <p class="text-muted small mt-2 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Montant TTC incluant {{ facture.taux_tva }}% de TVA
                            </p>
                        </div>
                    </div>

                    <!-- Informations de paiement -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                Paiement sécurisé
                            </h5>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Vos informations bancaires sont cryptées et sécurisées
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Paiement traité par Stripe, leader mondial du paiement en ligne
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Aucune donnée bancaire n'est stockée sur nos serveurs
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Confirmation immédiate par email
                                </li>
                            </ul>

                            <div class="alert alert-info mt-3">
                                <i class="fas fa-credit-card me-2"></i>
                                <strong>Modes de paiement acceptés :</strong> Visa, Mastercard, American Express
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de paiement -->
                    <div class="d-grid gap-2">
                        <button
                            @click="proceedToPayment"
                            class="btn btn-primary btn-lg"
                            :disabled="isProcessing"
                        >
                            <span v-if="!isProcessing">
                                <i class="fas fa-lock me-2"></i>
                                Procéder au paiement sécurisé
                            </span>
                            <span v-else>
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Redirection vers Stripe...
                            </span>
                        </button>

                        <Link
                            :href="route('client.factures.show', facture.id)"
                            class="btn btn-outline-secondary"
                            :class="{'disabled': isProcessing}"
                        >
                            Annuler
                        </Link>
                    </div>

                    <!-- Informations légales -->
                    <p class="text-muted text-center mt-4 small">
                        En cliquant sur "Procéder au paiement", vous serez redirigé vers la page de paiement sécurisée Stripe.
                        <br>
                        Une fois le paiement effectué, votre facture sera automatiquement mise à jour.
                    </p>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import { Link, router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { useToast } from '@/composables/useToast';

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
        stripePublicKey: {
            type: String,
            required: true,
        },
    },

    setup() {
        const toast = useToast();
        return { toast };
    },

    data() {
        return {
            isProcessing: false,
        };
    },

    computed: {
        isOverdue() {
            const today = new Date();
            const echeance = new Date(this.facture.date_echeance);
            return echeance < today && this.facture.statut !== 'payee';
        },
    },

    methods: {
        async proceedToPayment() {
            this.isProcessing = true;

            try {
                // Créer une session Stripe Checkout
                const response = await axios.post(
                    route('client.payment.checkout', this.facture.id)
                );

                if (response.data.url) {
                    // Rediriger vers Stripe Checkout
                    window.location.href = response.data.url;
                } else {
                    throw new Error('URL de paiement invalide');
                }
            } catch (error) {
                this.isProcessing = false;
                console.error('Erreur paiement:', error);

                const message = error.response?.data?.error || 'Une erreur est survenue lors de la création de la session de paiement';
                this.toast.error(message);
            }
        },

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
.card {
    border: none;
}

.card-header {
    border-radius: 0.375rem 0.375rem 0 0;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}

.list-unstyled li {
    padding: 0.25rem 0;
}
</style>
