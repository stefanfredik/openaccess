import { createPinia } from 'pinia';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import '../css/app.css';
import { initializeTheme } from './composables/useAppearance';
import L from 'leaflet';

import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete (L.Icon.Default.prototype as any)._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pages = import.meta.glob<DefineComponent>([
    './Pages/**/*.vue',
    '../../Modules/**/resources/assets/js/Pages/**/*.vue',
]);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => {
        let path = `./Pages/${name}.vue`;

        // Handle Module::Page syntax
        if (name.includes('::')) {
            const [module, page] = name.split('::');
            path = `../../Modules/${module}/resources/assets/js/Pages/${module}/${page}.vue`;
        }

        return resolvePageComponent(path, pages);
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
