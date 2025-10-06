<template>
    <ClientLayout title="Créer un mandat SEPA">
        <div class="mb-4">
            <h1 class="h3">
                <i class="fas fa-file-signature me-2"></i>
                Nouveau Mandat SEPA
            </h1>
            <p class="text-muted">Autorisez les prélèvements automatiques pour vos paiements</p>
        </div>

        <!-- Étapes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="steps-indicator d-flex justify-content-between mb-4">
                            <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
                                <div class="step-number">1</div>
                                <div class="step-label">Informations bancaires</div>
                            </div>
                            <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
                                <div class="step-number">2</div>
                                <div class="step-label">Vérification</div>
                            </div>
                            <div class="step" :class="{ active: currentStep >= 3 }">
                                <div class="step-number">3</div>
                                <div class="step-label">Signature</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 1: Informations bancaires -->
        <div v-if="currentStep === 1" class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-university me-2"></i>
                            Coordonnées bancaires
                        </h5>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="nextStep">
                            <div class="mb-3">
                                <label for="titulaire" class="form-label">Titulaire du compte *</label>
                                <input
                                    type="text"
                                    v-model="form.titulaire"
                                    @blur="v$.form.titulaire.$touch"
                                    class="form-control"
                                    :class="getValidationClass('titulaire')"
                                    id="titulaire"
                                    placeholder="Nom du titulaire"
                                >
                                <div v-if="getErrorMessage('titulaire')" class="invalid-feedback">
                                    {{ getErrorMessage('titulaire') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="iban" class="form-label">IBAN *</label>
                                <input
                                    type="text"
                                    v-model="form.iban"
                                    @blur="v$.form.iban.$touch"
                                    @input="formatIban"
                                    class="form-control"
                                    :class="getValidationClass('iban')"
                                    id="iban"
                                    placeholder="FR76 XXXX XXXX XXXX XXXX XXXX XXX"
                                    maxlength="34"
                                >
                                <div v-if="getErrorMessage('iban')" class="invalid-feedback">
                                    {{ getErrorMessage('iban') }}
                                </div>
                                <small class="text-muted">Format: 27 caractères pour un IBAN français</small>
                            </div>

                            <div class="mb-3">
                                <label for="bic" class="form-label">BIC/SWIFT *</label>
                                <input
                                    type="text"
                                    v-model="form.bic"
                                    @blur="v$.form.bic.$touch"
                                    class="form-control"
                                    :class="getValidationClass('bic')"
                                    id="bic"
                                    placeholder="BNPAFRPPXXX"
                                    maxlength="11"
                                >
                                <div v-if="getErrorMessage('bic')" class="invalid-feedback">
                                    {{ getErrorMessage('bic') }}
                                </div>
                                <small class="text-muted">Code BIC de votre banque (8 ou 11 caractères)</small>
                            </div>

                            <div class="mb-3">
                                <label for="banque" class="form-label">Nom de la banque</label>
                                <input
                                    type="text"
                                    v-model="form.banque"
                                    class="form-control"
                                    id="banque"
                                    placeholder="Ex: BNP Paribas"
                                >
                            </div>

                            <div class="d-flex justify-content-between">
                                <a :href="route('client.sepa')" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    Suivant<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle me-2"></i>Information</h6>
                        <p class="small mb-2">Le mandat SEPA vous permet d'autoriser les prélèvements automatiques.</p>
                        <hr>
                        <h6><i class="fas fa-shield-alt me-2"></i>Sécurité</h6>
                        <p class="small mb-0">Vos données bancaires sont cryptées et sécurisées.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 2: Vérification -->
        <div v-if="currentStep === 2" class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Vérification des informations
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Veuillez vérifier attentivement les informations avant de continuer
                        </div>

                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="40%">Titulaire:</th>
                                    <td>{{ form.titulaire }}</td>
                                </tr>
                                <tr>
                                    <th>IBAN:</th>
                                    <td><code>{{ form.iban }}</code></td>
                                </tr>
                                <tr>
                                    <th>BIC:</th>
                                    <td><code>{{ form.bic }}</code></td>
                                </tr>
                                <tr v-if="form.banque">
                                    <th>Banque:</th>
                                    <td>{{ form.banque }}</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="d-flex justify-content-between mt-4">
                            <button @click="currentStep = 1" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </button>
                            <button @click="currentStep = 3" class="btn btn-primary">
                                Continuer<i class="fas fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-question-circle me-2"></i>Une erreur ?</h6>
                        <p class="small">Vous pouvez retourner à l'étape précédente pour modifier les informations.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étape 3: Signature -->
        <div v-if="currentStep === 3" class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-pen-fancy me-2"></i>
                            Signature du mandat
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention:</strong> En signant, vous autorisez les prélèvements automatiques selon les conditions du mandat SEPA.
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Signature électronique *</label>
                            <div class="signature-pad border rounded" :class="{ 'is-invalid': signatureError }">
                                <canvas ref="signatureCanvas" class="signature-canvas"></canvas>
                            </div>
                            <div v-if="signatureError" class="invalid-feedback d-block">
                                {{ signatureError }}
                            </div>
                            <div class="mt-2">
                                <button @click="clearSignature" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eraser me-2"></i>Effacer
                                </button>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                v-model="form.accepte_conditions"
                                class="form-check-input"
                                id="accepte_conditions"
                            >
                            <label class="form-check-label" for="accepte_conditions">
                                J'accepte les <a href="#" target="_blank">conditions générales</a> du mandat SEPA *
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button @click="currentStep = 2" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </button>
                            <button @click="submitMandat" class="btn btn-success" :disabled="processing">
                                <i class="fas fa-check me-2"></i>
                                <span v-if="processing">Enregistrement...</span>
                                <span v-else>Valider le mandat</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6><i class="fas fa-file-contract me-2"></i>Mandat SEPA</h6>
                        <p class="small mb-2">Le mandat sera enregistré et vous pourrez le consulter à tout moment.</p>
                        <hr>
                        <h6><i class="fas fa-undo me-2"></i>Révocation</h6>
                        <p class="small mb-0">Vous pouvez révoquer ce mandat à tout moment depuis votre espace.</p>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>

<script>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { router } from '@inertiajs/vue3';
import { useVuelidate } from '@vuelidate/core';
import { required, minLength, maxLength, helpers } from '@vuelidate/validators';
import SignaturePad from 'signature_pad';

// Validation IBAN
const validIban = (value) => {
    if (!value) return true;
    const iban = value.replace(/\s/g, '');
    return /^[A-Z]{2}[0-9]{2}[A-Z0-9]+$/.test(iban) && iban.length >= 15 && iban.length <= 34;
};

// Validation BIC
const validBic = (value) => {
    if (!value) return true;
    return /^[A-Z]{6}[A-Z0-9]{2}([A-Z0-9]{3})?$/.test(value);
};

export default {
    components: {
        ClientLayout
    },

    setup() {
        return { v$: useVuelidate() };
    },

    data() {
        return {
            currentStep: 1,
            processing: false,
            signaturePad: null,
            signatureError: '',
            form: {
                titulaire: '',
                iban: '',
                bic: '',
                banque: '',
                accepte_conditions: false
            }
        };
    },

    validations() {
        return {
            form: {
                titulaire: {
                    required: helpers.withMessage('Le nom du titulaire est requis', required),
                    minLength: helpers.withMessage('Le nom doit contenir au moins 3 caractères', minLength(3)),
                    maxLength: helpers.withMessage('Le nom ne peut pas dépasser 100 caractères', maxLength(100))
                },
                iban: {
                    required: helpers.withMessage('L\'IBAN est requis', required),
                    validIban: helpers.withMessage('L\'IBAN n\'est pas valide', validIban)
                },
                bic: {
                    required: helpers.withMessage('Le BIC est requis', required),
                    validBic: helpers.withMessage('Le BIC n\'est pas valide', validBic)
                }
            }
        };
    },

    mounted() {
        this.initSignaturePad();
    },

    methods: {
        async nextStep() {
            const isValid = await this.v$.$validate();

            if (!isValid) {
                this.$toast.warning('Veuillez corriger les erreurs dans le formulaire');
                return;
            }

            this.currentStep = 2;
        },

        formatIban() {
            // Retirer tous les espaces
            let iban = this.form.iban.replace(/\s/g, '').toUpperCase();
            // Ajouter un espace tous les 4 caractères
            iban = iban.match(/.{1,4}/g)?.join(' ') || iban;
            this.form.iban = iban;
        },

        initSignaturePad() {
            this.$nextTick(() => {
                const canvas = this.$refs.signatureCanvas;
                if (canvas) {
                    canvas.width = canvas.offsetWidth;
                    canvas.height = 200;
                    this.signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)'
                    });
                }
            });
        },

        clearSignature() {
            if (this.signaturePad) {
                this.signaturePad.clear();
                this.signatureError = '';
            }
        },

        submitMandat() {
            // Validation signature
            if (!this.signaturePad || this.signaturePad.isEmpty()) {
                this.signatureError = 'La signature est requise';
                return;
            }

            // Validation conditions
            if (!this.form.accepte_conditions) {
                this.$toast.error('Vous devez accepter les conditions générales');
                return;
            }

            this.processing = true;
            this.signatureError = '';

            // Récupérer la signature en base64
            const signatureData = this.signaturePad.toDataURL();

            // Envoyer les données
            router.post(route('client.sepa.store'), {
                ...this.form,
                signature: signatureData
            }, {
                onSuccess: () => {
                    this.processing = false;
                    this.$toast.success('Mandat SEPA créé avec succès !');
                },
                onError: (errors) => {
                    this.processing = false;
                    this.$toast.error('Une erreur est survenue lors de la création du mandat');
                }
            });
        },

        getValidationClass(field) {
            if (!this.v$.form[field].$dirty) return '';
            return this.v$.form[field].$error ? 'is-invalid' : 'is-valid';
        },

        getErrorMessage(field) {
            if (!this.v$.form[field].$dirty || !this.v$.form[field].$error) return '';
            return this.v$.form[field].$errors[0].$message;
        }
    }
};
</script>

<style scoped>
.steps-indicator {
    position: relative;
}

.steps-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 25%;
    right: 25%;
    height: 2px;
    background: #dee2e6;
    z-index: 0;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.step.active .step-number {
    background: #0d6efd;
    color: white;
}

.step.completed .step-number {
    background: #198754;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-align: center;
}

.step.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.signature-pad {
    background: #f8f9fa;
    cursor: crosshair;
}

.signature-canvas {
    width: 100%;
    height: 200px;
}

.dark-mode .signature-pad {
    background: #2b3035;
}

.dark-mode .signature-canvas {
    filter: invert(1);
}
</style>
