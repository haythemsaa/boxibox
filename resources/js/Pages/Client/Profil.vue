<template>
    <ClientLayout title="Mon Profil">
        <div class="mb-4">
            <h1 class="h3">Mon Profil</h1>
            <p class="text-muted">Gérez vos informations personnelles</p>
        </div>

        <div class="row">
            <!-- Informations Client -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="updateProfile">
                            <div class="mb-3">
                                <label class="form-label">Nom Complet</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    :value="`${client.prenom} ${client.nom}`"
                                    readonly
                                >
                                <small class="text-muted">Pour modifier votre nom, contactez notre service.</small>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.email }"
                                    id="email"
                                    required
                                >
                                <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input
                                        type="text"
                                        v-model="form.telephone"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.telephone }"
                                        id="telephone"
                                    >
                                    <div v-if="errors.telephone" class="invalid-feedback">{{ errors.telephone }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="mobile" class="form-label">Mobile</label>
                                    <input
                                        type="text"
                                        v-model="form.mobile"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.mobile }"
                                        id="mobile"
                                    >
                                    <div v-if="errors.mobile" class="invalid-feedback">{{ errors.mobile }}</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea
                                    v-model="form.adresse"
                                    class="form-control"
                                    :class="{ 'is-invalid': errors.adresse }"
                                    id="adresse"
                                    rows="2"
                                ></textarea>
                                <div v-if="errors.adresse" class="invalid-feedback">{{ errors.adresse }}</div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="code_postal" class="form-label">Code Postal</label>
                                    <input
                                        type="text"
                                        v-model="form.code_postal"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.code_postal }"
                                        id="code_postal"
                                    >
                                    <div v-if="errors.code_postal" class="invalid-feedback">{{ errors.code_postal }}</div>
                                </div>
                                <div class="col-md-5">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input
                                        type="text"
                                        v-model="form.ville"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.ville }"
                                        id="ville"
                                    >
                                    <div v-if="errors.ville" class="invalid-feedback">{{ errors.ville }}</div>
                                </div>
                                <div class="col-md-3">
                                    <label for="pays" class="form-label">Pays</label>
                                    <input
                                        type="text"
                                        v-model="form.pays"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.pays }"
                                        id="pays"
                                    >
                                    <div v-if="errors.pays" class="invalid-feedback">{{ errors.pays }}</div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" :disabled="processing">
                                    <i class="fas fa-save me-2"></i>
                                    <span v-if="processing">Enregistrement...</span>
                                    <span v-else>Enregistrer les modifications</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informations Compte -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-lock me-2"></i>Compte Utilisateur</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Email de connexion:</th>
                                    <td>{{ user.email }}</td>
                                </tr>
                                <tr>
                                    <th>Dernière connexion:</th>
                                    <td>{{ user.last_login_at ? formatDateTime(user.last_login_at) : 'Jamais' }}</td>
                                </tr>
                                <tr>
                                    <th>Compte créé le:</th>
                                    <td>{{ formatDate(user.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <hr>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Changement de mot de passe</strong><br>
                            Pour modifier votre mot de passe, rendez-vous dans les paramètres de votre profil utilisateur.
                        </div>

                        <a :href="route('profile.edit')" class="btn btn-outline-primary w-100">
                            <i class="fas fa-key me-2"></i>Gérer mon mot de passe
                        </a>
                    </div>
                </div>

                <div v-if="client.type_client === 'entreprise'" class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Informations Entreprise</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Raison Sociale:</th>
                                    <td>{{ client.raison_sociale || 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>SIRET:</th>
                                    <td>{{ client.siret || 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>N° TVA:</th>
                                    <td>{{ client.numero_tva || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <small class="text-muted">
                            Pour modifier ces informations, contactez notre service commercial.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aide -->
        <div class="card bg-light">
            <div class="card-body">
                <h5><i class="fas fa-question-circle me-2"></i>Besoin d'aide ?</h5>
                <p class="mb-0">
                    Pour toute question ou modification ne pouvant être effectuée directement,
                    n'hésitez pas à contacter notre service client.
                </p>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { router } from '@inertiajs/vue3';

export default {
    components: {
        ClientLayout
    },

    props: {
        client: {
            type: Object,
            required: true
        },
        user: {
            type: Object,
            required: true
        },
        errors: {
            type: Object,
            default: () => ({})
        }
    },

    data() {
        return {
            processing: false,
            form: {
                email: this.client.email || '',
                telephone: this.client.telephone || '',
                mobile: this.client.mobile || '',
                adresse: this.client.adresse || '',
                code_postal: this.client.code_postal || '',
                ville: this.client.ville || '',
                pays: this.client.pays || 'France'
            }
        };
    },

    methods: {
        updateProfile() {
            this.processing = true;

            router.put(route('client.profil.update'), this.form, {
                onSuccess: () => {
                    this.processing = false;
                },
                onError: () => {
                    this.processing = false;
                }
            });
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('fr-FR');
        },

        formatDateTime(date) {
            if (!date) return '';
            const d = new Date(date);
            return `${d.toLocaleDateString('fr-FR')} ${d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`;
        }
    }
};
</script>
