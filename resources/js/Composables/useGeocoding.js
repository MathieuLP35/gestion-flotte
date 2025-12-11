// resources/js/composables/useGeocoding.js
import { ref } from 'vue';

export default function useGeocoding() {
    const suggestionsDeparture = ref([]);
    const suggestionsDestination = ref([]);
    const isLoadingDeparture = ref(false);
    const isLoadingDestination = ref(false);
    const cache = {};

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

            let suggestions = [];

            // 🔍 1. Essayer d'abord avec API Adresse.gouv.fr
            const adresseResponse = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=3`);
            const adresseData = await adresseResponse.json();

            suggestions = adresseData.features.map(f => ({
                label: f.properties.label,
                citycode: f.properties.citycode,
                city: f.properties.city,
                postcode: f.properties.postcode,
                street: f.properties.street || '',
                type: f.properties.type,
                lat: f.geometry.coordinates[1],
                lng: f.geometry.coordinates[0],
                source: 'adresse_gouv',
            }));

            // 🔍 2. Si peu de résultats (moins de 2) ET Nominatim activé → essayer avec Nominatim
            if (suggestions.length < 2 && geocoding.nominatimEnabled) {
                const nominatimUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=3&countrycodes=FR&addressdetails=1`;

                const nominatimResponse = await fetch(nominatimUrl);
                const nominatimData = await nominatimResponse.json();

                const nominatimSuggestions = nominatimData.map(f => ({
                    label: `${f.address.office ? f.address.office + ' ' : ''}, ${f.address.house_number ? f.address.house_number + ' ' : ''}${f.address.road ? f.address.road + ', ' : ''}${f.address.postcode ? f.address.postcode + ' ' : ''}${f.address.city || f.address.town || f.address.village || ''}`.trim().replace(/^,|,$/g, ''),
                    city: f.address?.city || f.address?.town || f.address?.village || '',
                    postcode: f.address?.postcode || '',
                    street: f.address?.road || '',
                    type: f.type || '',
                    lat: parseFloat(f.lat),
                    lng: parseFloat(f.lon),
                    source: 'nominatim',
                }));

                suggestions = [...suggestions, ...nominatimSuggestions];
            }

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

            // Sauvegarder dans localStorage (optionnel)
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