import { ref, readonly } from 'vue';

/** Cache partagé entre les composants (évite de recharger les mêmes clés). */
const cache = ref({});

/**
 * Charge les traductions à la demande via GET /api/translations.
 *
 * @param {string[]} [keys] - Clés à charger (ex. ['permissions.roles.view']). Si vide ou absent, charge toutes les clés permissions.*.
 * @returns {Promise<void>}
 */
async function load(keys = []) {
    try {
        const url =
            keys.length > 0
                ? `/api/translations?keys=${encodeURIComponent(keys.join(','))}`
                : '/api/translations';
        const res = await fetch(url, { credentials: 'include' });
        if (!res.ok) return;
        const json = await res.json();
        cache.value = { ...cache.value, ...json };
    } catch {
        // En tests ou si l’API est indisponible, on garde le cache actuel
    }
}

/**
 * @param {string} key - Clé de traduction (ex. 'permissions.roles.view')
 * @returns {string} Traduction ou la clé si absente
 */
function t(key) {
    return cache.value[key] ?? key;
}

/**
 * Composable pour les traductions chargées à la demande.
 * Remplacer page.props.translations + translate() par useTranslations + load() en onMounted.
 *
 * @example
 * const { t, load } = useTranslations();
 * onMounted(() => load()); // ou load(permissions.map(p => 'permissions.' + p))
 * // puis {{ t('permissions.roles.view') }}
 */
export default function useTranslations() {
    return {
        t,
        load,
        translations: readonly(cache),
    };
}
