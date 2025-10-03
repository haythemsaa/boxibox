<template>
    <ClientLayout title="Créer une Facture">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">
                    <i class="fas fa-file-invoice me-2"></i>
                    Créer une Nouvelle Facture
                </h1>
                <a :href="route('admin.factures.index')" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <!-- Stepper Progress -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="stepper-wrapper">
                    <div class="stepper-item" :class="{ completed: step > 1, active: step === 1 }">
                        <div class="step-counter">1</div>
                        <div class="step-name">Client & Contrat</div>
                    </div>
                    <div class="stepper-item" :class="{ completed: step > 2, active: step === 2 }">
                        <div class="step-counter">2</div>
                        <div class="step-name">Informations</div>
                    </div>
                    <div class="stepper-item" :class="{ completed: step > 3, active: step === 3 }">
                        <div class="step-counter">3</div>
                        <div class="step-name">Lignes</div>
                    </div>
                    <div class="stepper-item" :class="{ active: step === 4 }">
                        <div class="step-counter">4</div>
                        <div class="step-name">Validation</div>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm">
            <!-- Étape 1 : Client & Contrat -->
            <div v-show="step === 1" class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Sélection du Client et du Contrat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client *</label>
                            <select
                                v-model="form.client_id"
                                @change="loadClientContrats"
                                class="form-select"
                                :class="{ 'is-invalid': errors.client_id }"
                                required
                            >
                                <option value="">Sélectionnez un client...</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.prenom }} {{ client.nom }} - {{ client.email }}
                                </option>
                            </select>
                            <div v-if="errors.client_id" class="invalid-feedback">{{ errors.client_id }}</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contrat (optionnel)</label>
                            <select
                                v-model="form.contrat_id"
                                class="form-select"
                                :disabled="!form.client_id"
                            >
                                <option value="">Sans contrat</option>
                                <option v-for="contrat in clientContrats" :key="contrat.id" :value="contrat.id">
                                    {{ contrat.numero_contrat }} - Box {{ contrat.box?.numero }} - {{ formatAmount(contrat.montant_loyer) }}€/mois
                                </option>
                            </select>
                            <small class="text-muted">Laissez vide pour une facture hors contrat</small>
                        </div>
                    </div>

                    <!-- Infos Client Sélectionné -->
                    <div v-if="selectedClient" class="alert alert-info">
                        <h6 class="alert-heading">Client Sélectionné</h6>
                        <p class="mb-1"><strong>Nom :</strong> {{ selectedClient.prenom }} {{ selectedClient.nom }}</p>
                        <p class="mb-1"><strong>Email :</strong> {{ selectedClient.email }}</p>
                        <p class="mb-1"><strong>Téléphone :</strong> {{ selectedClient.telephone || 'N/A' }}</p>
                        <p class="mb-0"><strong>Adresse :</strong> {{ selectedClient.adresse || 'N/A' }}</p>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" @click="nextStep" class="btn btn-primary" :disabled="!form.client_id">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 2 : Informations de la Facture -->
            <div v-show="step === 2" class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations de la Facture
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Numéro de Facture *</label>
                            <input
                                v-model="form.numero_facture"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.numero_facture }"
                                placeholder="AUTO"
                                required
                            >
                            <small class="text-muted">Laissez vide pour génération automatique</small>
                            <div v-if="errors.numero_facture" class="invalid-feedback">{{ errors.numero_facture }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date d'Émission *</label>
                            <input
                                v-model="form.date_emission"
                                type="date"
                                class="form-control"
                                :class="{ 'is-invalid': errors.date_emission }"
                                required
                            >
                            <div v-if="errors.date_emission" class="invalid-feedback">{{ errors.date_emission }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date d'Échéance *</label>
                            <input
                                v-model="form.date_echeance"
                                type="date"
                                class="form-control"
                                :class="{ 'is-invalid': errors.date_echeance }"
                                required
                            >
                            <div v-if="errors.date_echeance" class="invalid-feedback">{{ errors.date_echeance }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type de Facture</label>
                            <select v-model="form.type_facture" class="form-select">
                                <option value="normale">Facture Normale</option>
                                <option value="acompte">Facture d'Acompte</option>
                                <option value="solde">Facture de Solde</option>
                                <option value="avoir">Avoir</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Taux de TVA (%)</label>
                            <select v-model.number="form.taux_tva" @change="calculateTotals" class="form-select">
                                <option :value="0">0% (Exonéré)</option>
                                <option :value="5.5">5,5% (Réduit)</option>
                                <option :value="10">10% (Intermédiaire)</option>
                                <option :value="20">20% (Normal)</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Statut</label>
                            <select v-model="form.statut" class="form-select">
                                <option value="en_attente">En attente</option>
                                <option value="envoyee">Envoyée</option>
                                <option value="payee">Payée</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes / Conditions de paiement</label>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="3"
                            placeholder="Exemple: Paiement par virement sous 30 jours..."
                        ></textarea>
                    </div>

                    <!-- Options Avancées -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3">Options Avancées</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input
                                        v-model="form.avec_remise"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="avec_remise"
                                    >
                                    <label class="form-check-label" for="avec_remise">
                                        Appliquer une remise
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4" v-if="form.avec_remise">
                                <input
                                    v-model.number="form.remise_pourcentage"
                                    @input="calculateTotals"
                                    type="number"
                                    class="form-control form-control-sm"
                                    placeholder="Remise %"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button type="button" @click="previousStep" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Précédent
                    </button>
                    <button type="button" @click="nextStep" class="btn btn-primary">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 3 : Lignes de Facture -->
            <div v-show="step === 3" class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>
                        Lignes de Facture
                    </h5>
                    <button type="button" @click="addLigne" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-2"></i>Ajouter une ligne
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%">Désignation *</th>
                                    <th style="width: 12%" class="text-center">Quantité *</th>
                                    <th style="width: 18%" class="text-end">Prix Unit. HT *</th>
                                    <th style="width: 18%" class="text-end">Total HT</th>
                                    <th style="width: 12%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(ligne, index) in form.lignes" :key="index">
                                    <td>
                                        <input
                                            v-model="ligne.designation"
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="Description du produit/service"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <input
                                            v-model.number="ligne.quantite"
                                            @input="calculateLigneTotal(ligne)"
                                            type="number"
                                            class="form-control form-control-sm text-center"
                                            min="0.01"
                                            step="0.01"
                                            required
                                        >
                                    </td>
                                    <td>
                                        <input
                                            v-model.number="ligne.prix_unitaire_ht"
                                            @input="calculateLigneTotal(ligne)"
                                            type="number"
                                            class="form-control form-control-sm text-end"
                                            min="0"
                                            step="0.01"
                                            required
                                        >
                                    </td>
                                    <td class="text-end align-middle">
                                        <strong>{{ formatAmount(ligne.montant_total_ht) }} €</strong>
                                    </td>
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            @click="removeLigne(index)"
                                            class="btn btn-sm btn-outline-danger"
                                            :disabled="form.lignes.length === 1"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="form.lignes.length === 0">
                                    <td colspan="5" class="text-center text-muted">
                                        Aucune ligne ajoutée. Cliquez sur "Ajouter une ligne" pour commencer.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Quick Add Templates -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-2">Modèles Rapides</h6>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" @click="addTemplateLoyer" class="btn btn-outline-primary">
                                <i class="fas fa-home me-1"></i>Loyer mensuel
                            </button>
                            <button type="button" @click="addTemplateDepot" class="btn btn-outline-primary">
                                <i class="fas fa-lock me-1"></i>Dépôt de garantie
                            </button>
                            <button type="button" @click="addTemplateFrais" class="btn btn-outline-primary">
                                <i class="fas fa-cog me-1"></i>Frais de gestion
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button type="button" @click="previousStep" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Précédent
                    </button>
                    <button type="button" @click="nextStep" class="btn btn-primary" :disabled="form.lignes.length === 0">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 4 : Validation et Récapitulatif -->
            <div v-show="step === 4" class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Récapitulatif et Validation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Informations Client -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">Informations Client</h6>
                            <p class="mb-1"><strong>Client :</strong> {{ selectedClient?.prenom }} {{ selectedClient?.nom }}</p>
                            <p class="mb-1"><strong>Email :</strong> {{ selectedClient?.email }}</p>
                            <p class="mb-1"><strong>Contrat :</strong> {{ selectedContrat?.numero_contrat || 'Sans contrat' }}</p>
                        </div>

                        <!-- Informations Facture -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">Informations Facture</h6>
                            <p class="mb-1"><strong>N° Facture :</strong> {{ form.numero_facture || 'AUTO' }}</p>
                            <p class="mb-1"><strong>Date émission :</strong> {{ formatDate(form.date_emission) }}</p>
                            <p class="mb-1"><strong>Date échéance :</strong> {{ formatDate(form.date_echeance) }}</p>
                            <p class="mb-1"><strong>Type :</strong> {{ getTypeLabel(form.type_facture) }}</p>
                            <p class="mb-1"><strong>Statut :</strong> <span :class="getStatusBadge(form.statut)">{{ getStatusLabel(form.statut) }}</span></p>
                        </div>
                    </div>

                    <!-- Lignes de Facture -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Lignes de Facture</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Désignation</th>
                                        <th class="text-center">Qté</th>
                                        <th class="text-end">PU HT</th>
                                        <th class="text-end">Total HT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(ligne, index) in form.lignes" :key="index">
                                        <td>{{ ligne.designation }}</td>
                                        <td class="text-center">{{ ligne.quantite }}</td>
                                        <td class="text-end">{{ formatAmount(ligne.prix_unitaire_ht) }} €</td>
                                        <td class="text-end">{{ formatAmount(ligne.montant_total_ht) }} €</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totaux -->
                    <div class="row">
                        <div class="col-md-8"></div>
                        <div class="col-md-4">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Total HT :</th>
                                    <td class="text-end">{{ formatAmount(totals.montant_ht) }} €</td>
                                </tr>
                                <tr v-if="form.avec_remise && form.remise_pourcentage > 0">
                                    <th>Remise ({{ form.remise_pourcentage }}%) :</th>
                                    <td class="text-end text-danger">- {{ formatAmount(totals.remise) }} €</td>
                                </tr>
                                <tr>
                                    <th>TVA ({{ form.taux_tva }}%) :</th>
                                    <td class="text-end">{{ formatAmount(totals.montant_tva) }} €</td>
                                </tr>
                                <tr class="table-primary">
                                    <th class="fs-5">Total TTC :</th>
                                    <td class="text-end fs-5"><strong>{{ formatAmount(totals.montant_ttc) }} €</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Options Finales -->
                    <div class="border-top pt-3 mt-3">
                        <div class="form-check mb-2">
                            <input
                                v-model="sendEmail"
                                type="checkbox"
                                class="form-check-input"
                                id="sendEmail"
                            >
                            <label class="form-check-label" for="sendEmail">
                                <i class="fas fa-envelope me-1"></i>
                                Envoyer la facture par email au client après création
                            </label>
                        </div>
                        <div class="form-check">
                            <input
                                v-model="generatePDF"
                                type="checkbox"
                                class="form-check-input"
                                id="generatePDF"
                                checked
                            >
                            <label class="form-check-label" for="generatePDF">
                                <i class="fas fa-file-pdf me-1"></i>
                                Générer automatiquement le PDF
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button type="button" @click="previousStep" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Précédent
                    </button>
                    <button type="submit" class="btn btn-success btn-lg" :disabled="processing">
                        <span v-if="processing">
                            <span class="spinner-border spinner-border-sm me-2"></span>
                            Création en cours...
                        </span>
                        <span v-else>
                            <i class="fas fa-check me-2"></i>Créer la Facture
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </ClientLayout>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';

export default {
    components: {
        ClientLayout
    },

    props: {
        clients: {
            type: Array,
            required: true
        },
        errors: {
            type: Object,
            default: () => ({})
        }
    },

    setup(props) {
        const step = ref(1);
        const processing = ref(false);
        const sendEmail = ref(false);
        const generatePDF = ref(true);
        const clientContrats = ref([]);

        const form = reactive({
            client_id: '',
            contrat_id: '',
            numero_facture: '',
            date_emission: new Date().toISOString().split('T')[0],
            date_echeance: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
            type_facture: 'normale',
            taux_tva: 20,
            statut: 'en_attente',
            notes: '',
            avec_remise: false,
            remise_pourcentage: 0,
            lignes: [
                {
                    designation: '',
                    quantite: 1,
                    prix_unitaire_ht: 0,
                    montant_total_ht: 0
                }
            ]
        });

        const selectedClient = computed(() => {
            return props.clients.find(c => c.id === form.client_id);
        });

        const selectedContrat = computed(() => {
            return clientContrats.value.find(c => c.id === form.contrat_id);
        });

        const totals = computed(() => {
            const montant_ht = form.lignes.reduce((sum, ligne) => sum + (ligne.montant_total_ht || 0), 0);
            const remise = form.avec_remise ? (montant_ht * form.remise_pourcentage / 100) : 0;
            const montant_ht_apres_remise = montant_ht - remise;
            const montant_tva = montant_ht_apres_remise * form.taux_tva / 100;
            const montant_ttc = montant_ht_apres_remise + montant_tva;

            return {
                montant_ht,
                remise,
                montant_ht_apres_remise,
                montant_tva,
                montant_ttc
            };
        });

        const formatAmount = (amount) => {
            if (!amount && amount !== 0) return '0.00';
            return parseFloat(amount).toFixed(2);
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        };

        const loadClientContrats = async () => {
            if (!form.client_id) {
                clientContrats.value = [];
                return;
            }

            // Simulation - à remplacer par vraie requête
            router.get(route('admin.clients.contrats', form.client_id), {}, {
                preserveState: true,
                only: ['contrats'],
                onSuccess: (page) => {
                    clientContrats.value = page.props.contrats || [];
                }
            });
        };

        const calculateLigneTotal = (ligne) => {
            ligne.montant_total_ht = (ligne.quantite || 0) * (ligne.prix_unitaire_ht || 0);
            calculateTotals();
        };

        const calculateTotals = () => {
            // Force recalcul des totaux via computed
            form.lignes = [...form.lignes];
        };

        const addLigne = () => {
            form.lignes.push({
                designation: '',
                quantite: 1,
                prix_unitaire_ht: 0,
                montant_total_ht: 0
            });
        };

        const removeLigne = (index) => {
            if (form.lignes.length > 1) {
                form.lignes.splice(index, 1);
                calculateTotals();
            }
        };

        const addTemplateLoyer = () => {
            if (selectedContrat.value) {
                form.lignes.push({
                    designation: `Loyer mensuel - Box ${selectedContrat.value.box?.numero || ''}`,
                    quantite: 1,
                    prix_unitaire_ht: selectedContrat.value.montant_loyer || 0,
                    montant_total_ht: selectedContrat.value.montant_loyer || 0
                });
            } else {
                form.lignes.push({
                    designation: 'Loyer mensuel',
                    quantite: 1,
                    prix_unitaire_ht: 0,
                    montant_total_ht: 0
                });
            }
            calculateTotals();
        };

        const addTemplateDepot = () => {
            if (selectedContrat.value) {
                form.lignes.push({
                    designation: `Dépôt de garantie - Box ${selectedContrat.value.box?.numero || ''}`,
                    quantite: 1,
                    prix_unitaire_ht: selectedContrat.value.depot_garantie || 0,
                    montant_total_ht: selectedContrat.value.depot_garantie || 0
                });
            } else {
                form.lignes.push({
                    designation: 'Dépôt de garantie',
                    quantite: 1,
                    prix_unitaire_ht: 0,
                    montant_total_ht: 0
                });
            }
            calculateTotals();
        };

        const addTemplateFrais = () => {
            form.lignes.push({
                designation: 'Frais de gestion',
                quantite: 1,
                prix_unitaire_ht: 15.00,
                montant_total_ht: 15.00
            });
            calculateTotals();
        };

        const nextStep = () => {
            if (step.value < 4) {
                step.value++;
            }
        };

        const previousStep = () => {
            if (step.value > 1) {
                step.value--;
            }
        };

        const getTypeLabel = (type) => {
            const labels = {
                'normale': 'Facture Normale',
                'acompte': "Facture d'Acompte",
                'solde': 'Facture de Solde',
                'avoir': 'Avoir'
            };
            return labels[type] || type;
        };

        const getStatusBadge = (statut) => {
            const badges = {
                'en_attente': 'badge bg-warning',
                'envoyee': 'badge bg-info',
                'payee': 'badge bg-success'
            };
            return badges[statut] || 'badge bg-secondary';
        };

        const getStatusLabel = (statut) => {
            const labels = {
                'en_attente': 'En attente',
                'envoyee': 'Envoyée',
                'payee': 'Payée'
            };
            return labels[statut] || statut;
        };

        const submitForm = () => {
            processing.value = true;

            const data = {
                ...form,
                montant_ht: totals.value.montant_ht_apres_remise,
                montant_tva: totals.value.montant_tva,
                montant_ttc: totals.value.montant_ttc,
                send_email: sendEmail.value,
                generate_pdf: generatePDF.value
            };

            router.post(route('admin.factures.store'), data, {
                onSuccess: () => {
                    processing.value = false;
                },
                onError: () => {
                    processing.value = false;
                    step.value = 1; // Retour à la première étape en cas d'erreur
                }
            });
        };

        return {
            step,
            form,
            processing,
            sendEmail,
            generatePDF,
            clientContrats,
            selectedClient,
            selectedContrat,
            totals,
            formatAmount,
            formatDate,
            loadClientContrats,
            calculateLigneTotal,
            calculateTotals,
            addLigne,
            removeLigne,
            addTemplateLoyer,
            addTemplateDepot,
            addTemplateFrais,
            nextStep,
            previousStep,
            getTypeLabel,
            getStatusBadge,
            getStatusLabel,
            submitForm
        };
    }
};
</script>

<style scoped>
/* Stepper */
.stepper-wrapper {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0;
}

.stepper-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
}

.stepper-item:not(:last-child)::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    right: -50%;
    height: 2px;
    background-color: #dee2e6;
    z-index: -1;
}

.stepper-item.completed:not(:last-child)::before {
    background-color: #198754;
}

.step-counter {
    position: relative;
    z-index: 5;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #dee2e6;
    color: #6c757d;
    font-weight: bold;
    margin-bottom: 8px;
}

.stepper-item.active .step-counter {
    background-color: #0d6efd;
    color: white;
}

.stepper-item.completed .step-counter {
    background-color: #198754;
    color: white;
}

.stepper-item.completed .step-counter::after {
    content: '\f00c';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
}

.step-name {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}

.stepper-item.active .step-name {
    color: #0d6efd;
    font-weight: 600;
}

.stepper-item.completed .step-name {
    color: #198754;
}

/* Tables */
.table-bordered th,
.table-bordered td {
    vertical-align: middle;
}

/* Responsive */
@media (max-width: 768px) {
    .stepper-wrapper {
        flex-direction: column;
    }

    .stepper-item:not(:last-child)::before {
        left: 20px;
        top: 50%;
        bottom: -50%;
        right: auto;
        width: 2px;
        height: 100%;
    }
}
</style>
