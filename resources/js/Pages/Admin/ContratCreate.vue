<template>
    <ClientLayout title="Créer un Contrat">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3">
                    <i class="fas fa-file-contract me-2"></i>
                    Créer un Nouveau Contrat
                </h1>
                <a :href="route('contrats.index')" class="btn btn-secondary">
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
                        <div class="step-name">Client</div>
                    </div>
                    <div class="stepper-item" :class="{ completed: step > 2, active: step === 2 }">
                        <div class="step-counter">2</div>
                        <div class="step-name">Box</div>
                    </div>
                    <div class="stepper-item" :class="{ completed: step > 3, active: step === 3 }">
                        <div class="step-counter">3</div>
                        <div class="step-name">Détails</div>
                    </div>
                    <div class="stepper-item" :class="{ active: step === 4 }">
                        <div class="step-counter">4</div>
                        <div class="step-name">Validation</div>
                    </div>
                </div>
            </div>
        </div>

        <form @submit.prevent="submitForm">
            <!-- Étape 1 : Sélection du Client -->
            <div v-show="step === 1" class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>
                        Sélection du Client
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Client *</label>
                            <select
                                v-model="form.client_id"
                                @change="loadClientInfo"
                                class="form-select"
                                :class="{ 'is-invalid': errors.client_id }"
                                required
                            >
                                <option value="">Sélectionnez un client...</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.prenom }} {{ client.nom }} - {{ client.email }}
                                    <span v-if="client.type_client">({{ client.type_client }})</span>
                                </option>
                            </select>
                            <div v-if="errors.client_id" class="invalid-feedback">{{ errors.client_id }}</div>
                        </div>
                    </div>

                    <!-- Nouveau Client -->
                    <div class="border-top pt-3">
                        <p class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Le client n'existe pas encore ?
                            <a :href="route('admin.clients.create')" class="ms-2">Créer un nouveau client</a>
                        </p>
                    </div>

                    <!-- Infos Client Sélectionné -->
                    <div v-if="selectedClient" class="alert alert-info mt-3">
                        <h6 class="alert-heading">
                            <i class="fas fa-user-check me-2"></i>
                            Client Sélectionné
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Nom :</strong> {{ selectedClient.prenom }} {{ selectedClient.nom }}</p>
                                <p class="mb-1"><strong>Email :</strong> {{ selectedClient.email }}</p>
                                <p class="mb-1"><strong>Téléphone :</strong> {{ selectedClient.telephone || 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Adresse :</strong> {{ selectedClient.adresse || 'N/A' }}</p>
                                <p class="mb-1"><strong>Type :</strong>
                                    <span class="badge" :class="selectedClient.type_client === 'particulier' ? 'bg-primary' : 'bg-info'">
                                        {{ selectedClient.type_client || 'Particulier' }}
                                    </span>
                                </p>
                                <p class="mb-0"><strong>Contrats actifs :</strong> {{ clientStats.contrats_actifs || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="button" @click="nextStep" class="btn btn-primary" :disabled="!form.client_id">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 2 : Sélection du Box -->
            <div v-show="step === 2" class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-box me-2"></i>
                        Sélection du Box
                    </h5>
                    <div>
                        <button type="button" @click="filterBoxes('')" class="btn btn-sm" :class="boxFilter === '' ? 'btn-primary' : 'btn-outline-secondary'">
                            Tous
                        </button>
                        <button type="button" @click="filterBoxes('libre')" class="btn btn-sm ms-1" :class="boxFilter === 'libre' ? 'btn-success' : 'btn-outline-success'">
                            Libres
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Recherche Box -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input
                                v-model="boxSearch"
                                type="text"
                                class="form-control"
                                placeholder="Rechercher N° box..."
                            >
                        </div>
                        <div class="col-md-4">
                            <select v-model="familleFilter" class="form-select">
                                <option value="">Toutes les familles</option>
                                <option v-for="famille in familles" :key="famille.id" :value="famille.id">
                                    {{ famille.nom }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Liste des Boxes -->
                    <div class="row">
                        <div
                            v-for="box in filteredBoxes"
                            :key="box.id"
                            class="col-md-4 mb-3"
                        >
                            <div
                                class="card box-card"
                                :class="{
                                    'border-primary': form.box_id === box.id,
                                    'border-success': box.statut === 'libre' && form.box_id !== box.id,
                                    'border-secondary': box.statut !== 'libre' && form.box_id !== box.id
                                }"
                                @click="selectBox(box)"
                                style="cursor: pointer;"
                            >
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">
                                            <i class="fas fa-box-open me-1"></i>
                                            Box {{ box.numero }}
                                        </h6>
                                        <span
                                            class="badge"
                                            :style="{ backgroundColor: box.famille?.couleur || '#6c757d' }"
                                        >
                                            {{ box.famille?.nom || 'N/A' }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ box.emplacement?.nom || 'N/A' }}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge" :class="getBoxStatusBadge(box.statut)">
                                                {{ getBoxStatusLabel(box.statut) }}
                                            </span>
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">{{ box.surface }} m²</small>
                                            <strong class="text-primary">{{ formatAmount(box.tarif_mensuel) }}€/mois</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="filteredBoxes.length === 0" class="col-12">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Aucun box disponible avec ces critères
                            </div>
                        </div>
                    </div>

                    <!-- Box Sélectionné -->
                    <div v-if="selectedBox" class="alert alert-success mt-3">
                        <h6 class="alert-heading">
                            <i class="fas fa-check-circle me-2"></i>
                            Box Sélectionné : {{ selectedBox.numero }}
                        </h6>
                        <div class="row">
                            <div class="col-md-3">
                                <p class="mb-0"><strong>Surface :</strong> {{ selectedBox.surface }} m²</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-0"><strong>Volume :</strong> {{ selectedBox.volume || 'N/A' }} m³</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-0"><strong>Famille :</strong> {{ selectedBox.famille?.nom }}</p>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-0"><strong>Tarif :</strong> {{ formatAmount(selectedBox.tarif_mensuel) }}€/mois</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button type="button" @click="previousStep" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Précédent
                    </button>
                    <button type="button" @click="nextStep" class="btn btn-primary" :disabled="!form.box_id">
                        Suivant <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>

            <!-- Étape 3 : Détails du Contrat -->
            <div v-show="step === 3" class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>
                        Détails du Contrat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Numéro de Contrat</label>
                            <input
                                v-model="form.numero_contrat"
                                type="text"
                                class="form-control"
                                placeholder="AUTO"
                            >
                            <small class="text-muted">Laissez vide pour génération automatique</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date de Début *</label>
                            <input
                                v-model="form.date_debut"
                                type="date"
                                class="form-control"
                                :class="{ 'is-invalid': errors.date_debut }"
                                required
                            >
                            <div v-if="errors.date_debut" class="invalid-feedback">{{ errors.date_debut }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Durée (mois)</label>
                            <input
                                v-model.number="form.duree_mois"
                                @input="calculateDateFin"
                                type="number"
                                class="form-control"
                                min="1"
                                placeholder="Ex: 12"
                            >
                            <small class="text-muted">Laissez vide pour durée indéterminée</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date de Fin</label>
                            <input
                                v-model="form.date_fin"
                                type="date"
                                class="form-control"
                                :disabled="!form.duree_mois"
                            >
                            <small class="text-muted">Calculée automatiquement si durée définie</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Loyer Mensuel (€) *</label>
                            <input
                                v-model.number="form.montant_loyer"
                                type="number"
                                class="form-control"
                                :class="{ 'is-invalid': errors.montant_loyer }"
                                step="0.01"
                                min="0"
                                required
                            >
                            <small class="text-success">Tarif box: {{ formatAmount(selectedBox?.tarif_mensuel) }}€</small>
                            <div v-if="errors.montant_loyer" class="invalid-feedback">{{ errors.montant_loyer }}</div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dépôt de Garantie (€)</label>
                            <input
                                v-model.number="form.depot_garantie"
                                type="number"
                                class="form-control"
                                step="0.01"
                                min="0"
                            >
                            <div class="form-check mt-1">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="autoDepot"
                                    @change="calculateDepotAuto"
                                >
                                <label class="form-check-label small" for="autoDepot">
                                    = 1 mois de loyer
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type de Contrat</label>
                            <select v-model="form.type_contrat" class="form-select">
                                <option value="stockage">Stockage Simple</option>
                                <option value="archive">Archivage</option>
                                <option value="professionnel">Professionnel</option>
                                <option value="particulier">Particulier</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statut</label>
                            <select v-model="form.statut" class="form-select">
                                <option value="actif">Actif</option>
                                <option value="en_attente">En attente de signature</option>
                                <option value="suspendu">Suspendu</option>
                            </select>
                        </div>
                    </div>

                    <!-- Options Avancées -->
                    <div class="border-top pt-3 mt-3">
                        <h6 class="mb-3">Options et Services</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input
                                        v-model="form.options.assurance"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="assurance"
                                    >
                                    <label class="form-check-label" for="assurance">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Assurance (+ 10€/mois)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input
                                        v-model="form.options.acces_24h"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="acces24h"
                                    >
                                    <label class="form-check-label" for="acces24h">
                                        <i class="fas fa-clock me-1"></i>
                                        Accès 24h/24
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input
                                        v-model="form.options.climatisation"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="climatisation"
                                    >
                                    <label class="form-check-label" for="climatisation">
                                        <i class="fas fa-snowflake me-1"></i>
                                        Climatisation (+ 15€/mois)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input
                                        v-model="form.options.alarme"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="alarme"
                                    >
                                    <label class="form-check-label" for="alarme">
                                        <i class="fas fa-bell me-1"></i>
                                        Alarme individuelle (+ 5€/mois)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input
                                        v-model="form.options.electricite"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="electricite"
                                    >
                                    <label class="form-check-label" for="electricite">
                                        <i class="fas fa-plug me-1"></i>
                                        Prise électrique (+ 8€/mois)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input
                                        v-model="form.options.prelevement_auto"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="prelevement"
                                    >
                                    <label class="form-check-label" for="prelevement">
                                        <i class="fas fa-credit-card me-1"></i>
                                        Prélèvement automatique
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Total Options -->
                        <div class="alert alert-light mt-3" v-if="montantOptions > 0">
                            <strong>Total options :</strong> + {{ formatAmount(montantOptions) }}€/mois
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="border-top pt-3 mt-3">
                        <label class="form-label">Notes / Conditions Particulières</label>
                        <textarea
                            v-model="form.notes"
                            class="form-control"
                            rows="3"
                            placeholder="Ajouter des notes ou conditions particulières..."
                        ></textarea>
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

            <!-- Étape 4 : Validation -->
            <div v-show="step === 4" class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        Récapitulatif et Validation
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Client -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">Client</h6>
                            <p class="mb-1"><strong>Nom :</strong> {{ selectedClient?.prenom }} {{ selectedClient?.nom }}</p>
                            <p class="mb-1"><strong>Email :</strong> {{ selectedClient?.email }}</p>
                            <p class="mb-1"><strong>Téléphone :</strong> {{ selectedClient?.telephone }}</p>
                            <p class="mb-0"><strong>Type :</strong> {{ selectedClient?.type_client || 'Particulier' }}</p>
                        </div>

                        <!-- Box -->
                        <div class="col-md-6 mb-4">
                            <h6 class="text-primary mb-3">Box</h6>
                            <p class="mb-1"><strong>Numéro :</strong> Box {{ selectedBox?.numero }}</p>
                            <p class="mb-1"><strong>Famille :</strong>
                                <span class="badge" :style="{ backgroundColor: selectedBox?.famille?.couleur }">
                                    {{ selectedBox?.famille?.nom }}
                                </span>
                            </p>
                            <p class="mb-1"><strong>Surface :</strong> {{ selectedBox?.surface }} m²</p>
                            <p class="mb-0"><strong>Emplacement :</strong> {{ selectedBox?.emplacement?.nom }}</p>
                        </div>
                    </div>

                    <!-- Détails Contrat -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Détails du Contrat</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="mb-1"><strong>N° Contrat :</strong> {{ form.numero_contrat || 'AUTO' }}</p>
                                <p class="mb-1"><strong>Type :</strong> {{ form.type_contrat }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Date début :</strong> {{ formatDate(form.date_debut) }}</p>
                                <p class="mb-1"><strong>Date fin :</strong> {{ form.date_fin ? formatDate(form.date_fin) : 'Indéterminée' }}</p>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><strong>Durée :</strong> {{ form.duree_mois || 'Indéterminée' }} mois</p>
                                <p class="mb-1"><strong>Statut :</strong> <span :class="getStatusBadge(form.statut)">{{ getStatusLabel(form.statut) }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Montants -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">Montants</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="60%">Loyer mensuel de base :</td>
                                <td class="text-end"><strong>{{ formatAmount(form.montant_loyer) }} €</strong></td>
                            </tr>
                            <tr v-if="montantOptions > 0">
                                <td>Options :</td>
                                <td class="text-end">+ {{ formatAmount(montantOptions) }} €</td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>Total mensuel :</strong></td>
                                <td class="text-end"><strong class="fs-5">{{ formatAmount(totalMensuel) }} €</strong></td>
                            </tr>
                            <tr>
                                <td>Dépôt de garantie :</td>
                                <td class="text-end">{{ formatAmount(form.depot_garantie) }} €</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Options -->
                    <div v-if="optionsSelectionnees.length > 0" class="mb-4">
                        <h6 class="text-primary mb-3">Options Sélectionnées</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span v-for="option in optionsSelectionnees" :key="option" class="badge bg-info">
                                {{ option }}
                            </span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="form.notes" class="mb-4">
                        <h6 class="text-primary mb-3">Notes</h6>
                        <p class="text-muted">{{ form.notes }}</p>
                    </div>

                    <!-- Options Finales -->
                    <div class="border-top pt-3">
                        <div class="form-check mb-2">
                            <input
                                v-model="generateFacture"
                                type="checkbox"
                                class="form-check-input"
                                id="generateFacture"
                            >
                            <label class="form-check-label" for="generateFacture">
                                <i class="fas fa-file-invoice me-1"></i>
                                Générer la première facture (loyer + dépôt de garantie)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input
                                v-model="sendEmail"
                                type="checkbox"
                                class="form-check-input"
                                id="sendEmailContrat"
                            >
                            <label class="form-check-label" for="sendEmailContrat">
                                <i class="fas fa-envelope me-1"></i>
                                Envoyer le contrat par email au client
                            </label>
                        </div>
                        <div class="form-check">
                            <input
                                v-model="generatePDF"
                                type="checkbox"
                                class="form-check-input"
                                id="generatePDFContrat"
                                checked
                            >
                            <label class="form-check-label" for="generatePDFContrat">
                                <i class="fas fa-file-pdf me-1"></i>
                                Générer le PDF du contrat
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
                            <i class="fas fa-check me-2"></i>Créer le Contrat
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </ClientLayout>
</template>

<script>
import { ref, reactive, computed } from 'vue';
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
        boxes: {
            type: Array,
            required: true
        },
        familles: {
            type: Array,
            default: () => []
        },
        errors: {
            type: Object,
            default: () => ({})
        }
    },

    setup(props) {
        const step = ref(1);
        const processing = ref(false);
        const generateFacture = ref(true);
        const sendEmail = ref(false);
        const generatePDF = ref(true);

        const boxSearch = ref('');
        const boxFilter = ref('libre');
        const familleFilter = ref('');
        const clientStats = ref({});

        const form = reactive({
            client_id: '',
            box_id: '',
            numero_contrat: '',
            date_debut: new Date().toISOString().split('T')[0],
            date_fin: '',
            duree_mois: null,
            montant_loyer: 0,
            depot_garantie: 0,
            type_contrat: 'stockage',
            statut: 'actif',
            notes: '',
            options: {
                assurance: false,
                acces_24h: false,
                climatisation: false,
                alarme: false,
                electricite: false,
                prelevement_auto: false
            }
        });

        const selectedClient = computed(() => {
            return props.clients.find(c => c.id === form.client_id);
        });

        const selectedBox = computed(() => {
            return props.boxes.find(b => b.id === form.box_id);
        });

        const filteredBoxes = computed(() => {
            let filtered = props.boxes;

            // Filtre statut
            if (boxFilter.value) {
                filtered = filtered.filter(b => b.statut === boxFilter.value);
            }

            // Filtre recherche
            if (boxSearch.value) {
                filtered = filtered.filter(b =>
                    b.numero.toLowerCase().includes(boxSearch.value.toLowerCase())
                );
            }

            // Filtre famille
            if (familleFilter.value) {
                filtered = filtered.filter(b => b.famille_id === familleFilter.value);
            }

            return filtered;
        });

        const montantOptions = computed(() => {
            let total = 0;
            if (form.options.assurance) total += 10;
            if (form.options.climatisation) total += 15;
            if (form.options.alarme) total += 5;
            if (form.options.electricite) total += 8;
            return total;
        });

        const totalMensuel = computed(() => {
            return (form.montant_loyer || 0) + montantOptions.value;
        });

        const optionsSelectionnees = computed(() => {
            const opts = [];
            if (form.options.assurance) opts.push('Assurance');
            if (form.options.acces_24h) opts.push('Accès 24h/24');
            if (form.options.climatisation) opts.push('Climatisation');
            if (form.options.alarme) opts.push('Alarme');
            if (form.options.electricite) opts.push('Électricité');
            if (form.options.prelevement_auto) opts.push('Prélèvement auto');
            return opts;
        });

        const formatAmount = (amount) => {
            if (!amount && amount !== 0) return '0.00';
            return parseFloat(amount).toFixed(2);
        };

        const formatDate = (date) => {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        };

        const loadClientInfo = () => {
            if (form.client_id) {
                // Charger stats client (simulation)
                clientStats.value = {
                    contrats_actifs: Math.floor(Math.random() * 3)
                };
            }
        };

        const selectBox = (box) => {
            if (box.statut === 'libre' || confirm('Ce box n\'est pas libre. Continuer quand même ?')) {
                form.box_id = box.id;
                form.montant_loyer = box.tarif_mensuel || 0;
            }
        };

        const filterBoxes = (statut) => {
            boxFilter.value = statut;
        };

        const calculateDateFin = () => {
            if (form.date_debut && form.duree_mois) {
                const debut = new Date(form.date_debut);
                debut.setMonth(debut.getMonth() + form.duree_mois);
                form.date_fin = debut.toISOString().split('T')[0];
            } else {
                form.date_fin = '';
            }
        };

        const calculateDepotAuto = (event) => {
            if (event.target.checked) {
                form.depot_garantie = form.montant_loyer;
            }
        };

        const getBoxStatusBadge = (statut) => {
            const badges = {
                'libre': 'bg-success',
                'occupe': 'bg-danger',
                'reserve': 'bg-warning',
                'maintenance': 'bg-info'
            };
            return badges[statut] || 'bg-secondary';
        };

        const getBoxStatusLabel = (statut) => {
            const labels = {
                'libre': 'Libre',
                'occupe': 'Occupé',
                'reserve': 'Réservé',
                'maintenance': 'Maintenance'
            };
            return labels[statut] || statut;
        };

        const getStatusBadge = (statut) => {
            const badges = {
                'actif': 'badge bg-success',
                'en_attente': 'badge bg-warning',
                'suspendu': 'badge bg-secondary',
                'resilie': 'badge bg-danger'
            };
            return badges[statut] || 'badge bg-secondary';
        };

        const getStatusLabel = (statut) => {
            const labels = {
                'actif': 'Actif',
                'en_attente': 'En attente',
                'suspendu': 'Suspendu',
                'resilie': 'Résilié'
            };
            return labels[statut] || statut;
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

        const submitForm = () => {
            processing.value = true;

            const data = {
                ...form,
                total_mensuel: totalMensuel.value,
                generate_facture: generateFacture.value,
                send_email: sendEmail.value,
                generate_pdf: generatePDF.value
            };

            router.post(route('contrats.store'), data, {
                onSuccess: () => {
                    processing.value = false;
                },
                onError: () => {
                    processing.value = false;
                    step.value = 1;
                }
            });
        };

        return {
            step,
            form,
            processing,
            generateFacture,
            sendEmail,
            generatePDF,
            boxSearch,
            boxFilter,
            familleFilter,
            clientStats,
            selectedClient,
            selectedBox,
            filteredBoxes,
            montantOptions,
            totalMensuel,
            optionsSelectionnees,
            formatAmount,
            formatDate,
            loadClientInfo,
            selectBox,
            filterBoxes,
            calculateDateFin,
            calculateDepotAuto,
            getBoxStatusBadge,
            getBoxStatusLabel,
            getStatusBadge,
            getStatusLabel,
            nextStep,
            previousStep,
            submitForm
        };
    }
};
</script>

<style scoped>
/* Stepper (réutilisé de FactureCreate) */
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

.step-name {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}

.stepper-item.active .step-name {
    color: #0d6efd;
    font-weight: 600;
}

/* Box Cards */
.box-card {
    transition: all 0.3s;
}

.box-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.box-card.border-primary {
    border-width: 2px !important;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,.25);
}

@media (max-width: 768px) {
    .stepper-wrapper {
        flex-direction: column;
    }
}
</style>
