import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import ToastPlugin from './plugins/toast';
import Toast from './Components/Toast.vue';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'BOXIBOX';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        // Lazy loading des pages avec code splitting
        return resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        );
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ToastPlugin);

        // Ajouter le composant Toast globalement
        app.component('Toast', Toast);

        return app.mount(el);
    },
    progress: {
        color: '#0d6efd',
        showSpinner: true,
    },
});
