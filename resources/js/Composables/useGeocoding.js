// resources/js/composables/useGeocoding.js
import { ref } from 'vue';

export default function useGeocoding() {
    const suggestionsDeparture = ref([]);
    const suggestionsDestination = ref([]);
    const isLoadingDeparture = ref(false);
    const isLoadingDestination = ref(false);
    const cache = {};
    const nominatimEnabled = import.meta.env.VITE_NOMINATIM_ENABLED;
    let suggestions = ref([]);

    const fetchSuggestions = async (query, type, geocoding) => {
        if (!query || query.length < 4) {
            if (type === 'departure') suggestionsDeparture.value = [];
            if (type === 'destination') suggestionsDestination.value = [];
            return;
        }

        const cacheKey = `${query}_${type}`;
        if (cache[cacheKey] && Date.now() - cache[cacheKey].timestamp < 5 * 60 * 1000) { // 5 min
            if (type === 'departure') suggestionsDeparture.value = cache[cacheKey].data;
            if (type === 'destination') suggestionsDestination.value = cache[cacheKey].data;
            return;
        }

        try {
            if (type === 'departure') isLoadingDeparture.value = true;
            if (type === 'destination') isLoadingDestination.value = true;

            const adresseResponse = await fetch(`https://data.geopf.fr/geocodage/search/?q=${encodeURIComponent(query)}&limit=3&index=address`);
            const adresseData = await adresseResponse.json();

            suggestions = adresseData.features.map(f => ({
                label: f.properties._type === 'poi' ? `${f.properties.toponym}, ${f.properties.postcode ? f.properties.postcode : ""} ${f.properties.city ? f.properties.city : ""}` : f.properties.label,
                citycode: f.properties.citycode,
                city: f.properties.city,
                postcode: f.properties.postcode,
                street: f.properties.street || '',
                type: f.properties.type,
                lat: f.geometry.coordinates[1],
                lng: f.geometry.coordinates[0],
                source: 'adresse_gouv',
            }));

            // On ajoute les agences à la liste des suggestions


            if (type === 'departure') {
                suggestionsDeparture.value = suggestions;
            } else {
                suggestionsDestination.value = suggestions;
            }

            // Enregistrer dans le cache
            cache[cacheKey] = {
                data: suggestions,
                timestamp: Date.now(),
            };

            // Sauvegarder dans localStorage
            localStorage.setItem('geocodingCache', JSON.stringify(cache));
        } catch (error) {
            console.error('Erreur lors de la recherche d’adresses :', error);
            if (type === 'departure') suggestionsDeparture.value = [];
            if (type === 'destination') suggestionsDestination.value = [];
        } finally {
            if (type === 'departure') isLoadingDeparture.value = false;
            if (type === 'destination') isLoadingDestination.value = false;
        }
    };

    return {
        suggestionsDeparture,
        suggestionsDestination,
        isLoadingDeparture,
        isLoadingDestination,
        fetchSuggestions,
    };
}
