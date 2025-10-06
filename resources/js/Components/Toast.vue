<template>
    <Teleport to="body">
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="toast show"
                    :class="`border-${toast.type}`"
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                >
                    <div class="toast-header" :class="`bg-${toast.type} bg-opacity-10`">
                        <i class="fas me-2" :class="getIconClass(toast.type)"></i>
                        <strong class="me-auto">{{ getTitle(toast.type) }}</strong>
                        <small>{{ getTimeAgo(toast.timestamp) }}</small>
                        <button
                            type="button"
                            class="btn-close"
                            @click="removeToast(toast.id)"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="toast-body">
                        {{ toast.message }}
                    </div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script>
export default {
    name: 'Toast',

    data() {
        return {
            toasts: []
        };
    },

    mounted() {
        // Écouter les événements toast globaux
        window.addEventListener('toast', this.handleToast);

        // Écouter les succès/erreurs Inertia
        this.$inertia.on('success', (event) => {
            const message = event.detail?.page?.props?.flash?.success;
            if (message) {
                this.showToast(message, 'success');
            }
        });

        this.$inertia.on('error', (event) => {
            const message = event.detail?.page?.props?.flash?.error;
            if (message) {
                this.showToast(message, 'danger');
            }
        });
    },

    beforeUnmount() {
        window.removeEventListener('toast', this.handleToast);
    },

    methods: {
        handleToast(event) {
            const { message, type = 'info', duration = 5000 } = event.detail;
            this.showToast(message, type, duration);
        },

        showToast(message, type = 'info', duration = 5000) {
            const id = Date.now() + Math.random();
            const toast = {
                id,
                message,
                type,
                timestamp: Date.now()
            };

            this.toasts.push(toast);

            // Auto-dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.removeToast(id);
                }, duration);
            }
        },

        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id);
            if (index !== -1) {
                this.toasts.splice(index, 1);
            }
        },

        getIconClass(type) {
            const icons = {
                success: 'fa-check-circle text-success',
                danger: 'fa-exclamation-circle text-danger',
                warning: 'fa-exclamation-triangle text-warning',
                info: 'fa-info-circle text-info'
            };
            return icons[type] || icons.info;
        },

        getTitle(type) {
            const titles = {
                success: 'Succès',
                danger: 'Erreur',
                warning: 'Attention',
                info: 'Information'
            };
            return titles[type] || 'Notification';
        },

        getTimeAgo(timestamp) {
            const seconds = Math.floor((Date.now() - timestamp) / 1000);
            if (seconds < 5) return 'À l\'instant';
            if (seconds < 60) return `Il y a ${seconds}s`;
            const minutes = Math.floor(seconds / 60);
            return `Il y a ${minutes}min`;
        }
    }
};
</script>

<style scoped>
.toast {
    min-width: 300px;
    max-width: 400px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100px);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100px);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>
