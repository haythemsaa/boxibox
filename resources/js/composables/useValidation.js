/**
 * Composable useValidation
 * Facilite l'utilisation de Vuelidate avec les validateurs personnalisés
 */

import { computed } from 'vue';
import { helpers } from '@vuelidate/validators';
import * as validators from '../utils/validators';

export function useValidation(v$) {
    /**
     * Obtenir la classe CSS pour un champ (Bootstrap)
     * @param {string} fieldPath - Chemin du champ (ex: 'form.email')
     * @returns {object} - Classes CSS
     */
    const getValidationClass = (fieldPath) => {
        const field = getField(v$, fieldPath);
        if (!field) return {};

        return {
            'is-invalid': field.$error,
            'is-valid': field.$dirty && !field.$invalid,
        };
    };

    /**
     * Obtenir le premier message d'erreur d'un champ
     * @param {string} fieldPath - Chemin du champ
     * @returns {string|null}
     */
    const getErrorMessage = (fieldPath) => {
        const field = getField(v$, fieldPath);
        if (!field || !field.$error) return null;

        // Récupérer la première erreur
        const errorKey = Object.keys(field.$errors[0])[0];
        const error = field.$errors[0];

        // Messages personnalisés
        const customMessages = {
            required: 'Ce champ est requis',
            minLength: `Minimum ${error.$params?.min} caractères`,
            maxLength: `Maximum ${error.$params?.max} caractères`,
            email: 'Email invalide',
            sameAs: 'Les champs ne correspondent pas',
            minValue: `Valeur minimale : ${error.$params?.min}`,
            maxValue: `Valeur maximale : ${error.$params?.max}`,
            ibanValidator: validators.validationMessages.iban,
            bicValidator: validators.validationMessages.bic,
            siretValidator: validators.validationMessages.siret,
            phoneFRValidator: validators.validationMessages.phoneFR,
            codePostalFRValidator: validators.validationMessages.codePostalFR,
            emailValidator: validators.validationMessages.email,
            urlValidator: validators.validationMessages.url,
            amountValidator: validators.validationMessages.amount,
            percentageValidator: validators.validationMessages.percentage,
            dateValidator: validators.validationMessages.date,
            strongPasswordValidator: validators.validationMessages.strongPassword,
            hexColorValidator: validators.validationMessages.hexColor,
        };

        return error.$message || customMessages[errorKey] || 'Champ invalide';
    };

    /**
     * Vérifier si un champ a des erreurs
     * @param {string} fieldPath - Chemin du champ
     * @returns {boolean}
     */
    const hasError = (fieldPath) => {
        const field = getField(v$, fieldPath);
        return field ? field.$error : false;
    };

    /**
     * Vérifier si le formulaire est valide
     * @returns {boolean}
     */
    const isFormValid = computed(() => {
        return !v$.value.$invalid;
    });

    /**
     * Toucher tous les champs (afficher toutes les erreurs)
     */
    const touchAll = () => {
        v$.value.$touch();
    };

    /**
     * Réinitialiser la validation
     */
    const reset = () => {
        v$.value.$reset();
    };

    /**
     * Valider le formulaire
     * @returns {Promise<boolean>}
     */
    const validate = async () => {
        const isValid = await v$.value.$validate();
        return isValid;
    };

    /**
     * Obtenir un champ depuis un chemin
     * @private
     */
    function getField(validator, path) {
        const parts = path.split('.');
        let current = validator.value;

        for (const part of parts) {
            if (!current || !current[part]) return null;
            current = current[part];
        }

        return current;
    }

    return {
        getValidationClass,
        getErrorMessage,
        hasError,
        isFormValid,
        touchAll,
        reset,
        validate,
    };
}

/**
 * Créer des validateurs Vuelidate avec helpers
 */
export function createValidators() {
    return {
        // Validateurs standards avec messages personnalisés
        required: helpers.withMessage('Ce champ est requis', (value) => {
            if (Array.isArray(value)) return value.length > 0;
            if (typeof value === 'string') return value.trim().length > 0;
            return value !== null && value !== undefined && value !== '';
        }),

        // Validateurs personnalisés
        iban: helpers.withMessage(
            validators.validationMessages.iban,
            validators.ibanValidator
        ),

        bic: helpers.withMessage(
            validators.validationMessages.bic,
            validators.bicValidator
        ),

        siret: helpers.withMessage(
            validators.validationMessages.siret,
            validators.siretValidator
        ),

        phoneFR: helpers.withMessage(
            validators.validationMessages.phoneFR,
            validators.phoneFRValidator
        ),

        codePostalFR: helpers.withMessage(
            validators.validationMessages.codePostalFR,
            validators.codePostalFRValidator
        ),

        email: helpers.withMessage(
            validators.validationMessages.email,
            validators.emailValidator
        ),

        url: helpers.withMessage(
            validators.validationMessages.url,
            validators.urlValidator
        ),

        amount: helpers.withMessage(
            validators.validationMessages.amount,
            validators.amountValidator
        ),

        percentage: helpers.withMessage(
            validators.validationMessages.percentage,
            validators.percentageValidator
        ),

        date: helpers.withMessage(
            validators.validationMessages.date,
            validators.dateValidator
        ),

        strongPassword: helpers.withMessage(
            validators.validationMessages.strongPassword,
            validators.strongPasswordValidator
        ),

        hexColor: helpers.withMessage(
            validators.validationMessages.hexColor,
            validators.hexColorValidator
        ),
    };
}

export default {
    useValidation,
    createValidators,
};
