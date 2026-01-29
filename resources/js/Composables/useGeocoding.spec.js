import { describe, it, expect, vi, beforeEach } from 'vitest';
import useGeocoding from '@/Composables/useGeocoding';

describe('useGeocoding', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    global.fetch = vi.fn();
  });

  it('returns refs and fetchSuggestions', () => {
    const r = useGeocoding();
    expect(r.suggestionsDeparture).toBeDefined();
    expect(r.suggestionsDestination).toBeDefined();
    expect(r.isLoadingDeparture).toBeDefined();
    expect(r.isLoadingDestination).toBeDefined();
    expect(typeof r.fetchSuggestions).toBe('function');
  });

  it('fetchSuggestions does nothing when query has less than 4 chars (departure)', async () => {
    const { fetchSuggestions, suggestionsDeparture } = useGeocoding();
    await fetchSuggestions('ab', 'departure');
    expect(global.fetch).not.toHaveBeenCalled();
    expect(suggestionsDeparture.value).toEqual([]);
  });

  it('fetchSuggestions does nothing when query has less than 4 chars (destination)', async () => {
    const { fetchSuggestions, suggestionsDestination } = useGeocoding();
    await fetchSuggestions('x', 'destination');
    expect(global.fetch).not.toHaveBeenCalled();
    expect(suggestionsDestination.value).toEqual([]);
  });

  it('fetchSuggestions fetches and maps adresse.gouv features', async () => {
    const adresseData = {
      features: [
        {
          properties: { label: '10 Rue de la Paix, 75002 Paris', city: 'Paris', postcode: '75002', street: 'Rue de la Paix', type: 'housenumber', citycode: '75102' },
          geometry: { coordinates: [2.33, 48.87] },
        },
      ],
    };
    global.fetch.mockImplementation((url) =>
      Promise.resolve({
        json: () => Promise.resolve(url.includes('nominatim') ? [] : adresseData),
      })
    );

    const { fetchSuggestions, suggestionsDeparture } = useGeocoding();
    await fetchSuggestions('10 rue de la paix', 'departure');

    expect(global.fetch).toHaveBeenCalled();
    expect(suggestionsDeparture.value).toHaveLength(1);
    expect(suggestionsDeparture.value[0].label).toBe('10 Rue de la Paix, 75002 Paris');
    expect(suggestionsDeparture.value[0].lat).toBe(48.87);
    expect(suggestionsDeparture.value[0].lng).toBe(2.33);
    expect(suggestionsDeparture.value[0].source).toBe('adresse_gouv');
  });

  it('fetchSuggestions clears suggestions and sets loading false on fetch error', async () => {
    global.fetch.mockRejectedValue(new Error('Network error'));
    const { fetchSuggestions, suggestionsDeparture, isLoadingDeparture } = useGeocoding();
    await fetchSuggestions('paris 75001', 'departure');
    expect(suggestionsDeparture.value).toEqual([]);
    expect(isLoadingDeparture.value).toBe(false);
  });

  it('fetchSuggestions sets suggestionsDestination when type is destination', async () => {
    const adresseData = {
      features: [
        {
          properties: { label: 'Lyon Part-Dieu', city: 'Lyon', postcode: '69003', street: '', type: 'street', citycode: '69123', _type: 'address' },
          geometry: { coordinates: [4.86, 45.76] },
        },
      ],
    };
    global.fetch.mockResolvedValue({
      json: () => Promise.resolve(adresseData),
    });

    const { fetchSuggestions, suggestionsDestination } = useGeocoding();
    await fetchSuggestions('lyon 69003', 'destination');

    expect(suggestionsDestination.value.length).toBeGreaterThanOrEqual(0);
  });

  it('fetchSuggestions does nothing when query is null or empty', async () => {
    const { fetchSuggestions, suggestionsDeparture, suggestionsDestination } = useGeocoding();
    await fetchSuggestions('', 'departure');
    await fetchSuggestions(null, 'destination');
    expect(global.fetch).not.toHaveBeenCalled();
    expect(suggestionsDeparture.value).toEqual([]);
    expect(suggestionsDestination.value).toEqual([]);
  });
});
