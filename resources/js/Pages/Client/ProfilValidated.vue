<template>
  <ClientLayout title="Mon Profil">
    <div class="mb-4">
      <h1 class="h3">Mon Profil</h1>
      <p class="text-muted">Gérez vos informations personnelles</p>
    </div>

    <!-- Messages de succès/erreur -->
    <div v-if="$page.props.flash?.success" class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i>{{ $page.props.flash.success }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <div class="row">
      <!-- Informations Client avec Validation -->
      <div class="col-md-6 mb-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h5>
          </div>
          <div class="card-body">
            <form @submit.prevent="submitForm">
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

              <!-- Email avec validation -->
              <FormInput
                id="email"
                v-model="form.email"
                type="email"
                label="Email"
                placeholder="votre@email.com"
                :required="true"
                :error="v$.email.$errors[0]?.$message"
                :external-error="errors.email"
                :touched="v$.email.$dirty"
                @blur="v$.email.$touch()"
                help-text="Votre adresse email principale de contact"
              />

              <!-- Téléphones avec validation -->
              <div class="row mb-3">
                <div class="col-md-6">
                  <FormInput
                    id="telephone"
                    v-model="form.telephone"
                    type="tel"
                    label="Téléphone"
                    placeholder="01 23 45 67 89"
                    :error="v$.telephone.$errors[0]?.$message"
                    :external-error="errors.telephone"
                    :touched="v$.telephone.$dirty"
                    @blur="v$.telephone.$touch()"
                  />
                </div>
                <div class="col-md-6">
                  <FormInput
                    id="mobile"
                    v-model="form.mobile"
                    type="tel"
                    label="Mobile"
                    placeholder="06 12 34 56 78"
                    :error="v$.mobile.$errors[0]?.$message"
                    :external-error="errors.mobile"
                    :touched="v$.mobile.$dirty"
                    @blur="v$.mobile.$touch()"
                  />
                </div>
              </div>

              <!-- Adresse -->
              <div class="mb-3">
                <label for="adresse" class="form-label">Adresse</label>
                <textarea
                  id="adresse"
                  v-model="form.adresse"
                  class="form-control"
                  :class="{
                    'is-invalid': v$.adresse.$errors.length || errors.adresse,
                    'is-valid': !v$.adresse.$errors.length && v$.adresse.$dirty && form.adresse
                  }"
                  rows="2"
                  @blur="v$.adresse.$touch()"
                ></textarea>
                <div v-if="v$.adresse.$errors[0]" class="invalid-feedback">
                  {{ v$.adresse.$errors[0].$message }}
                </div>
                <div v-else-if="errors.adresse" class="invalid-feedback">
                  {{ errors.adresse }}
                </div>
              </div>

              <!-- Code postal, Ville, Pays -->
              <div class="row mb-3">
                <div class="col-md-4">
                  <FormInput
                    id="code_postal"
                    v-model="form.code_postal"
                    type="text"
                    label="Code Postal"
                    placeholder="75001"
                    maxlength="10"
                    :error="v$.code_postal.$errors[0]?.$message"
                    :external-error="errors.code_postal"
                    :touched="v$.code_postal.$dirty"
                    @blur="v$.code_postal.$touch()"
                  />
                </div>
                <div class="col-md-5">
                  <FormInput
                    id="ville"
                    v-model="form.ville"
                    type="text"
                    label="Ville"
                    placeholder="Paris"
                    :error="v$.ville.$errors[0]?.$message"
                    :external-error="errors.ville"
                    :touched="v$.ville.$dirty"
                    @blur="v$.ville.$touch()"
                  />
                </div>
                <div class="col-md-3">
                  <FormInput
                    id="pays"
                    v-model="form.pays"
                    type="text"
                    label="Pays"
                    placeholder="France"
                    :error="v$.pays.$errors[0]?.$message"
                    :external-error="errors.pays"
                    :touched="v$.pays.$dirty"
                    @blur="v$.pays.$touch()"
                  />
                </div>
              </div>

              <!-- Indicateur de validation globale -->
              <div v-if="v$.$errors.length > 0" class="alert alert-warning mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Veuillez corriger les erreurs avant de soumettre le formulaire.
              </div>

              <div class="d-grid">
                <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="processing || v$.$invalid"
                >
                  <i class="fas fa-save me-2"></i>
                  <span v-if="processing">Enregistrement...</span>
                  <span v-else>Enregistrer les modifications</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Informations Compte (inchangé) -->
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

        <!-- Entreprise info -->
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

<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useVuelidate } from '@vuelidate/core';
import { required, email, minLength, maxLength, helpers } from '@vuelidate/validators';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import FormInput from '@/Components/FormInput.vue';

// Props
const props = defineProps({
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
});

// State
const processing = ref(false);

const form = reactive({
  email: props.client.email || '',
  telephone: props.client.telephone || '',
  mobile: props.client.mobile || '',
  adresse: props.client.adresse || '',
  code_postal: props.client.code_postal || '',
  ville: props.client.ville || '',
  pays: props.client.pays || 'France'
});

// Custom validators
const frenchPhoneValidator = helpers.regex(/^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/);
const postalCodeValidator = helpers.regex(/^[0-9]{5}$/);

// Validation rules
const rules = computed(() => ({
  email: {
    required: helpers.withMessage('L\'email est obligatoire', required),
    email: helpers.withMessage('Email invalide', email),
    maxLength: helpers.withMessage('Maximum 255 caractères', maxLength(255))
  },
  telephone: {
    frenchPhone: helpers.withMessage('Format de téléphone invalide (ex: 01 23 45 67 89)', frenchPhoneValidator)
  },
  mobile: {
    frenchPhone: helpers.withMessage('Format de mobile invalide (ex: 06 12 34 56 78)', frenchPhoneValidator)
  },
  adresse: {
    maxLength: helpers.withMessage('Maximum 500 caractères', maxLength(500))
  },
  code_postal: {
    postalCode: helpers.withMessage('Code postal invalide (5 chiffres)', postalCodeValidator)
  },
  ville: {
    maxLength: helpers.withMessage('Maximum 255 caractères', maxLength(255))
  },
  pays: {
    maxLength: helpers.withMessage('Maximum 255 caractères', maxLength(255))
  }
}));

// Vuelidate instance
const v$ = useVuelidate(rules, form);

// Methods
const submitForm = async () => {
  // Valider le formulaire
  const isFormValid = await v$.value.$validate();

  if (!isFormValid) {
    return;
  }

  processing.value = true;

  router.put(route('client.profil.update'), form, {
    onSuccess: () => {
      processing.value = false;
      v$.value.$reset();
    },
    onError: () => {
      processing.value = false;
    }
  });
};

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('fr-FR');
};

const formatDateTime = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return `${d.toLocaleDateString('fr-FR')} ${d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}`;
};

const route = (name, params) => {
  return window.route ? window.route(name, params) : '#';
};
</script>

<style scoped>
.is-valid {
  border-color: #198754;
}

.is-valid:focus {
  border-color: #198754;
  box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
}

.is-invalid:focus {
  box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}
</style>
