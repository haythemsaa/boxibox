# 📋 Guide de Validation - Boxibox

## 🎯 Vue d'ensemble

Ce guide explique comment utiliser le système de validation personnalisé de Boxibox, basé sur **Vuelidate** avec des validateurs métier spécifiques.

### Fichiers du système

```
resources/js/
├── utils/
│   └── validators.js              # Validateurs personnalisés
├── composables/
│   ├── useValidation.js           # Composable validation
│   └── useToast.js                # Toast pour feedback
└── Pages/Client/
    ├── Profil.vue                 # Exemple d'utilisation
    └── SepaCreate.vue             # Exemple avec IBAN/BIC
```

---

## 🚀 Utilisation rapide

### Exemple basique

```vue
<template>
    <form @submit.prevent="handleSubmit">
        <div class="mb-3">
            <label>Email</label>
            <input
                v-model="form.email"
                @blur="v$.form.email.$touch"
                :class="getValidationClass('form.email')"
                class="form-control"
            >
            <div v-if="getErrorMessage('form.email')" class="invalid-feedback">
                {{ getErrorMessage('form.email') }}
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</template>

<script>
import { reactive } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { createValidators, useValidation } from '@/composables/useValidation';

export default {
    setup() {
        const validators = createValidators();

        const form = reactive({
            email: '',
        });

        const rules = {
            form: {
                email: { required: validators.required, email: validators.email },
            },
        };

        const v$ = useVuelidate(rules, { form });
        const { getValidationClass, getErrorMessage, validate } = useValidation(v$);

        const handleSubmit = async () => {
            const isValid = await validate();
            if (!isValid) return;

            // Traiter le formulaire
        };

        return { form, v$, getValidationClass, getErrorMessage, handleSubmit };
    },
};
</script>
```

---

## 📚 Validateurs disponibles

### 1. **IBAN** (Compte bancaire)

```javascript
import { createValidators } from '@/composables/useValidation';

const validators = createValidators();

const rules = {
    iban: { required: validators.required, iban: validators.iban },
};
```

**Formats acceptés :**
- FR76 1234 5678 9012 3456 7890 123 (IBAN français - 27 caractères)
- DE89 3704 0044 0532 0130 00 (IBAN allemand - 22 caractères)
- Tous pays européens (15-34 caractères)

**Validation :**
- Longueur correcte
- Format pays valide
- Checksum MOD 97

---

### 2. **BIC/SWIFT** (Code bancaire)

```javascript
const rules = {
    bic: { required: validators.required, bic: validators.bic },
};
```

**Formats acceptés :**
- BNPAFRPPXXX (11 caractères avec code branche)
- BNPAFRPP (8 caractères sans branche)

**Validation :**
- 4 lettres (code banque)
- 2 lettres (code pays)
- 2 caractères (localisation)
- 3 caractères optionnels (branche)

---

### 3. **SIRET** (Numéro d'entreprise française)

```javascript
const rules = {
    siret: { required: validators.required, siret: validators.siret },
};
```

**Format :**
- 14 chiffres
- Validation algorithme de Luhn

---

### 4. **Téléphone français**

```javascript
const rules = {
    telephone: { required: validators.required, phoneFR: validators.phoneFR },
};
```

**Formats acceptés :**
- 01 23 45 67 89
- 0123456789
- +33 1 23 45 67 89
- +33123456789

---

### 5. **Code postal français**

```javascript
const rules = {
    codePostal: { required: validators.required, codePostalFR: validators.codePostalFR },
};
```

**Format :**
- 5 chiffres
- Commence par 01-95 (départements français)
- 2A et 2B pour la Corse

---

### 6. **Email**

```javascript
const rules = {
    email: { required: validators.required, email: validators.email },
};
```

**Format :**
- RFC 5322 simplifié
- Ex: utilisateur@domaine.com

---

### 7. **URL**

```javascript
const rules = {
    website: { url: validators.url },
};
```

**Formats acceptés :**
- https://example.com
- http://example.com
- https://sub.example.com/path

---

### 8. **Montant**

```javascript
const rules = {
    prix: { required: validators.required, amount: validators.amount },
};
```

**Validation :**
- Nombre positif
- Maximum 2 décimales
- Ex: 123.45

---

### 9. **Pourcentage**

```javascript
const rules = {
    tva: { required: validators.required, percentage: validators.percentage },
};
```

