import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Pusher = Pusher;

// Initialiser Echo seulement si les variables d'environnement Reverb sont définies
if (import.meta.env.VITE_REVERB_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
} else {
    // Créer un objet Echo vide pour éviter les erreurs
    window.Echo = {
        private: () => ({
            listen: () => {},
            leave: () => {},
        }),
        channel: () => ({
            listen: () => {},
            leave: () => {},
        }),
    };
}
