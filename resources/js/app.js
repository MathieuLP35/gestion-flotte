import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .mixin({
                methods: {
                    $t(key, replace = {}) {
                        // Récupère les traductions injectées
                        var translations = this.$page.props.translations || {};
                        var translation = translations[key] !== undefined ? translations[key] : key;

                        Object.keys(replace).forEach(function (k) {
                            translation = translation.replace(':' + k, replace[k]);
                        });

                        return translation;
                    }
                }
            })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