**Validation :**
- Entre 0 et 100
- Maximum 2 décimales
- Ex: 20.00

---

### 10. **Date**

```javascript
const rules = {
    dateNaissance: { required: validators.required, date: validators.date },
};
```

**Formats acceptés :**
- YYYY-MM-DD (2025-10-07)
- DD/MM/YYYY (07/10/2025)

---

### 11. **Mot de passe fort**

```javascript
const rules = {
    password: { required: validators.required, strongPassword: validators.strongPassword },
};
```

**Critères :**
- Minimum 8 caractères
- Au moins 1 majuscule
- Au moins 1 minuscule
- Au moins 1 chiffre

---

### 12. **Couleur hexadécimale**

```javascript
const rules = {
    couleur: { hexColor: validators.hexColor },
};
```

**Formats acceptés :**
- #RGB (ex: #F00)
- #RRGGBB (ex: #FF0000)

---

## 🛠️ Composable `useValidation`

### Méthodes disponibles

#### `getValidationClass(fieldPath)`

Retourne les classes CSS Bootstrap pour un champ.

```javascript
const { getValidationClass } = useValidation(v$);

// Dans le template
:class="getValidationClass('form.email')"
// Retourne: { 'is-invalid': true } ou { 'is-valid': true }
```

#### `getErrorMessage(fieldPath)`

Retourne le premier message d'erreur d'un champ.

```javascript
const { getErrorMessage } = useValidation(v$);

// Dans le template
<div class="invalid-feedback">
    {{ getErrorMessage('form.email') }}
</div>
// Affiche: "L'adresse email n'est pas valide"
```

#### `hasError(fieldPath)`

Vérifie si un champ a des erreurs.

```javascript
const { hasError } = useValidation(v$);

if (hasError('form.email')) {
    // Afficher un indicateur d'erreur
}
```

#### `isFormValid`

Computed qui indique si le formulaire est valide.

```javascript
const { isFormValid } = useValidation(v$);

// Dans le template
<button :disabled="!isFormValid">Envoyer</button>
```

#### `validate()`

Valide le formulaire et retourne un booléen.

```javascript
const { validate } = useValidation(v$);

const handleSubmit = async () => {
    const isValid = await validate();
    if (!isValid) {
        toast.validationError();
        return;
    }

    // Traiter le formulaire
};
```

#### `touchAll()`

Touche tous les champs (affiche toutes les erreurs).

```javascript
const { touchAll } = useValidation(v$);

const showAllErrors = () => {
    touchAll();
};
```

#### `reset()`

Réinitialise la validation.

```javascript
const { reset } = useValidation(v$);

const resetForm = () => {
    form.email = '';
    reset();
};
```

---

## 📝 Exemple complet : Formulaire de profil

