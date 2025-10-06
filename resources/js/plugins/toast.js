/**
 * Plugin Toast pour Vue 3
 * Usage: this.$toast.success('Message'), this.$toast.error('Message'), etc.
 */

export default {
    install(app) {
        const toast = {
            show(message, type = 'info', duration = 5000) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message, type, duration }
                }));
            },

            success(message, duration = 5000) {
                this.show(message, 'success', duration);
            },

            error(message, duration = 5000) {
                this.show(message, 'danger', duration);
            },

            warning(message, duration = 5000) {
                this.show(message, 'warning', duration);
            },

            info(message, duration = 5000) {
                this.show(message, 'info', duration);
            }
        };

        // Ajouter $toast à l'instance globale
        app.config.globalProperties.$toast = toast;

        // Rendre disponible via provide/inject
        app.provide('toast', toast);
    }
};
