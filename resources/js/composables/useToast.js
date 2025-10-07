import { useToast as useToastification } from "vue-toastification";

/**
 * Composable pour gérer les notifications toast
 * Usage dans les composants Vue 3 :
 *
 * <script setup>
 * import { useToast } from '@/composables/useToast';
 * const toast = useToast();
 *
 * toast.success('Opération réussie !');
 * toast.error('Une erreur est survenue');
 * toast.warning('Attention !');
 * toast.info('Information importante');
 * </script>
 */
export function useToast() {
    const toast = useToastification();

    return {
        success: (message, options = {}) => {
            toast.success(message, {
                timeout: 4000,
                ...options
            });
        },

        error: (message, options = {}) => {
            toast.error(message, {
                timeout: 6000,
                ...options
            });
        },

        warning: (message, options = {}) => {
            toast.warning(message, {
                timeout: 5000,
                ...options
            });
        },

        info: (message, options = {}) => {
            toast.info(message, {
                timeout: 4000,
                ...options
            });
        },

        clear: () => {
            toast.clear();
        },

        /**
         * Affiche un toast de succès pour une opération CRUD
         */
        saveSuccess: (entity = 'Élément') => {
            toast.success(`${entity} enregistré avec succès !`, {
                timeout: 3000
            });
        },

        /**
         * Affiche un toast de succès pour une suppression
         */
        deleteSuccess: (entity = 'Élément') => {
            toast.success(`${entity} supprimé avec succès !`, {
                timeout: 3000
            });
        },

        /**
         * Affiche un toast d'erreur générique
         */
        errorGeneric: () => {
            toast.error('Une erreur est survenue. Veuillez réessayer.', {
                timeout: 6000
            });
        },

        /**
         * Affiche un toast pour une erreur de validation
         */
        validationError: (message = 'Veuillez corriger les erreurs du formulaire') => {
            toast.warning(message, {
                timeout: 5000
            });
        },

        /**
         * Affiche un toast de confirmation
         */
        confirm: (message) => {
            toast.info(message, {
                timeout: 4000
            });
        }
    };
}