```vue
<template>
    <ClientLayout>
        <form @submit.prevent="updateProfile">
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email *</label>
                <input
                    type="email"
                    v-model="form.email"
                    @blur="v$.form.email.$touch"
                    :class="getValidationClass('form.email')"
                    class="form-control"
                    id="email"
                >
                <div v-if="getErrorMessage('form.email')" class="invalid-feedback">
                    {{ getErrorMessage('form.email') }}
                </div>
            </div>

            <!-- Téléphone -->
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone *</label>
                <input
                    type="tel"
                    v-model="form.telephone"
                    @blur="v$.form.telephone.$touch"
                    :class="getValidationClass('form.telephone')"
                    class="form-control"
                    id="telephone"
                    placeholder="06 12 34 56 78"
                >
                <div v-if="getErrorMessage('form.telephone')" class="invalid-feedback">
                    {{ getErrorMessage('form.telephone') }}
                </div>
            </div>

            <!-- Code postal -->
            <div class="mb-3">
                <label for="codePostal" class="form-label">Code postal *</label>
                <input
                    type="text"
                    v-model="form.codePostal"
                    @blur="v$.form.codePostal.$touch"
                    :class="getValidationClass('form.codePostal')"
                    class="form-control"
                    id="codePostal"
                    maxlength="5"
                >
                <div v-if="getErrorMessage('form.codePostal')" class="invalid-feedback">
                    {{ getErrorMessage('form.codePostal') }}
                </div>
            </div>

            <div class="d-grid gap-2">
                <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="!isFormValid"
                >
                    Mettre à jour
                </button>
            </div>
        </form>
    </ClientLayout>
</template>

<script>
import { reactive } from 'vue';
import { useVuelidate } from '@vuelidate/core';
import { router } from '@inertiajs/vue3';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { createValidators, useValidation } from '@/composables/useValidation';
import { useToast } from '@/composables/useToast';

export default {
    components: { ClientLayout },

    props: {
        client: Object,
    },

    setup(props) {
        const validators = createValidators();
        const toast = useToast();

        const form = reactive({
            email: props.client.email || '',
            telephone: props.client.telephone || '',
            codePostal: props.client.code_postal || '',
        });

        const rules = {
            form: {
                email: {
                    required: validators.required,
                    email: validators.email,
                },
                telephone: {
                    required: validators.required,
                    phoneFR: validators.phoneFR,
                },
                codePostal: {
                    required: validators.required,
                    codePostalFR: validators.codePostalFR,
                },
            },
        };

        const v$ = useVuelidate(rules, { form });
        const { getValidationClass, getErrorMessage, isFormValid, validate } = useValidation(v$);

        const updateProfile = async () => {
            const isValid = await validate();

            if (!isValid) {
                toast.validationError('Veuillez corriger les erreurs du formulaire');
                return;
            }

            router.put(route('client.profil.update'), form, {
                onSuccess: () => {
                    toast.saveSuccess('Profil');
                },
                onError: () => {
                    toast.error('Une erreur est survenue');
                },
            });
        };

        return {
            form,
            v$,
            getValidationClass,
            getErrorMessage,
            isFormValid,
            updateProfile,
        };
    },
};
</script>
```

---

## 🔧 Créer un validateur personnalisé

### 1. Ajouter dans `validators.js`

```javascript
export const validateCustom = (value) => {
    // Votre logique de validation
    return /* true ou false */;
};

export const customValidator = (value) => !value || validateCustom(value);
```

### 2. Ajouter le message

```javascript
export const validationMessages = {
    // ...
    custom: 'Message d\'erreur personnalisé',
};
```

### 3. Ajouter dans `useValidation.js`

```javascript
import * as validators from '../utils/validators';

export function createValidators() {
    return {
        // ...
        custom: helpers.withMessage(
            validators.validationMessages.custom,
            validators.customValidator
        ),
    };
}
```

### 4. Utiliser

```javascript
const rules = {
    monChamp: { custom: validators.custom },
};
```

---

## 💡 Bonnes pratiques

### 1. Toujours toucher les champs au blur

```vue
<input
    v-model="form.email"
    @blur="v$.form.email.$touch"
>
```

### 2. Désactiver le bouton si formulaire invalide

```vue
<button :disabled="!isFormValid">Envoyer</button>
```

### 3. Afficher un toast en cas d'erreur

```javascript
const handleSubmit = async () => {
    const isValid = await validate();
    if (!isValid) {
        toast.validationError();
        return;
    }
};
```

### 4. Utiliser les classes Bootstrap

```vue
<input
    :class="getValidationClass('form.email')"
    class="form-control"
>
<div v-if="getErrorMessage('form.email')" class="invalid-feedback">
    {{ getErrorMessage('form.email') }}
</div>
```

---

## 🧪 Tests

### Test manuel

```bash
# Démarrer le serveur
php artisan serve

# Tester les validations:
# 1. Aller sur /client/profil
# 2. Vider un champ requis → Erreur "Ce champ est requis"
# 3. Entrer email invalide → Erreur "Email invalide"
# 4. Entrer téléphone invalide → Erreur "Téléphone invalide"
```

### Tests unitaires (à venir)

```javascript
import { validateIban } from '@/utils/validators';

describe('Validators', () => {
    it('valide un IBAN français', () => {
        expect(validateIban('FR7630006000011234567890189')).toBe(true);
        expect(validateIban('FR00')).toBe(false);
    });
});
```

---

## 📞 Support

**Documentation :**
- Vuelidate : https://vuelidate-next.netlify.app/
- Bootstrap validation : https://getbootstrap.com/docs/5.3/forms/validation/

**Questions :**
- GitHub Issues : https://github.com/haythemsaa/boxibox/issues

---

**Dernière mise à jour** : 07 Octobre 2025
**Version** : 1.0.0
**Développé par** : Claude Code + Haythem SAA
